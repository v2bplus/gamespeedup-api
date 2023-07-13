<?php

class Response
{
    public const SUCCESS = 200;
    public const FAIL = 400;
    private static $phrases = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];

    public static function http($code = 200, $headers = [])
    {
        $protocol = Yaf_Dispatcher::getInstance()->getRequest()->getServer('SERVER_PROTOCOL', 'HTTP/1.1');
        $string = self::$phrases[$code] ?? '';
        header("{$protocol} {$code} {$string}");
        if ($headers) {
            foreach ($headers as $k => $v) {
                header($k.': '.$v, true);
            }
        }
    }

    public static function fail($data = [], $message = 'fail')
    {
        self::errorJson(self::FAIL, $data, $message);
    }

    public static function errorJson($errno, $data = [], $message = '')
    {
        $ret = [
            'errno' => $errno,
            'errmsg' => $message,
            'timestamp' => time(),
            'data' => $data,
        ];
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        exit(23);
    }

    public static function httpJson($code = 0, $message = '', $data = [], $httpCode = self::SUCCESS, $exit = true)
    {
        self::http($httpCode);
        if (!isset($data) || [] == $data) {
            $data = (object) $data;
        }
        $ret = [
            'code' => $code,
            'msg' => $message,
            'data' => $data,
        ];
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        if ($exit) {
            exit;
        }
    }

    public static function appJson($code, $message = '', $data = null, $exit = true)
    {
        if (is_array($message)) {
            $message = array_values($message)[0];
        }
        if (!isset($data) || [] == $data) {
            $data = (object) $data;
        }
        $ret = [
            'code' => $code,
            'msg' => $message,
            'data' => $data,
        ];
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        if ($exit) {
            exit;
        }
    }

    public static function renderJson($code, $message = '', $data = [], $exit = true)
    {
        if (is_array($message)) {
            $message = array_values($message)[0];
        }
        if (!isset($data) || [] == $data) {
            $data = (object) $data;
        }
        $ret = [
            'code' => $code,
            'data' => $data,
            'msg' => $message,
        ];
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        if ($exit) {
            exit;
        }
    }

    public static function raw($data, $type = 'json')
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function corsHeader(array $cors)
    {
        $from = $_SERVER['HTTP_ORIGIN'] ??
        ($_SERVER['HTTP_REFERER'] ?? null);
        if ($from) {
            $domains = $cors['Access-Control-Allow-Origin'];
            if ('*' !== $domains) {
                $domain = strtok($domains, ',');
                while ($domain) {
                    if (0 === strpos($from, rtrim($domain, '/'))) {
                        $cors['Access-Control-Allow-Origin'] = $domain;

                        break;
                    }
                    $domain = strtok(',');
                }
                if (!$domain) {
                    // 非请指定的求来源,自动终止响应
                    header('Forbid-Origin: '.$from);

                    return;
                }
            } elseif ('true' === $cors['Access-Control-Allow-Credentials']) {
                // 支持多域名和cookie认证,此时修改源
                $cors['Access-Control-Allow-Origin'] = $from;
            }
            foreach ($cors as $key => $value) {
                header($key.': '.$value);
            }
        }
    }
}
