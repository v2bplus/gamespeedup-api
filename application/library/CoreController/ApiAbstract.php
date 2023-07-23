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
}
