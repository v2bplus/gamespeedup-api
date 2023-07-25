<?php

namespace Services\Game;

use Services\Login\Token;
use Utility;

class Login extends \Service
{
    public const ADMIN_TOKEN_FILED = 'Token';
    public const USER_TOKEN_FILED = 'Access-Token';

    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';

    public const USER_LOGIN_TOKEN_TIME = 86400 * 7;

    public static function adminLogin($emailOrName, $pwd)
    {
        try {
            $adminModel = new \GameAdminUserModel();
            $where = [
                'OR' => [
                    'user_name' => $emailOrName,
                    'email' => $emailOrName,
                ],
            ];
            $adminInfo = $adminModel->getInfoByWhere($where);
            if (!$adminInfo) {
                throw new \Exception('管理员不存在');
            }
            $role = self::ROLE_ADMIN;
            if (!password_verify($pwd, $adminInfo['password'])) {
                throw new \Exception('密码不正确');
            }
            $userId = $id = $adminInfo['id'];
            //Cache
            $info = [
                'id' => $id,
                'uid' => $userId,
                'user_name' => $adminInfo['user_name'],
                'email' => $adminInfo['email'],
                'role' => $role,
            ];
            $token = Token::login($info, self::USER_LOGIN_TOKEN_TIME,time());
            if (!$token) {
                throw new \Exception('登陆失败');
            }
            $returnData = [
                'id' => $id,
                'user_id' => $userId,
                'user_name' => $adminInfo['user_name'],
                'token' => $token,
                'email' => $adminInfo['email'],
                'role' => $role,
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

    //手机验证码登录
    public static function mobileLogin($post,$platform, $ip)
    {
        try {
            if (ENVIRON == 'dev' || ENVIRON == 'test') {
                //测试环境
                if ($post['smsCode'] != '888888') {
                    throw new \Exception("验证码不正确");
                }
            }else{
                $rs = Common::checkSms($post['mobile'], $post['smsCode']);
                if ($rs['status'] != 1) {
                    throw new \Exception($rs['msg']);
                }
            }
            $data = [
                'mobile' => $post['mobile'],
            ];
            $data['invite'] = $post['invite'] ?? 0;
            $rs = User::login($data, $platform, $ip);
            if ($rs['status'] != 1) {
                throw new \Exception($rs['msg']);
            }
            $userInfo = $rs['data'];
            $info = [
                'id' => $userInfo['id'],
                'user_id' => $userInfo['id'],
                'nickname' => $userInfo['nickname'],
                'mobile' => $userInfo['mobile'],
            ];
            $token = Token::login($info, self::USER_LOGIN_TOKEN_TIME);
            if (!$token) {
                throw new \Exception('登陆失败');
            }
            $returnData = [
                'token' => $token,
                'email' => $userInfo['email'],
                'mobile' => $userInfo['mobile'],
                'nickname' => $userInfo['nickname'],
                'id' => $userInfo['id'],
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

    public static function getLoginInfo()
    {
        $rs = Token::getLoginInfo(self::ADMIN_TOKEN_FILED);
        if (!$rs) {
            return false;
        }

        return $rs;
    }

    public static function logout($type = 'admin')
    {
        if ('user' == $type) {
            $tokenField = self::USER_TOKEN_FILED;
        } else {
            $tokenField = self::ADMIN_TOKEN_FILED;
        }
        Token::logout($tokenField);

        return true;
    }

    public static function checkAdminToken()
    {
        $return = [];
        $userInfo = Token::getLoginInfo(self::ADMIN_TOKEN_FILED);
        if (!$userInfo) {
            return $return;
        }
        // todo
        // 判断cachetime 时长
        // 少于一天 续费缓存时间
        $return['uid'] = $userInfo['user']['id'] ?? 0;
        $return['role'] = $userInfo['user']['role'] ?? '';

        return $return;
    }

    public static function checkUserToken()
    {
        $return = [];
        $userInfo = Token::getLoginInfo(self::USER_TOKEN_FILED);
        if (!$userInfo) {
            return $return;
        }
        $return['uid'] = $userInfo['user']['id'] ?? 0;
        $return['role'] = $userInfo['user']['role'] ?? '';

        return $return;
    }
}
