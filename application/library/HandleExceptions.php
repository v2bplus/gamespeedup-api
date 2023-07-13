<?php
class HandleExceptions
{
    public static function register()
    {
        error_reporting(-1);
        set_error_handler([static::class, 'handleError']);
        set_exception_handler([static::class, 'handleException']);
        register_shutdown_function([static::class, 'handleShutdown']);
    }

    public static function handleError($level, $message, $file = '', $line = 0)
    {
        if (error_reporting() & $level) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }

    public static function handleException($e)
    {
        if ($e instanceof \Yaf_Exception) {
            $type = 'yaf exception';
        } elseif ($e instanceof \PDOException) {
            $type = 'db exception';
        } else {
            $type = 'unknow exception';
        }
        $log = Yaf_Registry::get('_logger');
        $log->info($type, [
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        static::renderHttpResponse($e);
        if (ENVIRON == 'test' || ENVIRON == 'product') {
            \Sentry\captureLastError();
        }
    }

    protected static function renderHttpResponse($e)
    {
        Response::fail('出错啦,服务器已经记录错误');
    }

    public static function handleShutdown()
    {
        if (!is_null($error = error_get_last()) && static::isFatal($error['type'])) {
            static::handleException(
                new \ErrorException(
                    $error['message'],
                    $error['type'],
                    0,
                    $error['file'],
                    $error['line']
                )
            );
        }
    }

    protected static function isFatal($type)
    {
        return in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]);
    }
}
