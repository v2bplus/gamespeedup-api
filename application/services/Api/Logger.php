<?php
namespace Services\Api;

use Service;

class Logger extends Service
{
    public static $_instance = null;

    public static function getInstance($type = '_logger')
    {
        if (!self::$_instance) {
            self::$_instance = \Yaf_Registry::get($type);
        }
        return self::$_instance;
    }

    public static function info($message, array $context = [])
    {
        self::writeLog(__FUNCTION__, $message, $context);
    }

    public static function debug($message, array $context = [])
    {
        self::writeLog(__FUNCTION__, $message, $context);
    }

    public static function log($message, array $context = [])
    {
        self::writeLog(__FUNCTION__, $message, $context);
    }

    protected static function writeLog($level, $message, $context)
    {
        return self::getInstance()->{$level}($message, $context);
    }

    // protected function formatMessage($message)
    // {
    //     if (is_array($message)) {
    //         return var_export($message, true);
    //     } elseif ($message instanceof Jsonable) {
    //         return $message->toJson();
    //     } elseif ($message instanceof Arrayable) {
    //         return var_export($message->toArray(), true);
    //     }

    //     return $message;
    // }
}
