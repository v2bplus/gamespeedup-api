<?php

namespace Services\Login;

class Token extends \Service
{
    public const TOKEN_CACHE_TIME = 86400 * 7;
    public const TOKEN_FILED = 'Token';

    public static function login($userInfo, $tokenTime = self::TOKEN_CACHE_TIME)
    {
        $token = self::makeToken((int) $userInfo['id']);
        \Cache::set($token, [
            'user' => $userInfo,
            'is_login' => true,
        ], $tokenTime);

        return $token;
    }

    public static function logout($tokenField = self::TOKEN_FILED)
    {
        $key = self::getToken($tokenField);
        if ($key) {
            \Cache::del($key);
        }

        return true;
    }

    public static function getLoginInfo($tokenField)
    {
        if (($token = self::getToken($tokenField)) !== false) {
            return \Cache::get($token);
        }

        return false;
    }

    public static function getHttpHeader(string $key)
    {
        $all = getallheaders();

        return $all[$key] ?? '';
    }

    protected static function makeToken(int $userId)
    {
        // 生成一个不会重复的随机字符串
        $guid = \Utility::getGuid();
        $timeStamp = microtime(true);
        $salt = PROJECT_NAME ?? '_salt_';

        return md5("{$timeStamp}_{$userId}_{$guid}_{$salt}");
    }

    private static function getToken($tokenField = self::TOKEN_FILED)
    {
        $token = self::getHttpHeader($tokenField);
        if (empty($token)) {
            return false;
        }

        return $token;
    }
}
