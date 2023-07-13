<?php
use Carbon\Carbon;

class Service
{
    public static $error;

    public static function setErrorMsg($msg)
    {
        self::$error = $msg;
    }

    public static function getErrorMsg()
    {
        return self::$error;
    }

    public static function condition($params = [], $array = [])
    {
        $where = [];
        foreach ($array as $key => $value) {
            if (!isset($params[$key])) {
                continue;
            }
            if ($value === '' || $value === '0') {
                continue;
            }
            $newKey = $params[$key];
            if (is_array($value)) {
                $dtStart = Carbon::parse($value[0]);
                $dtEnd = Carbon::parse($value[1]);
                $value = [$dtStart->startOfDay()->timestamp, $dtEnd->endOfDay()->timestamp];
                $where['"'.$newKey.'[<>]"'] = $value;
            } else {
                $where[$newKey] = $value;
            }
        }
        return $where;
    }

    public static function checkFrequency($key, $second)
    {
        if ($key === null) {
            return false;
        }
        $cacheKey = get_called_class().':'.$key;
        $now = microtime(true);
        $lastTime = LocalCache::get($cacheKey);
        LocalCache::set($cacheKey, $now, $second * 10);
        if ($now - $lastTime <= $second) {
            return false;
        }
        return true;
    }
}
