<?php
class LocalCache
{
    private static $maxLifetime = 86400;
    public static $instance;

    private function __clone()
    {
        trigger_error('clone is not allowed!');
    }

    public static function getInstance()
    {
        if (!(self::$instance)) {
            self::$instance = Yaf_Registry::get('_localCache');
        }
        return self::$instance;
    }

    public static function get(string $name, $default = null)
    {
        return self::getInstance()->get($name, $default);
    }

    public static function check(string $name)
    {
        return self::getInstance()->has($name);
    }

    public static function set(string $name, $data, $ttl = 0)
    {
        if ($ttl) {
            return self::getInstance()->set($name, $data, $ttl);
        }
        return self::getInstance()->set($name, $data, self::$maxLifetime);
    }

    public static function del(string $name)
    {
        return self::getInstance()->delete($name);
    }

    public static function getMultiple($name, $default = null)
    {
        return self::getInstance()->getMultiple($name, $default);
    }

    public static function setMultiple($values, $ttl = null)
    {
        return self::getInstance()->setMultiple($values, $ttl);
    }

    public static function flushAll()
    {
        return self::getInstance()->clear();
    }
}
