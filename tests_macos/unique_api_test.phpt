--TEST--
Test pinpoint_unique_id
--SKIPIF--
<?php if (!extension_loaded("pinpoint_php")) print "skip"; ?>
--INI--
pinpoint_php.CollectorHost=tcp:localhost:10000
pinpoint_php.SendSpanTimeOutMs=0
pinpoint_php.UnitTest=true
pinpoint_php.DebugReport=true
--FILE--
<?php
 
$v1= _pinpoint_unique_id();
$v2= _pinpoint_unique_id();
$v3= _pinpoint_unique_id();
var_dump( $v2*2 === $v1+$v3 );

--EXPECTF--
bool(true)