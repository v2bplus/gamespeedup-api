<?php

class TwigPlugin extends Yaf_Plugin_Abstract
{
    public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {
        $config = Yaf_Application::app()->getConfig()->toArray();
        $dispatcher = Yaf_Dispatcher::getInstance();
        if (in_array($request->module, explode(',', $config['application']['views']['modules']))) {
            $twig = new \Twig\Adapter(APPLICATION_PATH.'modules/'.$request->module.DS.'views'.DS, $config['twig']);
            $_methods = get_class_methods('View');
            array_walk($_methods, function ($funName) use ($twig) {
                $twig->getTwig()->addFunction(new \Twig\TwigFunction($funName, 'View::'.$funName));
            });
            $dispatcher->setView($twig);
        }
    }
}
