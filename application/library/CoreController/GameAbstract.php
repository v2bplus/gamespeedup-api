<?php

namespace CoreController;

abstract class GameAbstract extends CommonAbstract
{
    public $uid = 0;
    public $user = [];
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
}
