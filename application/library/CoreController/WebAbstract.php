<?php
namespace CoreController;

use \Yaf_Registry;

abstract class WebAbstract extends CommonAbstract
{
    public $page = 0;
    public $pageSize = 0;
    public function init()
    {
        parent::init();
        $config = Yaf_Registry::get('_config')->toArray();
        $module = strtolower($this->module);
        if (!isset($config[$module])) {
            die('没有配置'.$module.'模块site配置');
        }
        $this->assign('site', $config[$module]['site']);
        $this->assign('curController', $this->controller);
        $this->assign('curAction', $this->action);
        $this->assign('curModule', $module);
        $this->page = $this->getPost('page', 1);
        $this->pageSize = $this->getPost('size', 20);
        $this->enableView();
    }
}
