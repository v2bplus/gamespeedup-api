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
        if (!$maxLifetime) {
            $item = $item->expiresAfter($maxLifetime);
        }
        return self::getInstance()->save($item);
    }

    public static function update($name, $newVal, $maxLifetime = 0, $isMerge = true)
    {
        var_dump(self::getInstance());
        exit;

        // $cache = self::getInstance()->getItem($name);
        $value = $cache->get();
        if (is_array($value) && is_array($value)) {
            $newVal = array_merge($value, $newVal);
        }
        $cache->set($newVal);
        if ($maxLifetime) {
            $cache->expiresAfter($maxLifetime);
        }

        return self::getInstance()->save($cache);
    }

    public static function del($name)
    {
        // todo
        // 官方的deleteitem 有bug 暂时先用过期时间实现
        // $cache = self::getInstance()->getItem($name)->set(false)->expiresAfter(1);

        return self::getInstance()->save($cache);
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
