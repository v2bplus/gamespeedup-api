<?php

class RouterPlugin extends Yaf_Plugin_Abstract
{
    public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {
    }

    public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {
        $modules = Yaf_Application::app()->getModules();
        $config = Yaf_Application::app()->getConfig();
        $server = $request->getServer();
        $uri = $request->getRequestUri();
        $dispatcher = $config->application->dispatcher;
        $serverName = $server['HTTP_HOST'] ?? '';
        $uriInfo = explode('/', trim($uri, '/'));
        $module = $controller = $action = '';
        $siteConfig = Yaf_Registry::get('_siteConfig');

        // 根据域名来配置不同的网站项目
        if (isset($siteConfig['domain'])) {
            $domain = $siteConfig['domain'];
            foreach ($domain as $key => $url) {
                if (is_array($url)) {
                    if (in_array($serverName, $url)) {
                        $module = ucfirst($key);

                        break;
                    }
                } else {
                    if ($serverName == $url) {
                        $module = ucfirst($key);

                        break;
                    }
                }
            }
        }
        if (!$module) {
            $module = ucfirst(array_shift($uriInfo));
        }
        if (!in_array($module, $modules)) {
            $module = 'Index';
            if ($dispatcher) {
                $module = $dispatcher->defaultModule ? $dispatcher->defaultModule : 'Index';
            }
        }
        $controller = array_shift($uriInfo);
        if (!$controller) {
            $controller = 'Index';
            if ($dispatcher) {
                $controller = $dispatcher->defaultController ? $dispatcher->defaultController : 'Index';
            }
        }
        $action = array_shift($uriInfo);
        if (!$action) {
            $action = 'index';
            if ($dispatcher) {
                $action = $dispatcher->defaultAction ? $dispatcher->defaultAction : 'index';
            }
        }
        $request->setModuleName(ucfirst($module));
        $request->setControllerName(ucfirst($controller));
        $request->setActionName($action);
    }
}
