<?php
defined('DS') || define('DS', DIRECTORY_SEPARATOR);

define('PUBLIC_PATH', dirname(__FILE__).DS);
define('BASE_PATH', realpath(dirname(__FILE__).DS.'..').DS);
define('APPLICATION_PATH', BASE_PATH.'application'.DS);
defined('BIN_PATH') || define('BIN_PATH', APPLICATION_PATH.'bin'.DS);
$HTTPS = $_SERVER['HTTPS'] ?? ($HTTP_SERVER_VARS['HTTPS'] ?? 'off');
if ($HTTPS == '1' || $HTTPS == 'on') {
    $SCHEME = 'https://';
} else {
    $SCHEME = 'http://';
}
$HTTPHOST = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
defined('HOST') || define('HOST', $SCHEME.$HTTPHOST.DS);
defined('YAF_BEGIN_TIME') || define('YAF_BEGIN_TIME', microtime(true));
$app = new Yaf_Application(APPLICATION_PATH.'conf/application.ini');
$app->bootstrap()->run();
