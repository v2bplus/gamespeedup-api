<?php

namespace Services\Game;

use Services\Login\Token;

class User extends \Service
{
    public static function regUser($data,$platform, $ip = '127.0.0.1')
    {
        try {
            $userModel = new \GameUserModel();
            $mobile = strtolower($data['mobile']);
            $check = $userModel->checkMobile($mobile);
            if ($check) {
                throw new \Exception('手机号码: '.$mobile.' 已被使用');
            }
            $inviteUserId = 0;
            $invite = $data['invite'] ?? null;
            if ($invite) {
                $inviteUserId = $invite;
            }
            $uuid = \Utility::getGuid();
            $password = $data['password'] ?? $uuid;
            $phpPass = password_hash($password, PASSWORD_DEFAULT);
            $userInfo = [];
            $uuidRs = self::getOneUUID();
            if ($uuidRs) {
                $uuid = $uuidRs['data'];
            }
            $userInfo['mobile'] = $mobile ?? '';
            $userInfo['email'] = $data['email']??'';
            $userInfo['invite_user_id'] = $inviteUserId;
            $userInfo['php_password'] = $phpPass;
            $userInfo['uuid'] = $uuid;
            $userId = $userModel->addData($userInfo);
            if ($userModel->getErrors()) {
                throw new \Exception('添加用户信息失败:'.json_encode($userModel->getErrors()));
            }
            $returnData = [
                'id' => $userId,
                'email' => $userInfo['email'],
                'mobile' => $userInfo['mobile'],
                'role' => '',
            ];
            return [
                'status' => 1,
                'data' => $returnData,
                'msg' => '',
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
            $userModel = new \GameUserModel();
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

    public static function getOneUUID()
    {
        try {
            $uModel = new \GameUUIDModel();
            $info = $uModel->getOne();
            if (!$info) {
                $uuid = \Utility::getGuid();
            } else {
                $uuid = $info['uuid'];
                $uModel->setOne($info['id']);
            }

            return [
                'status' => 1,
                'data' => $uuid,
                'msg' => '',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public static function getUserInfo($uid = null)
    {
        try {
            $userModel = new \GameUserModel();
            $info = $userModel->getInfoById($uid, ['id', 'nickname', 'mobile', 'email', 'uuid',  'real_status', 'last_login_time']);
            if (!$info) {
                throw new \Exception('信息不存在');
            }
            unset($info['php_password']);
            return [
                'status' => 1,
                'data' => $info,
                'msg' => '',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public static function editInfo($post, $uid)
    {
        try {


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
}
