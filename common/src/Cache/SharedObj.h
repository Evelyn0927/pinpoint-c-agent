/*******************************************************************************
 * Copyright 2019 NAVER Corp
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License.  You may obtain a copy
 * of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.  See the
 * License for the specific language governing permissions and limitations under
 * the License.
 ******************************************************************************/
//
// Created by eeliu on 12/8/2019.
//

#ifndef COMMON_SHAREDOBJ_H
#define COMMON_SHAREDOBJ_H

#include "common.h"
#include <time.h>
#ifdef __cplusplus 
extern "C"{
#endif

#ifndef NAME_MAX
#define NAME_MAX 256
#endif

#define SHM_FILE_PREFIX_SIZE 32
typedef struct shared_object_s{
    void*  region;
    int    length;
    char   fileprefix[SHM_FILE_PREFIX_SIZE];
}SharedObject_T;

// void* fetch_shared_obj_addr();
// bool checking_and_init();
// void detach_shared_obj();

bool attach_shared_memory(SharedObject_T* shm_obj);
bool detach_shared_memory(SharedObject_T* shm_obj);
time_t get_shared_memory_create_time(SharedObject_T* shm_obj);
#ifdef __cplusplus
}
#endif

#endif //COMMON_SHAREDOBJ_H
