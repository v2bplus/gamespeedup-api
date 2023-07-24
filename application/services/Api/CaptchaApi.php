<?php
namespace Services\Api;

use Cache;
use Exception;
use Gregwar\Captcha\CaptchaBuilder;
use Service;

class CaptchaApi extends Service
{
    //验证码过期时间（s）
    protected static $expire = 300;
    //验证码可重复验证的次数

    //发送限制间隔时间，默认24小时
    protected static $safeTime = 86400;

    protected static $checkMaxTimes = 4;
    protected static $config = [
        'default' => [
            'length' => 4,
            'width' => 100,
            'height' => 30,
            'quality' => 90,
            'expire' => 120
        ],
    ];

    protected static $useMath = false;
    protected static $characters = '12346789';
    protected static $length = 4;
    protected static $quality = 90;

    protected static $width = 100;
    protected static $height = 30;

    public static function configure($con)
    {
        if (array_key_exists($con, self::$config)) {
            $configure = self::$config[$con];
            foreach ($configure as $key => $value) {
                self::$$key = $value;
            }
        }
    }

    protected static function getCacheKey($key, $type = 'code')
    {
        if ($type == 'code') {
            return 'captchaCode_'.$key;
        } elseif ($type == 'sms') {
            return 'captchaSms_'.$key;
        } elseif ($type == 'token') {
            return 'captchaToken_'.$key;
        }
        return false;
    }

    public static function token($key = null, $expire = 3600)
    {
        try {
            $key = (string) $key;
            $cacheKey = self::getCacheKey($key, 'token');
            Cache::set($cacheKey, 1, $expire);
            $data = [
                'key' => $key
            ];
            return [
                'status' => 1,
                'data' => $data,
                'msg' => 'success'
            ];
        } catch (Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }

    public static function checkToken($key = null)
    {
        try {
            if (!$key) {
                throw new Exception('页面code不正确');
            }
            $cacheKey = self::getCacheKey($key, 'token');
            $check = Cache::check($cacheKey);
            return [
                'status' => 1,
                'data' => $check,
                'msg' => 'success'
            ];
        } catch (Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }

    public static function generateCode()
    {
        $characters = is_string(self::$characters) ? str_split(self::$characters) : self::$characters;
        $bag = [];
        if (self::$useMath) {
            $x = random_int(10, 30);
            $y = random_int(1, 9);
            $bag = "$x + $y = ";
            $key = $x + $y;
            $key .= '';
        } else {
            for ($i = 0; $i < self::$length; ++$i) {
                $bag[] = $characters[rand(0, count($characters) - 1)];
            }
            $key = implode('', $bag);
            $md5 = md5($key);
        }
        $hash = password_hash(mb_strtolower($key, 'UTF-8'), PASSWORD_DEFAULT);
        $hash = sha1($hash);
        $cacheKey = self::getCacheKey($hash);
        Cache::set($cacheKey, [
            'code' => $key,
            'times' => self::$checkMaxTimes,
        ], self::$expire);
        $return = [
            'value' => $bag,
            'key' => $hash,
            'md5' => $md5,
        ];
        return $return;
    }

    public static function create($config = 'default')
    {
        try {
            self::configure($config);
            $info = self::generateCode();
            $base64 = self::getCaptchaImg($info['value']);
            if (!$base64) {
                throw new Exception('生成验证码图片失败');
            }
            $return = [
                'base64' => 'data:image/png;base64,'.chunk_split(base64_encode($base64)),
                'key' => $info['key'],
                'md5' => $info['md5'],
            ];
            if (ENVIRON == 'dev' || ENVIRON == 'test') {
                $value = implode('', $info['value']);
                $return['value'] = $value;
            }
            return [
                'status' => 1,
                'data' => $return,
                'msg' => 'success'
            ];
        } catch (Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }

    public static function createSMS(string $phone)
    {
        $code = (string) mt_rand(100000, 999999);
        $cacheKey = self::getCacheKey($phone, 'sms');
        $rs = Cache::set($cacheKey, [
            'code' => $code,
            'times' => self::$checkMaxTimes,
        ], self::$expire);
        return ['phone' => $phone, 'code' => $code];
    }

    public static function record($mobile)
    {
        $cacheKey = "sendCaptchaSMS.$mobile";
        $check = Cache::check($cacheKey);
        if (!$check) {
            $rs = Cache::set($cacheKey, [
                'times' => self::$checkMaxTimes - 1,
            ], self::$safeTime);
            return [
                'status' => 1,
                'data' => [],
                'msg' => 'success'
            ];
        }
        $record = Cache::get($cacheKey);
        $times = $record['times'] ?? 0;
        // 判断发送次数是否合法
        if ($times <= 0) {
            if (ENVIRON != 'dev') {
                throw new Exception('很抱歉，已超出今日最大发送次数限制');
            }
        }
        // 发送次数递减
        Cache::update($cacheKey, ['times' => $record['times'] - 1]);
        return [
            'status' => 1,
            'data' => [],
            'msg' => 'success'
        ];
    }

    public static function check($code, $key)
    {
        try {
            $cacheKey = self::getCacheKey($key);
            $check = Cache::check($cacheKey);
            if (!$check) {
                throw new Exception('验证码不存在，请重新获取');
            }
            $data = Cache::get($cacheKey);
            if ($data && $data['times'] <= 0) {
                throw new Exception('验证码已超出错误次数，请重新获取');
            }
            if ($data['code'] != $code) {
                Cache::update($cacheKey, ['times' => $data['times'] - 1], self::$expire);
                throw new Exception('验证码不正确');
            }
            if (ENVIRON != 'dev') {
                Cache::del($cacheKey);
            }
            return [
                'status' => 1,
                'data' => true,
                'msg' => 'success'
            ];
        } catch (Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }

    public static function checkSMS($phone, $code)
    {
        $cacheKey = self::getCacheKey($phone, 'sms');
        $check = Cache::check($cacheKey);
        if (!$check) {
            throw new Exception('短信验证码不存在，请重新获取');
        }
        $data = Cache::get($cacheKey);
        if ($data && $data['times'] <= 0) {
            throw new Exception('短信验证码已超出错误次数，请重新获取');
            if (ENVIRON != 'dev') {
                Cache::del($cacheKey);
            }
        }
        if ($data['code'] != $code) {
            Cache::update($cacheKey, ['times' => $data['times'] - 1]);
            throw new Exception('短信验证码错误');
        }
        if (ENVIRON != 'dev') {
            Cache::del($cacheKey);
        }
        return [
            'status' => 1,
            'data' => true,
            'msg' => 'success'
        ];
    }

    public static function getCaptchaImg($text)
    {
        if (is_array($text)) {
            $text = implode('', $text);
        }
        $builder = new CaptchaBuilder($text);
        $builder->setDistortion(false);
        $builder->setMaxBehindLines(1);
        $builder->build(self::$width, self::$height);
        // header('Content-type: image/jpeg');
        // echo $builder->output();
        return $builder->inline();
    }
}
