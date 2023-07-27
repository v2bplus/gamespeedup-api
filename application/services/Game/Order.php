<?php

namespace Services\Game;

class Order extends \Service
{
    public const STATUS_0 = 0; // 待支付
    public const STATUS_1 = 1; // 已完成
    public const STATUS_2 = 2; // 已取消

    //业务类型
    public static $typeArray = [
        1, 2,
    ];

    public static function save($post, $uid)
    {
        try {
            $userModel = new \GameUserModel();
            $userInfo = $userModel->getInfoById($uid, ['id', 'invite_user_id']);
            if (!$userInfo) {
                throw new \Exception('用户信息不存在');
            }
            $planId = $post['plan_id'];
            $rs = Plan::getInfo($planId);
            if (1 != $rs['status']) {
                throw new \Exception($rs['msg']);
            }
            $planInfo = $rs['data'];

            $inviteUserId = $userInfo['invite_user_id']??0;

            $orderNo = \Utility::orderNum($uid);
            $money = $planInfo['money'];

            $orderModel = new \GameOrderModel();
            // $orderModel->begin();
            $addArray = [];
            $addArray['user_id'] = $uid;
            $addArray['plan_id'] = $planId;
            $addArray['invite_user_id'] = $inviteUserId;
            $addArray['order_no'] = $orderNo;
            $addArray['total_amount'] = $money;
            $addArray['status'] = self::STATUS_0;

            $insertId = $orderModel->addData($addArray);
            if ($orderModel->getErrors()) {
                throw new \Exception('新增订单失败：'.json_encode($orderModel->getErrors()));
            }
            // $orderModel->commit();
            $return = [];
            $return['order_id'] = $insertId;
            $return['order_no'] = $orderNo;
            $return['pay_price'] = (int) $money;
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

    public static function check($tradeNo, $uid)
    {
        try {
            $orderModel = new \GameOrderModel();

            $where = ['order_no' => $tradeNo, 'user_id' => $uid];
            $check = $orderModel->checkExist($where);
            if (!$check) {
                $orderInfo = ['status' => 0];

                return [
                    'status' => 1,
                    'data' => $orderInfo,
                    'msg' => '',
                ];
            }
            $orderInfo = $orderModel->getInfoByWhere($where, ['status']);

            return [
                'status' => 1,
                'data' => $orderInfo,
                'msg' => '',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public static function autoClose($tradeNo, $status = self::STATUS_2)
    {
        try {
            $orderModel = new \GameOrderModel();
            $where = ['order_no' => $tradeNo];
            $check = $orderModel->checkExist($where);
            if (!$check) {
                return [
                    'status' => 1,
                    'data' => ['status' => 0],
                    'msg' => '订单不存在',
                ];
            }
            $orderInfo = $orderModel->getInfoByWhere($where, ['id', 'status', 'create_time']);

            $update = ['update_time' => time()];
            if (!in_array($orderInfo['status'], [self::STATUS_1])) {
                $update['status'] = $status;

                $orderModel->updateData($update, $orderInfo['id']);
                if ($orderModel->getErrors()) {
                    throw new \Exception('更新订单状态失败');
                }
            }

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

    public static function getAllList($page, $pageSize, $sort)
    {
        try {
            $where = [];
            $orderModel = new \GameOrderModel();
            $list = $orderModel->getList($page, $pageSize, null, $where, $sort);

            if (!$list) {
                return [
                    'status' => 1,
                    'data' => [],
                ];
            }

            $planList = Plan::getNameList();

            if ($list['items']) {
                foreach ($list['items'] as $index => $v) {
                    $list['items'][$index]['plan_name'] = null;
                    if (isset($planList[$v['plan_id']])) {
                        $list['items'][$index]['plan_name'] = $planList[$v['plan_id']]['name'];
                    }
                    $total_amount = $list['items'][$index]['total_amount'];
                    if ($total_amount) {
                        $list['items'][$index]['total_amount'] = bcdiv((string) $total_amount, '100', 2);
                    }
                }
            }

            return [
                'status' => 1,
                'data' => $list,
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
