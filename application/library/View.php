<?php

class View
{
    const MINUTE_IN_SECONDS = 60;
    const HOUR_IN_SECONDS = 3600;
    const DAY_IN_SECONDS = 86400;
    const WEEK_IN_SECONDS = 604800;
    const YEAR_IN_SECONDS = 3.15569e7;

    public static function url($route, $params = array())
    {
        $moduleName = Yaf_Dispatcher::getInstance()->getRequest()->getModuleName();
        $controllerName = Yaf_Dispatcher::getInstance()->getRequest()->getControllerName();
        $actionName = Yaf_Dispatcher::getInstance()->getRequest()->getActionName();
        $moduleName = strtolower($moduleName);
        if ($route[0] == '/') {
            $arr = Yaf_Dispatcher::getInstance()->getRequest()->getParams();
            $params = array_merge($arr, $params);
            if (isset($params['page'])) {
                unset($params['page']);
                $params['page'] = '';
            }
            $route = strtolower($moduleName).'/'.strtolower($controllerName).'/'.strtolower($actionName);
        }
        $url = $route;
        $url = rtrim($url, '/');
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                if (empty($value) && $key != 'page') {
                    continue;
                }
                $url .= '/'.$key.'/'.$value;
            }
        }
        $url = preg_replace('/index\/index$/i', '', $url);
        return $url;
    }

    public static function formatTime($time)
    {
        if ($time === null) {
            return '-';
        }
        return date('Y-m-d H:i:s', $time);
    }

    public static function prettyPrintJson($json)
    {
        return json_encode(json_decode($json, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public static function formatHumanTimeDiff($from, $to = null)
    {
        $to = $to ?: time();

        $diff = (int) abs($to - $from);

        if ($diff < self::MINUTE_IN_SECONDS) {
            $since = array($diff, 'sec');
        } elseif ($diff < self::HOUR_IN_SECONDS) {
            $since = array(round($diff / self::MINUTE_IN_SECONDS), 'min');
        } elseif ($diff < self::DAY_IN_SECONDS and $diff >= self::HOUR_IN_SECONDS) {
            $since = array(round($diff / self::HOUR_IN_SECONDS), 'hour');
        } elseif ($diff < self::WEEK_IN_SECONDS and $diff >= self::DAY_IN_SECONDS) {
            $since = array(round($diff / self::DAY_IN_SECONDS), 'day');
        } elseif ($diff < 30 * self::DAY_IN_SECONDS and $diff >= self::WEEK_IN_SECONDS) {
            $since = array(round($diff / self::WEEK_IN_SECONDS), 'week');
        } elseif ($diff < self::YEAR_IN_SECONDS and $diff >= 30 * self::DAY_IN_SECONDS) {
            $since = array(round($diff / (30 * self::DAY_IN_SECONDS)), 'month');
        } elseif ($diff >= self::YEAR_IN_SECONDS) {
            $since = array(round($diff / self::YEAR_IN_SECONDS), 'year');
        }

        if ($since[0] <= 1) {
            $since[0] = 1;
        }

        return $since[0].' '.$since[1].($since[0] == 1 ? '' : 's');
    }

    public static function asset(...$args): string
    {
        if (func_num_args() === 1) {
            $uri = $args[0];
        } else {
            list($context, $uri) = $args;
        }
        if (strpos($uri, '://') !== false || strpos($uri, '.min') !== false || 'product' === Yaf_Application::app()->environ()) {
            return $uri;
        }
        $file = PUBLIC_PATH.$uri;
        if (is_file($file)) {
            $uri .= '?_'.filemtime($file);
        } elseif (is_file($file.'.js')) {
        } else {
            $uri = '';
        }
        return $uri;
    }
}
