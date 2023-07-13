#!/usr/bin/env php
<?php
use Phinx\Console\PhinxApplication;

defined('DS') || define('DS', '/');

define('BASE_PATH', realpath(dirname(__FILE__).DS.'..'.DS.'..').DS);
define('APPLICATION_PATH', realpath(dirname(__FILE__).DS.'..').DS);

$app = new Yaf_Application(APPLICATION_PATH.'conf/application.ini');

$app->bootstrap();
$config = Yaf_Application::app()->getConfig()->toArray();

$app = new PhinxApplication();
$app->run();
