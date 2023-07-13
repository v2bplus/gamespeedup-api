<?php

use Phpfastcache\CacheManager;
use Phpfastcache\Drivers\Redis\Config as RedisConfig;
use Phpfastcache\Helper\Psr16Adapter;

class Bootstrap extends Yaf_Bootstrap_Abstract
{
    public function _initTimeZone()
    {
        date_default_timezone_set('Asia/Shanghai');
    }

    public function _initType()
    {
        defined('CACHE_PATH') || define('CACHE_PATH', APPLICATION_PATH.'cache'.DS);
        defined('CONF_PATH') || define('CONF_PATH', APPLICATION_PATH.'conf'.DS);
        defined('DATA_PATH') || define('DATA_PATH', APPLICATION_PATH.'data'.DS);
        defined('LOGS_PATH') || define('LOGS_PATH', APPLICATION_PATH.'logs'.DS);
        $this->_bootType = 'web';
        if ('cli' == php_sapi_name()) {
            $this->_bootType = 'script';
        }
    }

    public function _initLoader(Yaf_Dispatcher $dispatcher)
    {
        $autoloadFile = BASE_PATH.'vendor/autoload.php';
        if (!file_exists($autoloadFile)) {
            $version = phpversion();
            if (version_compare($version, '8.0.0', '<')) {
                echo 'PHP版本需要升级.'."\n";
                exit(9);
            }
            $mod = 755;
            if (!is_dir(CACHE_PATH)) {
                if (!mkdir(CACHE_PATH, $mod)) {
                    echo 'cache目录权限不足.'."\n";
                    exit(9);
                }
            }
            $currentPerms = substr(decoct(fileperms(CACHE_PATH)), 2);
            if ($currentPerms != $mod) {
                chmod(CACHE_PATH, $mod);
            }
            if (!is_dir(LOGS_PATH)) {
                mkdir(LOGS_PATH, $mod);
            }
            $currentPerms = substr(decoct(fileperms(LOGS_PATH)), 2);
            if ($currentPerms != $mod) {
                chmod(LOGS_PATH, $mod);
            }
            echo '需要root在目录'.BASE_PATH.'下中执行 php install.php.'."\n";
            exit(9);
        }
        Yaf_Loader::import($autoloadFile);
    }

    public function _initEnv()
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
        $dotenv->safeLoad();
        if (isset($_ENV['ENVIRON'])) {
            define('ENVIRON', $_ENV['ENVIRON']);
        } else {
            define('ENVIRON', YAF_ENVIRON);
        }
        if (isset($_ENV['REDIS_HOST'])) {
            define('REDIS_HOST', $_ENV['REDIS_HOST']);
        } else {
            define('REDIS_HOST', '');
        }
        if (isset($_ENV['MYSQL_SERVER'])) {
            define('MYSQL_SERVER', $_ENV['MYSQL_SERVER']);
        } else {
            define('MYSQL_SERVER', '');
        }
    }

    public function _initConfig()
    {
        $this->_config = Yaf_Application::app()->getConfig();
        Yaf_Registry::set('_config', $this->_config);
        $dbConfig = $this->_config->mysql->toArray();
        if (ENVIRON == 'dev' || ENVIRON == 'test') {
            $dbConfig['logging'] = true;
        }
        if (MYSQL_SERVER != '') {
            $dbConfig['server'] = MYSQL_SERVER;
        }
        Yaf_Registry::set('_dbConfig', $dbConfig);
        $resqueConfig = $this->_config->resque->toArray();
        Yaf_Registry::set('_resqueConfig', $resqueConfig);
    }

    public function _initProject()
    {
        $siteConfig = $this->_config->site->toArray();
        Yaf_Registry::set('_siteConfig', $siteConfig);
        defined('UPLOAD_PATH') || define('UPLOAD_PATH', PUBLIC_PATH.'upload'.DS);
        defined('PROJECT_NAME') || define('PROJECT_NAME', $siteConfig['project_name']);
    }

    public function _initHelpers()
    {
        Yaf_Loader::import(APPLICATION_PATH.'/helpers/constants.php');
        Yaf_Loader::import(APPLICATION_PATH.'/helpers/functions.php');
    }

    public function _initErrors()
    {
        if (ENVIRON == 'dev') {
            error_reporting(-1);
            ini_set('display_errors', 'On');
            ini_set('display_startup_errors', 1);
        // } elseif (ENVIRON == 'test') {
        //     HandleExceptions::register();
        } else {
            HandleExceptions::register();
        }
    }

    public function _initSentry(Yaf_Dispatcher $dispatcher)
    {
        if (ENVIRON == 'test' || ENVIRON == 'product') {
            // $sentryConfig = $this->_config->sentry->toArray();
            // if ($sentryConfig) {
            //     \Sentry\init([
            //         'dsn' => $sentryConfig['dsn'],
            //         'environment' => ENVIRON,
            //     ]);
            // }
        }
    }

    public function _initRoute(Yaf_Dispatcher $dispatcher)
    {
        $route = new Yaf_Config_Ini(APPLICATION_PATH.'/conf/routes.ini', YAF_ENVIRON);
        $dispatcher->getRouter()->addConfig($route);
    }

    public function _initCache(Yaf_Dispatcher $dispatcher)
    {
        $rs = $this->_config->redis;
        if ($rs) {
            $redisConfig = $rs->toArray();
            $redis = new Redis();
            if (REDIS_HOST != '') {
                $redisConfig['host'] = REDIS_HOST;
            }
            $redis->connect($redisConfig['host'], $redisConfig['port'], 3);
            if (isset($redisConfig['auth'])) {
                $redis->auth($redisConfig['auth']);
            }
            Yaf_Registry::set('_redis', $redis);
            $config = new RedisConfig();
            $config->setHost($redisConfig['host']);
            $config->setPort((int) $redisConfig['port']);
            if (isset($redisConfig['auth'])) {
                $config->setPassword($redisConfig['auth']);
            }
            if (isset($redisConfig['database'])) {
                $config->setDatabase($redisConfig['database']);
            }
            if (isset($redisConfig['timeout'])) {
                $config->setTimeout($redisConfig['timeout']);
            }
            if (isset($redisConfig['ttl'])) {
                $config->setDefaultTtl($redisConfig['ttl']);
            }
            $config->setOptPrefix(PROJECT_NAME.'_');
            $cacheinstance = CacheManager::getInstance('Redis', $config);
            Yaf_Registry::set('_cache', $cacheinstance);
        }
        $InstanceCache = new Psr16Adapter('Apcu');
        Yaf_Registry::set('_localCache', $InstanceCache);
    }

    public function _initMonolog(Yaf_Dispatcher $dispatcher)
    {
        $monologConfig = $this->_config->monolog->toArray();
        if (isset($monologConfig['sql'])) {
            $sqlConfig = $monologConfig['sql'];
            $logger = new Monolog\Logger($sqlConfig['channel']);
            $stream = new Monolog\Handler\StreamHandler($sqlConfig['path'], Monolog\Logger::DEBUG);
            $logger->pushHandler($stream);

            $processors = explode(',', $sqlConfig['processors']);
            foreach ($processors as $processor) {
                $logger->pushProcessor(new $processor());
            }
            Yaf_Registry::set('_sqllogger', $logger);
        }
        if ('web' == $this->_bootType) {
            $logger = new Monolog\Logger($monologConfig['channel']);
            $stream = new Monolog\Handler\StreamHandler($monologConfig['path'], Monolog\Logger::DEBUG);
            $logger->pushHandler($stream);
            $processors = explode(',', $monologConfig['processors']);
            foreach ($processors as $processor) {
                $logger->pushProcessor(new $processor());
            }
            Yaf_Registry::set('_logger', $logger);
        } else {
            if (isset($monologConfig['cli'])) {
                $cliConfig = $monologConfig['cli'];
                $logger = new Monolog\Logger($cliConfig['channel']);

                $stream = new Monolog\Handler\StreamHandler($cliConfig['path'], Monolog\Logger::DEBUG);
                $logger->pushHandler($stream);
                $processors = explode(',', $cliConfig['processors']);
                foreach ($processors as $processor) {
                    $logger->pushProcessor(new $processor());
                }
                Yaf_Registry::set('_logger', $logger);
            }
        }
    }

    public function _initMail(Yaf_Dispatcher $dispatcher)
    {
        $mailConfig = $this->_config->mail->toArray();
        Yaf_Registry::set('_mailer', $mailConfig);
    }

    public function _initPlugin(Yaf_Dispatcher $dispatcher)
    {
        // web模式下才加载
        if ('web' == $this->_bootType) {
            $dispatcher->registerPlugin(new RouterPlugin());
            $dispatcher->registerPlugin(new TwigPlugin());
        }
        if (ENVIRON == 'dev' || ENVIRON == 'test') {
            // $dispatcher->registerPlugin(new MysqlQueryLogPlugin());
        }
    }

    public function _initI18n(Yaf_Dispatcher $dispatcher)
    {
        // Yaf_Registry::set('_lang', $lang);
    }
}
