<?php

// admin模块
use Http;
use Services\Game\Login;
use Services\Game\User;
use Services\Game\Common;

class LoginController extends \CoreController\GameAbstract
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

    public function codeAction()
    {
        $info = Common::code();
        if ($info['status'] !== 1) {
            Response::renderJson(GAME_USER_STATUS_ERROR, $info['msg']);
        }
        Response::renderJson(GAME_USER_STATUS_SUCCESS, '成功', $info['data']);
    }

    public function registerAction()
    {
        $email = $this->getPost('email', null);
        $password = $this->getPost('password', null);
        $inviteCode = $this->getPost('invite', null);
        $post = [
            'email' => $email,
            'password' => $password,
            'invite' => $inviteCode,
        ];
        $rules = [
            'email' => [
                ['required', 'message' => '邮件地址不能为空'],
            ],
            'password' => [
                ['required', 'message' => 'password不能为空'],
            ],
        ];
        $rs = Validator::customerValidate($post, $rules);
        if (!$rs->validate()) {
            Response::renderJson(GAME_USER_STATUS_ERROR, '验证错误', $rs->errors());
        }
        $detail = User::regUser($post, Http::clientIp());
        if (1 !== $detail['status']) {
            Response::renderJson(GAME_USER_STATUS_ERROR, $detail['msg']);
        }
        Response::renderJson(GAME_USER_STATUS_SUCCESS, '处理成功', $detail['data']);
    }

    // 用户登陆
    public function loginAction()
    {
        $username = $this->getPost('user', null, true);
        $username = strtolower($username);
        $password = $this->getPost('password', null);
        $post = [
            'user' => $username,
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
        $rs = Login::userLogin($username, $password);
        if (1 !== $rs['status']) {
            Response::renderJson(GAME_USER_STATUS_ERROR, $rs['msg']);
        }
        Response::renderJson(GAME_USER_STATUS_SUCCESS, '登陆成功', $rs['data']);
    }

    // // 重设密码提交 (发邮件)
    // public function resetAction()
    // {
    //     $email = $this->getPost('email', null, true);
    //     $post = [
    //         'email' => $email,
    //     ];
    //     $rules = [
    //         'email' => [
    //             ['required', 'message' => 'email不能为空'],
    //             ['email', 'message' => '不是正确的邮箱地址'],
    //         ],
    //     ];
    //     $rs = Validator::customerValidate($post, $rules);
    //     if (!$rs->validate()) {
    //         Response::renderJson(GAME_USER_STATUS_ERROR, '验证错误', $rs->errors());
    //     }
    //     $rs = Login::sendResetEmail($email);
    //     if (1 !== $rs['status']) {
    //         Response::renderJson(GAME_USER_STATUS_ERROR, $rs['msg']);
    //     }
    //     Response::renderJson(GAME_USER_STATUS_SUCCESS, $rs['msg'], $rs['data']);
    // }

    // // 重置密码确认
    // public function reset_confirmAction()
    // {
    //     $token = $this->getPost('token', null, true);
    //     $newPass = $this->getPost('new_password', null, true);
    //     $post = [
    //         'token' => $token,
    //         'new_password' => $newPass,
    //     ];
    //     $rules = [
    //         'token' => [
    //             ['required', 'message' => 'Token不能为空'],
    //         ],
    //         'new_password' => [
    //             ['required', 'message' => '密码不能为空'],
    //         ],
    //     ];
    //     $rs = Validator::customerValidate($post, $rules);
    //     if (!$rs->validate()) {
    //         Response::renderJson(GAME_USER_STATUS_ERROR, '验证错误', $rs->errors());
    //     }
    //     $result = Login::resetPassword($token, $newPass);
    //     if (1 !== $result['status']) {
    //         Response::renderJson(GAME_USER_STATUS_ERROR, $result['msg']);
    //     }

    //     Response::renderJson(GAME_USER_STATUS_SUCCESS, $result['msg'], $result['data']);
    // }
}
