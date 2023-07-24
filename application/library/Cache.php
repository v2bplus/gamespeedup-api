<?php

class Cache
{
    private static $instance;

    private function __clone()
    {
        trigger_error('clone is not allowed!');
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = Yaf_Registry::get('_cache');
        }

        return self::$instance;
    }

    public static function get($name)
    {
        return self::getInstance()->getItem($name)->get();
    }

    public static function check($name)
    {
        return self::getInstance()->hasItem($name);
    }

    public static function set($name, $data, $maxLifetime = 0)
    {
        $item = self::getInstance()->getItem($name);
        $item->set($data);
        if ($maxLifetime > 0) {
            $item = $item->expiresAfter($maxLifetime);
        }
        return self::getInstance()->save($item);
    }

    public static function update($name, $newVal, $maxLifetime = 0, $isMerge = true)
    {
        $item = self::getInstance()->getItem($name);
        $value = $item->get();
        if (is_array($value) && is_array($newVal) && $isMerge) {
            $newVal = array_merge($value, $newVal);
        }
        $item->set($newVal);
        if ($maxLifetime > 0) {
            $item = $item->expiresAfter($maxLifetime);
        }

        return self::getInstance()->save($item);
    }

    public static function del($name)
    {
        return self::getInstance()->deleteItem($name);
    }

    public static function deleteAll()
    {
        // todo
        // api会清除redis的所有数据 暂时不使用
        // return self::getInstance()->clear();
    }

    public static function getStats()
    {
        return self::getInstance()->getStats();
    }
}
