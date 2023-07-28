<?php

// user模块
use Http;
use Services\Game\User;
use Services\Game\Plan;
use Services\Game\Order;
use Services\Game\Payment;

class UserController extends \CoreController\GameUserAbstract
{
    public $_white_actions = [
        'index', 'ping',
    ];

    public function init()
    {
        $action = $this->getRequest()->getActionName();
        if (in_array($action, $this->_white_actions)) {
            $this->needLogin = false;
        }
        parent::init();
    }

    public function indexAction()
    {
        Response::appJson(GAME_USER_STATUS_SUCCESS, $this->moduleName.': User首页', []);
    }

    public function pingAction()
    {
        Response::appJson(GAME_USER_STATUS_SUCCESS, ' Ping', []);
    }

    public function myinfoAction()
    {
        $detail = User::getUserInfo($this->uid);
        if (1 !== $detail['status']) {
            Response::renderJson(GAME_USER_STATUS_ERROR, $detail['msg']);
        }
        Response::renderJson(GAME_USER_STATUS_SUCCESS, '处理成功', $detail['data']);
    }

    public function plan_listAction()
    {
        $detail = Plan::getAll($this->uid);
        if (1 !== $detail['status']) {
            Response::renderJson(GAME_USER_STATUS_ERROR, $detail['msg']);
        }
        Response::renderJson(GAME_USER_STATUS_SUCCESS, '处理成功', $detail['data']);
    }

    public function order_addAction()
    {
        $planId = $this->getPost('plan_id', 0);
        $post = [
            'plan_id' => $planId,
            'type' => 1,
        ];
        $rules = [
            'plan_id' => [
                ['required', 'message' => '订阅id不能为空'],
                ['integer', 'message' => '套餐格式不正确'],
            ]
        ];
        $rs = Validator::customerValidate($post, $rules);
        if (!$rs->validate()) {
            Response::renderJson(GAME_USER_STATUS_ERROR, '验证错误', $rs->errors());
        }
        $detail = Order::createOrder($post, $this->uid);
        if (1 !== $detail['status']) {
            Response::renderJson(GAME_USER_STATUS_ERROR, $detail['msg']);
        }
        Response::renderJson(GAME_USER_STATUS_SUCCESS, '处理成功', $detail['data']);
    }

    //结账
    public function order_checkoutAction()
    {
        $tradeNo = $this->getPost('order_no', 0);
        $post = [
            'order_no' => $tradeNo,
            'type' => 1,
        ];
        $rules = [
            'order_no' => [
                ['required', 'message' => '订单号不能为空'],
                ['integer', 'message' => '订单号格式不正确'],
            ]
        ];
        $rs = Validator::customerValidate($post, $rules);
        if (!$rs->validate()) {
            Response::renderJson(GAME_USER_STATUS_ERROR, '验证错误', $rs->errors());
        }
        $detail = Order::checkout($post, $this->uid);
        if (1 !== $detail['status']) {
            Response::renderJson(GAME_USER_STATUS_ERROR, $detail['msg']);
        }
        Response::renderJson(GAME_USER_STATUS_SUCCESS, '处理成功', $detail['data']);
    }

    public function payment_methodAction()
    {
        $detail = Payment::getPaymentMethod();
        if (1 !== $detail['status']) {
            Response::renderJson(GAME_USER_STATUS_ERROR, $detail['msg']);
        }
        Response::renderJson(GAME_USER_STATUS_SUCCESS, '处理成功', $detail['data']);
    }
}

