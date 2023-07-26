<?php

namespace Services\Game;

use Services\Api\PushApi;
use Carbon\Carbon;

class UserVip extends \Service
{
    const STATUS_EXPIRE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_NOVIP = -1;

    public static $status = [
       self::STATUS_EXPIRE,self::STATUS_ACTIVE,self::STATUS_NOVIP,
    ];

    public static function vipConfig()
    {
        return [
        ];
    }

    //是否是vip
    public static function isAvailable($uid)
    {
        try {
            $vipModel = new \GameUserVipModel();
            $where = ['user_id' => $uid];
            $rs = $vipModel->checkExist($where);
            if(!$rs){
                $addArray = [];
                $addArray['user_id'] = $uid;
                $addArray['status'] = self::STATUS_NOVIP;
                $vipModel->addData($addArray);
                if ($vipModel->getErrors()) {
                    throw new \Exception('初始化VIP信息失败');
                }
                $return = [
                   'uid' => $uid,
                   'status' => false,
                ];
                return [
                    'status' => 1,
                    'data' => $return,
                    'msg' => '',
                ];
            }

            $vipInfo = $vipModel->getInfoByWhere($where);
            $status = false;
            if ($vipInfo['status'] == self::STATUS_ACTIVE) {
                $status = true;
            }
            $return['uid'] = $uid;
            $return['status'] = $status;
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

    public static function getInfo($uid)
    {
        try {
            $vipModel = new \GameUserVipModel();
            $where = ['user_id' => $uid];
            $rs = $vipModel->checkExist($where);
            if (!$rs) {
                $addArray = [];
                $addArray['user_id'] = $uid;
                $vipModel->addData($addArray);
                if ($vipModel->getErrors()) {
                    throw new \Exception('添加账户信息失败');
                }
            }
            $vipInfo = $vipModel->getInfoByWhere($where);

            $return = [
               'id'=>$vipInfo['id'],
               'status' => $vipInfo['status'],
               'user_id' => $uid,
               'time_num' => $vipInfo['time_num'],
               'end_time' => $vipInfo['end_time'],
               'day' => 0,
            ];

            if ($vipInfo['status'] == self::STATUS_EXPIRE) {
                $return['msg'] = '过期';
            }elseif ($vipInfo['status'] == self::STATUS_ACTIVE) {
                $return['day'] = round(($vipInfo['end_time'] - time()) / 3600 / 24);
                $return['msg'] = '正常';
            }
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

    public static function openVip($uid, $days)
    {
        try {
            $rs = self::getInfo($uid);
            if ($rs['status'] != 1) {
                throw new \Exception($rs['msg']);
            }
            $info = $rs['data'];
            $update = [
                'status' => self::STATUS_ACTIVE,
            ];
            if ($info['status'] != self::STATUS_ACTIVE) {
                $update['start_time'] = time();
                $update['end_time'] = Carbon::createFromTimestamp(time())->addDays($days)->endOfDay()->getTimestamp();
            }else{
                $update['end_time'] = Carbon::createFromTimestamp($info['end_time'])->addDays($days)->endOfDay()->getTimestamp();
            }
            $vipModel = new \GameUserVipModel();
            $vipModel->updateData($update, $info['id']);
            return [
                'status' => 1,
                'data' => $update,
                'msg' => '',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public static function updateStatus($post, $ext)
    {
        try {
            $vipModel = new \GameUserVipModel();
            $info = $vipModel->getInfoById($post['id'], ['id', 'user_id','status', 'start_time', 'end_time']);
            if (!$info) {
                throw new \Exception('信息不存在');
            }
            $update = [];
            $update['status'] = $post['status'];
            if (isset($post['start_time']) && !empty($post['start_time'])) {
                $update['start_time'] = $post['start_time'];
            }
            if (isset($post['end_time']) && !empty($post['end_time'])) {
                $update['end_time'] = $post['end_time'];
            }
            $vipModel->updateData($update, $post['id']);
            if ($vipModel->getErrors()) {
                throw new \Exception('更新信息失败');
            }

            return [
                'status' => 1,
                'data' => [],
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public static function getAll($page, $pageSize, $order)
    {
        try {
            $where = [];
            $userModel = new \GameUserVipModel();
            $column = [];
            $list = $userModel->getList($page, $pageSize, $column, $where, $order);

            if (!$list) {
                return [
                    'status' => 1,
                    'data' => [],
                ];
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
