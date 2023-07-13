<?php

class Http
{
    private static $postPayload;

    public static function clientIp(): string
    {
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $addresses = explode(',', getenv('HTTP_X_FORWARDED_FOR'));
            $ipaddr = reset($addresses);
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $ipaddr = getenv('HTTP_CLIENT_IP');
        } else {
            $ipaddr = getenv('REMOTE_ADDR');
        }

        return trim($ipaddr);
    }

    public static function getReferer(): string
    {
        if (isset($_SERVER['HTTP_REFERER'])) {
            return $_SERVER['HTTP_REFERER'];
        }

        return '';
    }

    public static function httpPost($url, $param = [], $headers = [], $config = [])
    {
        try {
            $config['timeout'] = Utility::arrayGet($config, 'timeout', 0);
            $config['connect_timeout'] = Utility::arrayGet($config, 'connect_timeout', 0);
            // $headers['User-Agent'] = Utility::arrayGet($headers, 'User-Agent', PROJECT_NAME);
            $client = new GuzzleHttp\Client($config);
            $data = $param;
            $data['headers'] = $headers;
            // $data['debug'] = true;
            $request = $client->request('POST', $url, $data);
        } catch (GuzzleHttp\Exception\RequestException $e) {
            return [
                'success' => 0,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        } catch (GuzzleHttp\Exception\InvalidArgumentException $e) {
            return [
                'success' => 0,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'msg' => '参数不正确',
            ];
        }
        $httpCode = $request->getStatusCode();
        $header = $request->getHeaders();
        $return = $request->getBody()->getContents();
        if (200 != $httpCode) {
            return [
                'success' => 0,
                'code' => $httpCode,
                'message' => '',
            ];
        }

        return [
            'success' => 1,
            'code' => $httpCode,
            'header' => $header,
            'data' => $return,
        ];
    }

    public static function httpGet(string $url, array $query = [], array $headers = [], array $config = [])
    {
        try {
            $config['timeout'] = Utility::arrayGet($config, 'timeout', 0);
            $config['connect_timeout'] = Utility::arrayGet($config, 'connect_timeout', 0);
            $headers['User-Agent'] = Utility::arrayGet($headers, 'User-Agent', PROJECT_NAME);
            $client = new GuzzleHttp\Client($config);
            $data = [];
            if (!empty($query)) {
                $data = ['query' => $query];
            }
            $data['headers'] = $headers;
            $request = $client->request('GET', $url, $data);
        } catch (GuzzleHttp\Exception\RequestException $e) {
            return [
                'success' => 0,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
        $httpCode = $request->getStatusCode();
        $return = $request->getBody()->getContents();
        if (200 != $httpCode) {
            return [
                'success' => 0,
                'code' => $httpCode,
                'message' => '',
            ];
        }

        return [
            'success' => 1,
            'code' => $httpCode,
            'data' => $return,
        ];
    }

    public static function request($method, $url, $options = [], $resolveResponse = true, $stream = false)
    {
        try {
            if ($stream) {
                $options['stream'] = true;
            }
            $client = new GuzzleHttp\Client($options);
            $response = $client->request($method, $url, $options);
            if ($stream) {
                $return = null;
                $body = $response->getBody();
                while (!$body->eof()) {
                    $return = $return.$body->read(1024);
                }

                return [
                    'success' => 1,
                    'data' => $return,
                    'message' => '',
                ];
            }

            if ($resolveResponse) {
                $return = json_decode($response->getBody()->getContents(), true);
            } else {
                $return = $response;
            }

            return [
                'success' => 1,
                'data' => $return,
                'message' => '',
            ];
        } catch (GuzzleHttp\Exception\RequestException $e) {
            return [
                'success' => 0,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }

    public static function getUrlSize(string $url, array $headers = [], array $config = []): int
    {
        $size = 0;

        try {
            $config['timeout'] = Utility::arrayGet($config, 'timeout', 3);
            $config['connect_timeout'] = Utility::arrayGet($config, 'connect_timeout', 0);
            $headers['User-Agent'] = Utility::arrayGet($headers, 'User-Agent', PROJECT_NAME);
            $client = new GuzzleHttp\Client($config);
            $data = [];
            $data['headers'] = $headers;
            $response = $client->request('HEAD', $url, $data);
        } catch (GuzzleHttp\Exception\RequestException $e) {
            return 0;
        }

        return intval($response->getHeaderLine('Content-Length'));
    }

    public static function getHttpHeader(string $headerKey)
    {
        $headerKey = strtoupper($headerKey);
        $headerKey = str_replace('-', '_', $headerKey);
        $headerKey = 'HTTP_'.$headerKey;

        return $_SERVER[$headerKey] ?? '';
    }

    // todo  修改为Guzzle方式
    // public static function curl_multi(array $data, array $options = array()): void
    // {
    //     $handles = $contents = array();
    //     //初始化curl multi对象
    //     $mh = curl_multi_init();
    //     //添加curl 批处理会话
    //     foreach ($data as $key => $value) {
    //         $url = (is_array($value) && !empty($value['url'])) ? $value['url'] : $value;
    //         $handles[$key] = curl_init($url);
    //         curl_setopt($handles[$key], CURLOPT_RETURNTRANSFER, 1);
    //         //判断是否是post
    //         if (is_array($value)) {
    //             if (!empty($value['post'])) {
    //                 curl_setopt($handles[$key], CURLOPT_POST, 1);
    //                 curl_setopt($handles[$key], CURLOPT_POSTFIELDS, $value['post']);
    //             }
    //         }
    //         //extra options?
    //         if (!empty($options)) {
    //             curl_setopt_array($handles[$key], $options);
    //         }
    //         curl_multi_add_handle($mh, $handles[$key]);
    //     }
    //     //======================执行批处理句柄=================================
    //     $active = null;
    //     do {
    //         $mrc = curl_multi_exec($mh, $active);
    //     } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    //     while ($active and $mrc == CURLM_OK) {
    //         if (curl_multi_select($mh) === -1) {
    //             usleep(100);
    //         }
    //         do {
    //             $mrc = curl_multi_exec($mh, $active);
    //         } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    //     }
    //     //====================================================================
    //     //获取批处理内容
    //     foreach ($handles as $i => $ch) {
    //         $content = curl_multi_getcontent($ch);
    //         $contents[$i] = curl_errno($ch) == 0 ? $content : '';
    //     }
    //     //移除批处理句柄
    //     foreach ($handles as $ch) {
    //         curl_multi_remove_handle($mh, $ch);
    //     }
    //     //关闭批处理句柄
    //     curl_multi_close($mh);
    //     return $contents;
    // }

    // public static function getHttpPost($key, $trim = true, $removeXss = false)
    // {
    //     if ($trim) {
    //         $key = trim($key);
    //     }
    //     $content = $_POST[$key] ?? '';
    //     if ($removeXss) {
    //         $content = self::removeXss($content);
    //     }
    //     return $content;
    // }

    public static function getHttpFile($key)
    {
        $key = trim($key);

        return $_FILES[$key] ?? '';
    }

    public static function getPostPayload()
    {
        if (is_string(self::$postPayload)) {
            return self::$postPayload;
        }

        return file_get_contents('php://input');
    }

    public static function setPostPayload($payload)
    {
        self::$postPayload = $payload;
    }
}
