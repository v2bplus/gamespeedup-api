<?php
namespace CoreController;

use Response;
use Session;

abstract class AppAbstract extends CommonAbstract
{
    const SESSION_KEY = 'demo_user';
    public $uid = 0;
    public $user = [];
    public $needLogin = true;
    public function init()
    {
        parent::init();
        $this->user = Session::get('user');
        if ($this->user) {
            $this->uid = intval($this->user['id']);
        }
        if ($this->needLogin) {
            $this->checkLogin();
            if (!$this->uid) {
                Response::appJson(API_CODE_FAILURE, null, '需要登陆', true);
            }
        }
        $this->page = $this->getPost('page', 1);
        $this->pageSize = $this->getPost('size', 20);
        $this->disableView();
    }

    public function getPost($name, $defaultValue = null, $filter = false)
    {
        $phpInput = $this->getRequest()->getRaw();
        if ($phpInput) {
            $post = (array) json_decode(trim($phpInput), true);
            $value = $post[$name] ?? $defaultValue;
        } else {
            $value = (null === $defaultValue) ? $this->getRequest()->getPost($name) : $this->getRequest()->getPost($name, $defaultValue);
        }
        if (!$filter) {
            return $value;
        }
        return Utility::removeXss($value);
    }

    public function checkLogin()
    {
        $userInfo = Session::get(self::SESSION_KEY);
        if (!$userInfo) {
            return false;
        }
        $this->uid = isset($userInfo['id']) ? intval($userInfo['id']) : 0;
        if (!$this->uid) {
            return false;
        }
        return (int) $this->uid;
    }
}
