<?php

class IndexController extends \CoreController\AppAbstract
{
    public $needLogin = false;

    public function indexAction()
    {
        Response::appJson(GAME_APP_CODE_SUCCESS, 'App首页', []);
    }
}
