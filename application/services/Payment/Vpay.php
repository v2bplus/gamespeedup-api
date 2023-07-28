<?php

namespace Services\Payment;

class Vpay
{
    public static $payType = [
        'alipay', 'wechat',
    ];
    public static $keys = [
        'name', 'mch_id', 'amount', 'nofity_url', 'return_url',
    ];

    public static function unify($order = [], $payType = null)
    {
        try {
            // if (!in_array($payType, self::$payType)) {
            //     throw new \Exception('支付方式不正确');
            // }
            // if (array_diff(self::$keys, array_keys($order))) {
            //     throw new \Exception('传递参数缺失');
            // }
            // $param = [
            //     'method' => 'epay://pay/create',
            //     'mch_id' => $order['mch_id'],
            //     'out_trade_no' => $order['out_trade_no'],
            //     'amount' => $order['amount'],
            //     'openid' => $order['openid'],
            //     'nofity_url' => $order['nofity_url'],
            //     'return_url' => $order['return_url'],
            // ];
            $prepay_id = 'www.baidu.com';
            return [
                'status' => 1,
                'data' => $prepay_id,
                'msg' => 'success',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public static function notify($params)
    {
        // code...
    }
}
