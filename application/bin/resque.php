#!/usr/bin/env php
<?php
use Symfony\Component\Yaml\Yaml;

defined('DS') || define('DS', '/');
define('BASE_PATH', realpath(dirname(__FILE__).DS.'..'.DS.'..').DS);
define('PUBLIC_PATH', BASE_PATH.'public'.DS);
define('APPLICATION_PATH', realpath(dirname(__FILE__).DS.'..').DS);
define('BIN_PATH', APPLICATION_PATH.'bin'.DS);

$app = new Yaf_Application(APPLICATION_PATH.'conf/application.ini');
$app->bootstrap();
$config = Yaf_Application::app()->getConfig()->toArray();

define('RESQUE_BIN_DIR', realpath(__DIR__));
define('RESQUE_DIR', realpath(RESQUE_BIN_DIR.'/../'));
$files = [
    RESQUE_DIR.'/vendor/autoload.php',
    RESQUE_DIR.'/../vendor/autoload.php',
];
$loaded = false;
foreach ($files as $file) {
    if (file_exists($file)) {
        require_once $file;
        $loaded = true;

        break;
    }
}
if (!$loaded) {
    echo '<pre>You need to set up the project dependencies using the following commands:'.PHP_EOL.
    "\t".'$ curl -s http://getcomposer.org/installer | php'.PHP_EOL.
    "\t".'$ php composer.phar install</pre>'.PHP_EOL;
    exit(1);
}

if (!isset($config['resque'])) {
    echo '<pre> No resque config </pre>'.PHP_EOL;
    exit(1);
}
if (!isset($config['redis'])) {
    echo '<pre> No redis config </pre>'.PHP_EOL;
    exit(1);
}
$eventfile = APPLICATION_PATH.'plugins'.DS.'ResquePlugin.php';
if (file_exists($eventfile)) {
    include $eventfile;
}
$resqueConfig = $config['resque'];
$systemRedis = $config['redis'];
$workerConfig = $resqueConfig['worker'];
$configFile = APPLICATION_PATH.'conf'.DS.$resqueConfig['config'];
$writeConfig = $redisConfig = $defaultConfig = $workConfig = $logConfig = [];
$redisConfig['scheme'] = 'tcp';
if (defined('REDIS_HOST') && (REDIS_HOST != '')) {
    $redisConfig['host'] = REDIS_HOST;
} else {
    $redisConfig['host'] = $systemRedis['host'] ?? '127.0.0.1';
}
$redisConfig['port'] = $systemRedis['port'] ?? 6379;
if (isset($systemRedis['auth'])) {
    $redisConfig['password'] = $systemRedis['auth'];
}
$redisConfig['namespace'] = PROJECT_NAME.'_'.$resqueConfig['prefix'];
$redisTimeout = $resqueConfig['rw_timeout'] ?? 60;
$redisConfig['rw_timeout'] = (int) $redisTimeout;
$redisConfig['phpiredis'] = false;

$writeConfig['redis'] = $redisConfig;
$defaultConfig['expiry_time'] = $resqueConfig['expiry_time'] ?? 604800;
$verbose = $resqueConfig['verbose'] ?? 4;
$defaultConfig['verbose'] = (int) $verbose;
$workConfig['queue'] = $workerConfig['queue'] ?? 'default';
$workConfig['blocking'] = $workerConfig['blocking'] ?? true;
$interval = $workerConfig['interval'] ?? 1;
$workConfig['interval'] = (int) $interval;
$workConfig['timeout'] = $workerConfig['timeout'] ?? 60;
$workConfig['memory'] = $workerConfig['memory'] ?? 64;
$logConfig = $resqueConfig['log'];

if (isset($logConfig['console'])) {
    $logConfig['console'] = true;
} elseif (isset($logConfig['rorate'])) {
    $logConfig['rorate'] = $logConfig['rorate'];
}

$defaultConfig['workers'] = $workConfig;
$writeConfig['default'] = $defaultConfig;
$writeConfig['log'] = $logConfig;

$yaml = Yaml::dump($writeConfig, 3);
file_put_contents($configFile, $yaml);

$application = new Symfony\Component\Console\Application('php-resque', Resque::VERSION);
$application->add(new Resque\Commands\Clear());
$application->add(new Resque\Commands\Hosts());
$application->add(new Resque\Commands\Queues());
$application->add(new Resque\Commands\Cleanup());
$application->add(new Resque\Commands\Workers());

$application->add(new Resque\Commands\Job\Queue());

$application->add(new Resque\Commands\Socket\Send());
$application->add(new Resque\Commands\Socket\Receive());
$application->add(new Resque\Commands\Socket\Connect());

$application->add(new Resque\Commands\Worker\Start());
$application->add(new Resque\Commands\Worker\Stop());
$application->add(new Resque\Commands\Worker\Restart());
$application->add(new Resque\Commands\Worker\Pause());
$application->add(new Resque\Commands\Worker\Resume());
$application->add(new Resque\Commands\Worker\Cancel());
$application->run();
