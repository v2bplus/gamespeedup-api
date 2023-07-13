<?php

namespace Services\Api;

use EasyWeChat\Factory;

class Wechat extends \Service
{
    public static $tradeTypes = [
        'JSAPI', 'NATIVE', 'APP', 'MWEB',
    ];
    public static $JKeys = [
        'body', 'out_trade_no', 'total_fee', 'openid',
    ];
    public static $NKeys = [
        'body', 'out_trade_no', 'total_fee', 'product_id',
    ];
    protected static $_officialAccount;
    protected static $_miniProgram;
    protected static $_payment;

    // //公众号初始化
    // public static function officialAccount($appId, $appSecret)
    // {
    //     $config = [
    //         'app_id' => $appId,
    //         'secret' => $appSecret,
    //     ];
    //     $log = [];
    //     $log['file'] = LOGS_PATH.'/wechat.log';
    //     if (ENVIRON == 'dev' || ENVIRON == 'test') {
    //         $log['level'] = 'debug';
    //     } else {
    //         $log['level'] = 'info';
    //     }
    //     $config['log'] = $log;
    //     // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
    //     $config['response_type'] = 'array';
    //     self::$_officialAccount = Factory::officialAccount($config);
    //     return true;
    // }

    // //小程序初始化
    // public static function miniProgram($appId, $appSecret)
    // {
    //     $config = [
    //         'app_id' => $appId,
    //         'secret' => $appSecret,
    //     ];
    //     $log = [];
    //     $log['file'] = LOGS_PATH.'/wechat.log';
    //     if (ENVIRON == 'dev' || ENVIRON == 'test') {
    //         $log['level'] = 'debug';
    //     } else {
    //         $log['level'] = 'info';
    //     }
    //     $config['log'] = $log;
    //     $config['response_type'] = 'array';

    //     self::$_miniProgram = Factory::miniProgram($config);
    //     return true;
    // }

    // 支付初始化
    public static function payment($config)
    {
        // $config = [
        //     // 必要配置
        //     'app_id' => 'xxxx',
        //     'mch_id' => 'your-mch-id',
        //     'key' => 'key-for-signature',
        //     'cert_path' => 'path/to/your/cert.pem', // XXX: 绝对路径！！！！
        //     'key_path' => 'path/to/your/key',      // XXX: 绝对路径！！！！
        //     'notify_url' => '默认的订单回调地址',     // 你也可以在下单时单独设置来想覆盖它
        // ];
        $log = [];
        $log['file'] = LOGS_PATH.'/wechat.log';
        if (YAF_ENVIRON == 'dev' || YAF_ENVIRON == 'test') {
            $log['level'] = 'debug';
        } else {
            $log['level'] = 'info';
        }
        $config['log'] = $log;

        self::$_payment = Factory::payment($config);

        return true;
    }

    // // 根据 jsCode 获取用户 session 信息
    // public static function session($code)
    // {
    //     try {
    //         if (!self::$_miniProgram) {
    //             throw new \Exception('未找到小程序配置');
    //         }
    //         $array = self::$_miniProgram->auth->session($code);

    //         return [
    //             'status' => 1,
    //             'data' => $array,
    //             'msg' => 'success',
    //         ];
    //     } catch (\Exception $e) {
    //         return [
    //             'status' => 0,
    //             'msg' => $e->getMessage(),
    //         ];
    //     }
    // }

    // // 微信小程序消息解密
    // public static function decryptData($code, $iv, $encryptedData)
    // {
    //     try {
    //         if (!self::$_miniProgram) {
    //             throw new \Exception('未找到小程序配置');
    //         }
    //         $rs = self::session($code);
    //         if (1 != $rs['status']) {
    //             throw new \Exception($rs['msg']);
    //         }
    //         $session = $rs['data'];
    //         if (!isset($session['session_key'])) {
    //             throw new \Exception('获取sessionKey失败');
    //         }
    //         $return = self::$_miniProgram->encryptor->decryptData($session, $iv, $encryptedData);
    //         $return['openid'] = $session['openid'];

    //         return [
    //             'status' => 1,
    //             'data' => $return,
    //             'msg' => 'success',
    //         ];
    //     } catch (\Exception $e) {
    //         return [
    //             'status' => 0,
    //             'msg' => $e->getMessage(),
    //         ];
    //     }
    // }

    // 统一下单
    public static function unify($order = [], $tradeType = 'JSAPI')
    {
        try {
            if (!self::$_payment) {
                throw new \Exception('未找到支付配置');
            }
            if (!in_array($tradeType, self::$tradeTypes)) {
                throw new \Exception('交易类型不正确');
            }
            $notifyUrl = $order['notify_url'] ?? null;
            if ('JSAPI' == $tradeType) {
                if (array_diff(self::$JKeys, array_keys($order))) {
                    throw new \Exception('传递参数缺失');
                }
                $param = [
                    'body' => $order['body'],
                    'out_trade_no' => $order['out_trade_no'],
                    'total_fee' => $order['total_fee'],
                    'trade_type' => $tradeType,
                    'openid' => $order['openid'],
                ];
                if ($notifyUrl) {
                    $param['notify_url'] = $notifyUrl;
                }
                $prepay = self::$_payment->order->unify($param);
            } elseif ('NATIVE' == $tradeType) {
                if (array_diff(self::$NKeys, array_keys($order))) {
                    throw new \Exception('传递参数缺失');
                }
                $param = [
                    'body' => $order['body'],
                    'out_trade_no' => $order['out_trade_no'],
                    'total_fee' => $order['total_fee'],
                    'trade_type' => $tradeType,
                    'product_id' => $order['product_id'],
                ];
                if ($notifyUrl) {
                    $param['notify_url'] = $notifyUrl;
                }
                $prepay = self::$_payment->order->unify($param);
            } else {
                throw new \Exception('交易类型格式不正确');
            }
            if ('FAIL' === $prepay['return_code']) {
                throw new \Exception('微信支付api:'.$prepay['return_msg']);
            }
            if ('FAIL' === $prepay['result_code']) {
                throw new \Exception('微信支付api:'.$prepay['err_code_des']);
            }

            return [
                'status' => 1,
                'data' => $prepay['prepay_id'],
                'msg' => 'success',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
