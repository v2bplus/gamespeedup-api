#!/usr/bin/env php
<?php
/**
 * 命令行执行
 * php cli.php "/module/appdemo/sendmailt?a=1&b=c"
 * php cli.php "/{模块名字}/{控制器名字}/{方法名字}?{参数}"
 *
 */

defined('DS') || define('DS', '/');
define('BASE_PATH', realpath(dirname(__FILE__).DS.'..'.DS.'..').DS);
define('PUBLIC_PATH', BASE_PATH.'public'.DS);
define('APPLICATION_PATH', BASE_PATH.'application'.DS);
defined('BIN_PATH') || define('BIN_PATH', APPLICATION_PATH.'bin'.DS);
$app = new Yaf_Application(APPLICATION_PATH.'conf/application.ini');
$app->bootstrap();
$modules = Yaf_Application::app()->getModules();

$request = new Yaf_Request_Simple();
if ($request->isCli()) {
    global $argc, $argv;
    if ($argc > 1) {
        $module = '';
        $uri = $argv[1];
        $uriInfo = explode('/', trim($uri, '/'));
        $module = ucfirst(array_shift($uriInfo));
        $controller = array_shift($uriInfo);
        $action = array_shift($uriInfo);

        if (in_array(ucfirst(strtolower($module)), $modules)) {
            $request->setModuleName($module);
        }
        if (false === strpos($uri, '?')) {
            $args = array();
        } else {
            list($uri, $args) = explode('?', $uri, 2);
            parse_str($args, $args);
        }
        foreach ($args as $k => $v) {
            $request->setParam($k, $v);
        }
        $request->setRequestUri($uri);
        $app->getDispatcher()->dispatch($request);
    }
}
