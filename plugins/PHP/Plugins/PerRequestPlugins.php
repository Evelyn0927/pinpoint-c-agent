<?php
/******************************************************************************
 * Copyright 2020 NAVER Corp.                                                 *
 *                                                                            *
 * Licensed under the Apache License, Version 2.0 (the "License");            *
 * you may not use this file except in compliance with the License.           *
 * You may obtain a copy of the License at                                    *
 *                                                                            *
 *     http://www.apache.org/licenses/LICENSE-2.0                             *
 *                                                                            *
 * Unless required by applicable law or agreed to in writing, software        *
 * distributed under the License is distributed on an "AS IS" BASIS,          *
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.   *
 * See the License for the specific language governing permissions and        *
 * limitations under the License.                                             *
 ******************************************************************************/




namespace Plugins;

require_once "__init__.php";

class PerRequestPlugins
{
    public static $_intance = null;
    public $tid = null;
    public $sid = null;
    public $psid = null;
    public $pname = null;
    public $ptype = null;
    public $ah = null;
    public $app_name = null;
    public $app_id = null;
    private $curNextSpanId = '';
    private $isLimit = false;

    public function traceLimit()
    {
        return $this->isLimit;
    }

    public static function instance()
    {
        if (self::$_intance == null) {
            self::$_intance = new PerRequestPlugins();
        }
        return self::$_intance;
    }

    private function initTrace()
    {
        while (pinpoint_end_trace() > 0);
    }

    private function __construct()
    {
        $this->initTrace();

        pinpoint_start_trace();
        pinpoint_add_clue(PP_REQ_URI, $_SERVER['REQUEST_URI']);
        pinpoint_add_clue(PP_REQ_CLIENT, $_SERVER["REMOTE_ADDR"]);
        pinpoint_add_clue(PP_REQ_SERVER, $_SERVER["HTTP_HOST"]);
        pinpoint_add_clue(PP_SERVER_TYPE, PP_PHP);
        pinpoint_add_clue(PP_INTERCEPTER_NAME, "PP_PHP Request");

        if (defined('APPLICATION_NAME')) {
            if($_SERVER["HTTP_HOST"] == "php.backend.com"){
                $this->app_name = APPLICATION_NAME.'01';
            }else{
                $this->app_name = APPLICATION_NAME;
            }
        } else {
            $this->app_name = pinpoint_app_name();
        }

        pinpoint_add_clue(PP_APP_NAME, $this->app_name);
        if (defined('APPLICATION_ID')) {
            if($_SERVER["HTTP_HOST"] == "php.backend.com"){
                $this->app_id = APPLICATION_ID.'01';
            }else{
                $this->app_id = APPLICATION_ID;
            }
        } else {
            $this->app_id = pinpoint_app_id();
        }

        pinpoint_add_clue(PP_APP_ID, $this->app_id);

        if (isset($_SERVER[PP_HEADER_PSPANID]) || array_key_exists(PP_HEADER_PSPANID, $_SERVER)) {
            $this->psid = $_SERVER[PP_HEADER_PSPANID];
            pinpoint_add_clue(PP_PARENT_SPAN_ID, $this->psid);
            echo "psid: $this->psid \n";
        }

        if (isset($_SERVER[PP_HEADER_SPANID]) || array_key_exists(PP_HEADER_SPANID, $_SERVER)) {
            $this->sid = $_SERVER[PP_HEADER_SPANID];
            echo "sid: $this->sid \n";
        } else {
            $this->sid = $this->generateSpanID();
        }

        if (isset($_SERVER[PP_HEADER_TRACEID]) || array_key_exists(PP_HEADER_TRACEID, $_SERVER)) {
            $this->tid = $_SERVER[PP_HEADER_TRACEID];
            echo "tid: $this->tid\n";
        } else {
            $this->tid = $this->generateTransactionID();
        }

        if (isset($_SERVER[PP_HEADER_PAPPNAME]) || array_key_exists(PP_HEADER_PAPPNAME, $_SERVER)) {
            $this->pname = $_SERVER[PP_HEADER_PAPPNAME];

            pinpoint_add_clue(PP_PARENT_NAME, $this->pname);
            echo "pname: $this->pname";
        }

        if (isset($_SERVER[PP_HEADER_PAPPTYPE]) || array_key_exists(PP_HEADER_PAPPTYPE, $_SERVER)) {
            $this->ptype = $_SERVER[PP_HEADER_PAPPTYPE];
            pinpoint_add_clue(PP_PARENT_TYPE, $this->ptype);
            echo "ptype: $this->pname";
        }

        if (isset($_SERVER[PP_HEADER_PINPOINT_HOST]) || array_key_exists(PP_HEADER_PINPOINT_HOST, $_SERVER)) {
            $this->ah = $_SERVER[PP_HEADER_PINPOINT_HOST];
            pinpoint_add_clue(PP_PARENT_HOST, $this->ah);
            echo "Ah: $this->ah";
        }
        if (isset($_SERVER[PP_HEADER_NGINX_PROXY]) || array_key_exists(PP_HEADER_NGINX_PROXY, $_SERVER)) {
            pinpoint_add_clue(PP_NGINX_PROXY, $_SERVER[PP_HEADER_NGINX_PROXY]);
        }

        if (isset($_SERVER[PP_HEADER_APACHE_PROXY]) || array_key_exists(PP_HEADER_APACHE_PROXY, $_SERVER)) {
            pinpoint_add_clue(PP_APACHE_PROXY, $_SERVER[PP_HEADER_APACHE_PROXY]);
        }

        if (isset($_SERVER[PP_HEADER_SAMPLED]) || array_key_exists(PP_HEADER_SAMPLED, $_SERVER)) {
            if ($_SERVER[PP_HEADER_SAMPLED] == PP_NOT_SAMPLED) {
                $this->isLimit = true;
                //drop this request. collector could not receive any thing
                pinpoint_drop_trace();
            }
        } else {
            $this->isLimit = pinpoint_tracelimit();
            echo $this->isLimit;
        }


        pinpoint_add_clue(PP_TRANSCATION_ID, $this->tid);
        pinpoint_add_clue(PP_SPAN_ID, $this->sid);
    }
    public function __destruct()
    {
        // reset limit
        $this->isLimit = false;
        pinpoint_add_clues(PP_HTTP_STATUS_CODE, http_response_code());
        pinpoint_end_trace();
    }

    public function generateSpanID()
    {
        try {
            $this->curNextSpanId = mt_rand(); //random_int(-99999999,99999999);
            return $this->curNextSpanId;
        } catch (\Exception $e) {
            return rand();
        }
    }

    public function getCurNextSpanId()
    {
        return $this->curNextSpanId;
    }

    public function generateTransactionID()
    {
        return  $this->app_id . '^' . strval(pinpoint_start_time()) . '^' . strval(pinpoint_unique_id());
    }
}
