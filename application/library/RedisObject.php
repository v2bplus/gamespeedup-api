<?php

class RedisObject
{
    public static $instance;
    private function __clone()
    {
        trigger_error('clone is not allowed!');
    }

    public static function limits()
    {
        return [
            'info', 'slaveOf', 'gbRewriteAOF', 'gbSave', 'config', 'dbSize', 'flushAll', 'flushDb', 'info', 'lastSave', 'save', 'time', 'slowLog', 'pSubscribe', 'publish', 'subscribe', 'pubSub', 'flushall', 'eval', 'evalSha', 'script'
        ];
    }

    public static function getInstance()
    {
        if (!(self::$instance)) {
            self::$instance = Yaf_Registry::get('_redis');
            // self::$instance->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
            self::$instance->setOption(Redis::OPT_PREFIX, PROJECT_NAME. ':');
        }
        return self::$instance;
    }

    // redis mq
    public static function product($message, $queueName)
    {
        return self::getInstance()->lPush($queueName, $message);
    }

    public static function consume($count = 1, $queueName)
    {
        $messages = [];
        for ($i = 0; $i < $count; ++$i) {
            if ($item = self::getInstance()->rPop()) {
                $messages[] = $item;
            }
        }
        return (!$messages || count($messages) > 1) ? $messages : $messages[0];
    }

    public static function __callStatic($methodName, $parameters)
    {
        try {
            if (in_array($methodName, self::limits())) {
                throw new \Exception('Method Not Allowed');
            }
            $res = call_user_func_array([self::getInstance(), $methodName], $parameters);
            return $res;
        } catch (\Exception $e) {
            return false;
        }
    }
}
