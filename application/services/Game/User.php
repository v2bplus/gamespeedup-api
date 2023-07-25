<?php

namespace Services\Game;

use Services\Login\Token;
use Utility;

class User extends \Service
{
    // public static function regUser($data,$platform, $ip = '127.0.0.1')
    // {
    //     try {
    //         $userModel = new \GameUserModel();
    //         $mobile = strtolower($data['mobile']);
    //         $check = $userModel->checkMobile($mobile);
    //         if ($check) {
    //             throw new \Exception('手机号码: '.$mobile.' 已被使用');
    //         }
    //         $inviteUserId = 0;
    //         $invite = $data['invite'] ?? null;
    //         if ($invite) {
    //             $inviteUserId = $invite;
    //         }

    //         return [
    //             'status' => 1,
    //             'data' => $returnData,
    //             'msg' => '',
    //         ];
    //     } catch (\Exception $e) {
    //         return [
    //             'status' => 0,
    //             'msg' => $e->getMessage(),
    //         ];
    //     }
    // }

    public static function login($data,$platform, $ip = '127.0.0.1')
    {
         try {
            $userModel = new \GameUserModel();
            $where = [
                'mobile' => $data['mobile']
            ];
            $userInfo = $userModel->getInfoByWhere($where, '*');
            if ($userInfo) {
                return [
                    'status' => 1,
                    'data' => $userInfo,
                    'msg' => '',
                ];
            }
            $rs = self::createUser($data['mobile'],$data);
            if ($rs['status'] != 1) {
                throw new \Exception($rs['msg']);
            }
            return [
                'status' => 1,
                'data' => $rs['data'],
                'msg' => '',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public static function checkUser($user, $password)
    {
        try {
            $userModel = new \GameUserModel();
            $where = [
                'OR' => [
                    'mobile' => $user,
                    'email' => $user,
                ],
            ];
            $userInfo = $userModel->getInfoByWhere($where);
            if (!$userInfo) {
                throw new \Exception('用户不存在');
            }
            if (!password_verify($password, $userInfo['php_password'])) {
                throw new \Exception('密码不正确');
            }
            // $rs = self::updateUser($userInfo['id'],  $data);
            $return = [
                'id' => $userInfo['id'],
                'user_id' => $userInfo['id'],
                'nickname' => $userInfo['nickname'],
                'mobile' => $userInfo['mobile'],
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

    private static function createUser($mobile, $data)
    {
        $userModel = new \GameUserModel();
        $userInfo = [];
        $uuid = \Utility::getGuid();
        $password = $data['password'] ?? $uuid;
        $phpPass = password_hash($password, PASSWORD_DEFAULT);
        $uuidRs = self::getOneUUID();
        if ($uuidRs) {
            $uuid = $uuidRs['data'];
        }
        $nickName = \Utility::hideMobile($mobile);
        $userInfo['mobile'] = $mobile;
        $userInfo['nickname'] = $nickName;
        $userInfo['email'] = $data['email']??'';
        $userInfo['invite_user_id'] = $data['invite']??0;
        $userInfo['php_password'] = $phpPass;
        $userInfo['uuid'] = $uuid;
        $userInfo['last_login_time'] = time();
        $userId = $userModel->addData($userInfo);
        if ($userModel->getErrors()) {
            throw new \Exception('添加用户信息失败:'.json_encode($userModel->getErrors()));
        }
        $returnData = [
            'id' => $userId,
            'mobile' => $userInfo['mobile'],
            'nickname' => $userInfo['nickname'],
            'uuid' => $userInfo['uuid'],
        ];
        return [
            'status' => 1,
            'data' => $returnData,
            'msg' => '',
        ];
    }

    private static function updateUser($id, $data)
    {
        $userModel = new \GameUserModel();
        $userModel->updateData($id, $data);
        if ($userModel->getErrors()) {
            throw new \Exception('编辑用户失败');
        }
        $return = [];
        $return['id'] = $id;
        return [
            'status' => 1,
            'data' => $return,
            'msg' => '',
        ];
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
