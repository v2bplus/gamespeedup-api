<?php
namespace Services\Api;

use Exception;
use LocalCache as Local;
use Service;

class LocalCache extends Service
{
    public static function add($cacheKey, $data, $maxLifetime = 0)
    {
        try {
            Local::set($cacheKey, $data, $maxLifetime);
            return [
                'status' => 1,
                'data' => [],
                'msg' => ''
            ];
        } catch (Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }

    public static function del($cacheKey)
    {
        try {
            if (Local::check($cacheKey)) {
                Local::del($cacheKey);
            }
            return [
                'status' => 1,
                'data' => [],
                'msg' => ''
            ];
        } catch (Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }

    public static function get($cacheKey)
    {
        try {
            $data = Local::get($cacheKey);
            return [
                'status' => 1,
                'data' => $data,
                'msg' => ''
            ];
        } catch (Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }
}
