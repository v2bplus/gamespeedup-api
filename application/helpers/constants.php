<?php

// 来源参考微信开发文档全局返回码说明  定义一些全局返回码或者变量
define('API_WEAPP_LOGIN_FAILED', 'API_WEAPP_LOGIN_FAILED');
define('API_BAIDU_SPEECH_FAILED', 'API_BAIDU_SPEECH_FAILED');
define('API_CODE_SUCCESS', 0);
define('API_CODE_FAILURE', -1);
define('APP_CODE_SUCCESS', 0);
define('APP_CODE_FAILURE', 1);
define('EXCEPTION_ERROR_CODE', -1);
define('EXCEPTION_FILE_NOT_FOUND', -301);
define('EXCEPTION_FILE_EXISTS', -302);

// game模块
define('GAME_STATUS_SUCCESS', 200);
define('GAME_STATUS_ERROR', 500);
define('GAME_STATUS_NOT_LOGGED', 401);
define('GAME_NOT_ADMIN', 403);

// 系统升级中,请稍后再试
define('GAME_CLOSE', 405);
