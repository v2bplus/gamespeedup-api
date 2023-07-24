<?php

namespace CoreController;

use Services\Game\Login;

abstract class GameAdminAbstract extends CommonAbstract
{
    public $adminId = 0;
    public $isAdmin = false;
    public $user = [];
    public $page = 1;
    public $pageSize = 20;
    public $sorter;
    public $sortDir;
    public $sortInfo = [];
    public $maxPage = 50;
    public $maxPageSize = 500;
    public $needLogin = true;

    public function init()
    {
        parent::init();
        $page = $this->getQuery('page') ?? $this->_getParam('page') ?? $this->page;
        $pageSize = $this->getQuery('pageSize') ?? $this->_getParam('pageSize') ?? $this->pageSize;
        $sorter = $this->getQuery('sorter') ?? $this->_getParam('sorter') ?? $this->sorter;
        $sortDir = $this->getQuery('sortDir') ?? $this->_getParam('sortDir') ?? $this->sortDir;
        $this->page = ((int) $page < $this->maxPage) ? ((int) $page) : $this->maxPage;
        $this->pageSize = ((int) $pageSize < $this->maxPageSize) ? (int) $pageSize : $this->maxPageSize;
        if ($sorter) {
            $this->sortInfo = ['sort' => $sorter, 'sortDir' => $this->sortDir($sortDir)];
        }
        if ($this->needLogin) {
            $check = $this->checkToken();
            if (!$check) {
                \Response::renderJson(GAME_ADMIN_STATUS_NOT_LOGGED, '登陆过期');
            }
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

        return \Utility::removeXss($value);
    }

    public function sortDir($direction = null)
    {
        $dir = null;
        if ($direction) {
            $dir = strtoupper($direction);
        }
        if ('ASC' === $dir) {
            return 'ASC';
        }

        return 'DESC';
    }

    public function checkToken()
    {
        $info = Login::checkAdminToken();
        if (!$info) {
            return false;
        }
        $this->adminId = $info['uid'] ?? 0;
        $this->isAdmin = $info['isAdmin'] ?? false;

        return true;
    }
}
