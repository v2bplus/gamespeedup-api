<?php

namespace Services\Game;

class Payment extends \Service
{
    public static $types = [
        'alipay', 'wechat', 'all',
    ];

    public static $method = [
        'vpay'
    ];
    protected static $payment;

    public static function pay($order=[])
    {
        try {
            $type = 'vpay';
            $rs = self::init($type);
            if (1 != $rs['status']) {
                throw new \Exception($rs['msg']);
            }
            $gate = self::$payment;
            $url = $gate::unify();
            $return = [
                'type' => 1,
                'data' => $url
            ];
            return [
                'status' => 1,
                'data' => $return,
                'msg' => '',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public static function init($className, $arguments=[])
    {
        try {
            $class = '\\Services\\Payment\\'.ucfirst($className);
            if (!class_exists($class)) {
                throw new \Exception('未找到支付网关');
            }
            self::$payment = $class;
            return [
                'status' => 1,
                'data' => [],
                'msg' => '',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public static function getPaymentMethod()
    {
        try {
            $data[] = [
                'id' => 1,
                'type' => 'alipay',
                'name' => '支付宝',
                'ico' => 'https://cdn.ourplay.net/pc/src/img/wxm.png',
            ];
            $data[] = [
                'id' => 2,
                'type' => 'wechat',
                'name' => '微信',
                'ico' => 'https://cdn.ourplay.net/pc/src/img/wxm.png',
            ];

            return [
                'status' => 1,
                'data' => $data,
                'msg' => '',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
