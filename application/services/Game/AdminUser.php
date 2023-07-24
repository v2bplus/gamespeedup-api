<?php

namespace Services\Game;

use Services\Login\Token;

class AdminUser extends \Service
{
    // 增加管理员
    public static function addUser($post, $uid)
    {
        try {
            $adminModel = new \GameAdminUserModel();
            $username = $post['username'];
            $check = $adminModel->checkName($username);
            if ($check) {
                throw new \Exception('用户名: '.$username.' 已被使用');
            }
            $password = $post['password'];
            $phpPass = password_hash($password, PASSWORD_DEFAULT);
            $userInfo = [];
            $userInfo['user_name'] = $username;
            $userInfo['mobile'] = $post['mobile'] ?? null;
            $userInfo['password'] = $phpPass;

            $adminUid = $adminModel->addData($userInfo);
            if ($adminModel->getErrors()) {
                throw new \Exception('添加管理员失败');
            }
            $return = [
                'id' => $adminUid,
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

    public static function delUser($data = [])
    {
        try {
            $userName = $data['username'] ?? '';
            if ('admin' == $userName) {
                throw new \Exception("不能删除用户名为'admin'的用户!");
            }
            $adminModel = new \GameAdminUserModel();
            $where = [
                'id' => $data['id'],
                'user_name' => $userName
            ];
            $hasInfo = $adminModel->checkExist($where);
            if (!$hasInfo) {
                throw new \Exception('信息不存在,请刷新后重试');
            }
            $adminModel->del($where);

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

    public static function editInfo($post)
    {
        try {
            $adminModel = new \GameAdminUserModel();
            $info = $adminModel->getInfoById($post['id'], ['id','user_name','email']);
            if (!$info) {
                throw new \Exception('信息不存在');
            }
            if (isset($post['email'])) {
                $check = $adminModel->checkEmail($post['email'], $post['id']);
                if ($check) {
                    throw new \Exception('邮件地址: '.$post['email'].' 已使用');
                }
            }
            $adminModel->updateData($post, $post['id']);
            if ($adminModel->getErrors()) {
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

    public static function getAll($page, $pageSize, $order, $user)
    {
        try {
            $where = [];
            if ($user) {
                if (Login::ROLE_ADMIN != $user['role']) {
                    $where = ['id' => $user['uid']];
                }
            }

            $adminModel = new \GameAdminUserModel();
            $column = [];
            $list = $adminModel->getList($page, $pageSize, $column, $where, $order);

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

    public static function getUserInfo($uid = null)
    {
        try {
            $userModel = new \GameAdminUserModel();
            $info = $userModel->getInfoById($uid, ['id', 'user_name', 'real_name', 'mobile', 'last_login_time', 'last_login_ip']);
            if (!$info) {
                throw new \Exception('信息不存在');
            }
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

}
