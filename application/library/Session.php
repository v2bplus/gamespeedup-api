<?php

class Session
{
    public static $instance;

    protected static $started = false;

    public static function getInstance()
    {
        if (! is_null(self::$instance)) {
            return self::$instance;
        }
        self::$instance = Yaf_Session::getInstance();
        return self::$instance;
    }

    public static function get($name)
    {
        return self::getInstance()->get($name);
    }

    public static function has($name)
    {
        return self::getInstance()->has($name);
    }

    public static function set($name, $value)
    {
        return self::getInstance()->set($name, $value);
    }

    public static function del($name)
    {
        return self::getInstance()->del($name);
    }

    public static function start()
    {
        self::getInstance()->start();
        self::$started = true;
        return true;
    }
}
