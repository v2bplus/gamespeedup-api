<?php

namespace CoreController;

abstract class CommonAbstract extends \Yaf_Controller_Abstract
{
    public $moduleName = '';
    public $controller = '';
    public $action = '';
    public $getArray = [];

    public function init()
    {
        $this->moduleName = $this->getModuleName();
        $this->controller = strtolower($this->getRequest()->getControllerName());
        $this->action = strtolower($this->getRequest()->getActionName());
    }

    public function getParam($name, $defaultValue = null, $filter = false)
    {
        $value = (null === $defaultValue) ? $this->getRequest()->getParam($name) : $this->getRequest()->getParam($name, $defaultValue);
        if (!$filter) {
            return $value;
        }

        return \Utility::removeXss($value);
    }

    public function _getParam($key = null, $defaultValue = null)
    {
        if (empty($this->getArray)) {
            $array = [];
            foreach ($_GET as $name => $value) {
                if (strrchr($name, '?')) {
                    $k = substr(strrchr($name, '?'), 1);
                    $array[$k] = $value;
                } else {
                    $array[$name] = $value;
                }
            }
            $this->getArray = $array;
        }
        if (!empty($key)) {
            $value = $this->getArray[$key] ?? $defaultValue;

            return $value;
        }

        return $this->getArray;
    }

    public function getPost($name, $defaultValue = null, $filter = false)
    {
        $value = (null === $defaultValue) ? $this->getRequest()->getPost($name) : $this->getRequest()->getPost($name, $defaultValue);
        if (!$filter) {
            return $value;
        }

        return \Utility::removeXss($value);
    }

    public function getQuery($name, $defaultValue = null, $filter = false)
    {
        $value = (null === $defaultValue) ? $this->getRequest()->getQuery($name) : $this->getRequest()->getQuery($name, $defaultValue);
        if (!$filter) {
            return $value;
        }

        return \Utility::removeXss($value);
    }

    public function getFiles($name, $defaultValue = null)
    {
        $files = (null === $defaultValue) ? $this->getRequest()->getFiles($name) : $this->getRequest()->getFiles($name, $defaultValue);
        $value = [];
        if ($files) {
            $i = 0;
            if (is_string($files['name'])) {
                $value[$i] = $files;
                ++$i;
            } elseif (is_array($files['name'])) {
                foreach ($files['name'] as $key => $val) {
                    $value[$i]['name'] = $files['name'][$key];
                    $value[$i]['type'] = $files['type'][$key];
                    $value[$i]['tmp_name'] = $files['tmp_name'][$key];
                    $value[$i]['error'] = $files['error'][$key];
                    $value[$i]['size'] = $files['size'][$key];
                    ++$i;
                }
            }
        }

        return $value;
    }

    public function getRaw()
    {
        return $this->getRequest()->getRaw();
    }

    public function isCli()
    {
        return $this->getRequest()->isCli();
    }

    public function isAjax()
    {
        return $this->getRequest()->isXmlHttpRequest();
    }

    public function isGet()
    {
        return $this->getRequest()->isGet();
    }

    public function isPost()
    {
        return $this->getRequest()->isPost();
    }

    public function isHead()
    {
        return $this->getRequest()->isHead();
    }

    public function isOptions()
    {
        return $this->getRequest()->isOptions();
    }

    public function getCookie($name = null, $defaultValue = null, $filter = false)
    {
        if (!$name) {
            return $this->getRequest()->getCookie();
        }
        $value = (null === $defaultValue) ? $this->getRequest()->getCookie($name) : $this->getRequest()->getCookie($name, $defaultValue);
        if (!$filter) {
            return $value;
        }

        return \Utility::removeXss($value);
    }

    public function assign($name, $value)
    {
        $this->getView()->assign($name, $value);
    }

    // public function display($template, $variables = NULL)
    // {
    //     $this->getView()->display($template, $variables);
    // }

    // 不加载视图引擎
    public function disableView()
    {
        \Yaf_Dispatcher::getInstance()->disableView();
    }

    // 加载视图引擎 yaf 默认开启
    public function enableView()
    {
        \Yaf_Dispatcher::getInstance()->enableView();
    }

    //  控制器执行完成后 自动加载对应的模板,渲染
    public function autoRender($boolean = true)
    {
        \Yaf_Dispatcher::getInstance()->autoRender($boolean);
    }

    public function getAllPost()
    {
        return $_POST;
    }
}
