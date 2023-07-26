<?php

namespace CoreController;

use Http;
use Services\Game\Common;
use Services\Game\Login;

abstract class GameUserAbstract extends CommonAbstract
{
    public $uid = 0;
    public $user = [];
    public $needLogin = true;
    public $platform = false;
    public $ip = null;

    public function init()
    {
        parent::init();
        $this->platform = $this->getPlatform();
        $this->ip = Http::clientIp();
        if ($this->needLogin) {
            $check = $this->checkToken();
            if (!$check) {
                \Response::renderJson(GAME_USER_STATUS_NOT_LOGGED, '登陆过期');
            }
        }
        $this->disableView();
    }

    public function getPlatform()
    {
        $platform = Common::getPlatform();
        if (YAF_ENVIRON == 'dev' && empty($platform)) {
            $platform = $this->getQuery('platform') ?? $this->_getParam('platform') ?? null;
        }
        $this->platform = $platform;

        return $this->platform;
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
        $info = Login::checkUserToken();
        if (!$info) {
            return false;
        }

        $this->uid = $info['uid'] ?? 0;
        $this->user['mobile'] = $info['mobile']??null;
        $this->user['nickname'] = $info['nickname']??null;

        return true;
    }

}
