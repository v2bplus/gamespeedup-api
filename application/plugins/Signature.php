<?php
//请求 App签名
class SignaturePlugin extends Yaf_Plugin_Abstract
{
    //请求头
    //x-app-signature-method 签名方式，目前只支持HMAC-SHA1
    //x-app-signature-version 签名版本，1.0
    //x-app-signature-timestamp 请求的时间戳
    //x-app-signature-value 请求的签名值  签名方式 时间戳+secrectKey
    public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {
    }

    public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {
        $config = Yaf_Application::app()->getConfig();
        $module = $request->getModuleName();
        $server = $request->getServer();

        if (isset($config['application']['sign']['modules'])) {
            if (in_array($module, explode(',', $config['application']['sign']['modules']))) {
                if (isset($config['app']['signature'])) {
                    $methodKey = $config['app']['signature']['method_key'];
                    $versionKey = $config['app']['signature']['version_key'];
                    $timestampKey = $config['app']['signature']['time_key'];
                    $valueKey = $config['app']['signature']['value_key'];

                    $secrectKey = $config['app']['signature']['secrectKey'];
                    $keys = array($methodKey, $versionKey, $timestampKey, $valueKey);
                    if (array_diff($keys, array_keys($server))) {
                        Response::authFail(403, '鉴权参数缺失');
                        exit;
                    }
                    if (!in_array($server[$methodKey], ['HMAC-SHA1'])) {
                        Response::authFail(403, '鉴权方法不支持');
                        exit;
                    }
                    if (!in_array($server[$versionKey], ['1.0'])) {
                        Response::authFail(403, '鉴权版本不支持');
                        exit;
                    }
                    // if ($server[$timestampKey] + 200 > time()) {
                    //     Response::authFail(403, '签名时间过期');
                    //     exit;
                    // }
                    $signatureKey = $server[$timestampKey].$secrectKey;
                    $signature = base64_encode(hash_hmac('sha1', $signatureKey, $secrectKey, true));
                    if ($signature != $server[$valueKey]) {
                        Response::authFail(403, '鉴权失败');
                        exit;
                    }
                }
            }
        }
    }
}
