<?php

class IndexController extends \CoreController\ApiAbstract
{
    public $needLogin = false;

    public function indexAction()
    {
        Response::appJson(API_CODE_SUCCESS, 'Api首页', []);
    }
}
