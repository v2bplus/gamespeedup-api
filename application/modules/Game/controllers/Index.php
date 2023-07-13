<?php

class IndexController extends \CoreController\GameAbstract
{
    public $needLogin = false;

    public function indexAction()
    {
        Response::appJson(GAME_STATUS_SUCCESS, 'Game首页', []);
    }
}
