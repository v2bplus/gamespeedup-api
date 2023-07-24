<?php
namespace Services\Game;

use Exception;
use Services\Api\CaptchaApi;

class Common extends \Service
{
    const SCENES = [
        'captcha',
        'payment',
    ];
    const TOKEN_CODE_CACHE_TIME = 600;

    public static function captcha()
    {
        try {
            $codeLength = 4;
            $captcha = CaptchaApi::create($codeLength);
            if ($captcha['status'] != 1) {
                throw new \Exception($captcha['msg']);
            }
            return [
                'status' => 1,
                'data' => $captcha['data'],
                'msg' => ''
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }

    //页面code
    public static function code()
    {
        try {
            $key = microtime(true);
            $captcha = CaptchaApi::token($key, self::TOKEN_CODE_CACHE_TIME);
            if ($captcha['status'] != 1) {
                throw new Exception($captcha['msg']);
            }
            $key = $captcha['data']['key'];
            $data = [
                'code' => $key
            ];
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

    public static function sendCaptcha($storeId, $mobile, $code = null, $key = null)
    {
        try {
            if ($code && $key) {
                $rs = CaptchaApi::check($code, $key);
                if ($rs['status'] != 1) {
                    throw new \Exception($rs['msg']);
                }
            }
            $rs = CaptchaApi::record($mobile);
            if ($rs['status'] != 1) {
                throw new \Exception($rs['msg']);
            }
            $smsCaptcha = CaptchaApi::createSMS($mobile);
            // 发送短信
            $params = ['code' => $smsCaptcha['code']];
            $rs = self::sendSms('captcha', $mobile, $params, $storeId);
            if ($rs['status'] != 1) {
                throw new \Exception($rs['msg']);
            }
            return [
                'status' => 1,
                'data' => [],
                'msg' => 'success'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }

    public static function checkSms($phone, $code)
    {
        try {
            $rs = CaptchaApi::checkSMS($phone, $code);
            if ($rs['status'] != 1) {
                throw new Exception($rs['msg']);
            }
            return [
                'status' => 1,
                'data' => $rs['data'],
                'msg' => 'success'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }

    private static function sendSms($sceneValue, $acceptPhone, $templateParams, $storeId)
    {
        try {

            return [
                'status' => 1,
                'data' =>[],
                'msg' => 'success'
            ];
            return true;
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }
}