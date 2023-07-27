<?php

namespace Services\Game;

class Payment extends \Service
{
    public static $types = [
        'alipay', 'wechat', 'all',
    ];

    public static $model = [
        'VPAY',
    ];

    public static function getPaymentMethod()
    {
        try {
            $data[] = [
                'id'=>1,
                'type'=>'alipay',
                'name'=>'支付宝',
                'ico'=>'https://cdn.ourplay.net/pc/src/img/wxm.png'
            ];
            $data[] = [
                'id' =>2,
                'type'=> 'wechat',
                'name'=> '微信',
                'ico'=> 'https://cdn.ourplay.net/pc/src/img/wxm.png'
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
