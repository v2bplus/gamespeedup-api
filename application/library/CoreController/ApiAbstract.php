<?php
namespace CoreController;

use Response;
use Session;
use Utility;

abstract class ApiAbstract extends CommonAbstract
{
    const SESSION_KEY = 'demo_user';
    public $uid = 0;
    public $user = [];
    public $page = 1;
    public $pageSize = 20;
    public $sorter = null;
    public $sortDir = null;
    public $sortInfo = [];
    public $maxPage = 50;
    public $maxPageSize = 500;
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
        $page = $this->getQuery('page') ?? $this->_getParam('page') ?? $this->page;
        $pageSize = $this->getQuery('pageSize') ?? $this->_getParam('pageSize') ?? $this->pageSize;
        $sorter = $this->getQuery('sorter') ?? $this->_getParam('sorter') ?? $this->sorter;
        $sortDir = $this->getQuery('sortDir') ?? $this->_getParam('sortDir') ?? $this->sortDir;
        $this->page = ((int) $page < $this->maxPage) ? ((int) $page) :$this->maxPage;
        $this->pageSize = ((int) $pageSize < $this->maxPageSize) ? (int) $pageSize :$this->maxPageSize;
        if ($sorter) {
            $this->sortInfo = ['sort' => $sorter, 'sortDir' => $this->sortDir($sortDir)];
        }
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

    public function sortDir($direction = null)
    {
        $dir = null;
        if ($direction) {
            $dir = strtoupper($direction);
        }
        if ($dir === 'ASC') {
            return 'ASC';
        }
        return 'DESC';
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
