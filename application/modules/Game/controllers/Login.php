<?php

use Http;
use Services\Game\Login;
use Services\Game\User;
use Services\Game\Common;

class LoginController extends \CoreController\GameUserAbstract
{
    public function init()
    {
        $this->needLogin = false;
        parent::init();
    }

    public function indexAction()
    {
        Response::appJson(GAME_USER_STATUS_SUCCESS, $this->moduleName.': Login首页', []);
    }

    public function pingAction()
    {
        //Todo
        Response::appJson(GAME_USER_STATUS_SUCCESS, ' Ping', []);
    }

    //发送短信的图形验证码
    public function captchaAction()
    {
        $rs = Common::captcha();
        if ($rs['status'] !== 1) {
            Response::renderJson(GAME_USER_STATUS_ERROR, $rs['msg']);
        }
        Response::renderJson(GAME_USER_STATUS_SUCCESS, 'success', $rs['data']);
    }

    // public function codeAction()
    // {
    //     $info = Common::code();
    //     if ($info['status'] !== 1) {
    //         Response::renderJson(GAME_USER_STATUS_ERROR, $info['msg']);
    //     }
    //     Response::renderJson(GAME_USER_STATUS_SUCCESS, '成功', $info['data']);
    // }

    public function sendsmsAction()
    {
        $mobile = $this->getPost('mobile', null);
        $captchaKey = $this->getPost('captchaKey', null);
        $captchaCode = $this->getPost('captchaCode', null);
        $post = [
            'mobile' => $mobile,
            'captchaCode' => $captchaCode,
            'captchaKey' => $captchaKey,
        ];
        $rules = [
            'mobile' => [
                ['required', 'message' => '手机号不能为空'],
            ],
            'captchaKey' => [
                ['required', 'message' => '验证码key不能为空'],
            ],
            'captchaCode' => [
                ['required', 'message' => '验证码code不能为空'],
            ],
        ];
        $rs = Validator::customerValidate($post, $rules);
        if (!$rs->validate()) {
            Response::renderJson(GAME_USER_STATUS_ERROR, '验证错误', $rs->errors());
        }
        $rs = Common::sendCaptcha($mobile, $captchaKey,$captchaCode);
        if ($rs['status'] !== 1) {
            Response::renderJson(GAME_USER_STATUS_ERROR, $rs['msg']);
        }
        Response::renderJson(GAME_USER_STATUS_SUCCESS, 'success', $rs['data']);
    }

    // public function registerAction()
    // {
    //     $email = $this->getPost('email', null);
    //     $password = $this->getPost('password', null);
    //     $inviteCode = $this->getPost('invite', null);
    //     $post = [
    //         'email' => $email,
    //         'password' => $password,
    //         'invite' => $inviteCode,
    //     ];
    //     $rules = [
    //         'email' => [
    //             ['required', 'message' => '邮件地址不能为空'],
    //         ],
    //         'password' => [
    //             ['required', 'message' => 'password不能为空'],
    //         ],
    //     ];
    //     $rs = Validator::customerValidate($post, $rules);
    //     if (!$rs->validate()) {
    //         Response::renderJson(GAME_USER_STATUS_ERROR, '验证错误', $rs->errors());
    //     }
    //     $detail = User::regUser($post, Http::clientIp());
    //     if (1 !== $detail['status']) {
    //         Response::renderJson(GAME_USER_STATUS_ERROR, $detail['msg']);
    //     }
    //     Response::renderJson(GAME_USER_STATUS_SUCCESS, '处理成功', $detail['data']);
    // }

    // 用户验证码登陆
    public function loginAction()
    {
        $mobile = $this->getPost('mobile', null, true);
        $smsCode = $this->getPost('smsCode', null);
        $inviteCode = $this->getPost('invite', null);

        $post = [
            'mobile' => $mobile,
            'smsCode' => $smsCode,
            'invite' => $inviteCode,
        ];
        $rules = [
            'mobile' => [
                ['required', 'message' => '手机号不能为空'],
            ],
            'smsCode' => [
                ['required', 'message' => '验证码code不能为空'],
            ],
        ];
        $rs = Validator::customerValidate($post, $rules);
        if (!$rs->validate()) {
            Response::renderJson(GAME_USER_STATUS_ERROR, '验证错误', $rs->errors());
        }
        $rs = Login::smsLogin($post, $this->platform,$this->ip);
        if (1 !== $rs['status']) {
            Response::renderJson(GAME_USER_STATUS_ERROR, $rs['msg']);
        }
        Response::renderJson(GAME_USER_STATUS_SUCCESS, '登陆成功', $rs['data']);
    }

    //密码登录
    public function passAction()
    {
        $user = $this->getPost('user', null, true);
        $password = $this->getPost('password', null);

        $post = [
            'user' => $user,
            'password' => $password,
        ];
        $rules = [
            'user' => [
                ['required', 'message' => '用户名不能为空'],
            ],
            'password' => [
                ['required', 'message' => '密码不能为空'],
                ['lengthMin', 6, 'message' => '密码格式不正确'],
            ],
        ];
        $rs = Validator::customerValidate($post, $rules);
        if (!$rs->validate()) {
            Response::renderJson(GAME_USER_STATUS_ERROR, '验证错误', $rs->errors());
        }
        $rs = Login::passLogin($user,$password, $this->platform,$this->ip);
        if (1 !== $rs['status']) {
            Response::renderJson(GAME_USER_STATUS_ERROR, $rs['msg']);
        }
        Response::renderJson(GAME_USER_STATUS_SUCCESS, '登陆成功', $rs['data']);
    }
}
