<?php

// user模块
use Http;
use Services\Game\User;
use Services\Game\Plan;

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
}
