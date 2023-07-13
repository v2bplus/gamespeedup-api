<?php

namespace FileSystem\QiNiuOss;

use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Config;
use League\Flysystem\Util;
use Qiniu\Auth;
use Qiniu\Http\Client;
use Qiniu\Http\Error;
use Qiniu\Processing\PersistentFop;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

class QiNiuOssAdapter extends AbstractAdapter
{
    private $client;
    private $auth;
    private $bucket;
    private $host;

    private $bucketManager;
    private $uploadManager;
    private $fopManager;

    /**
     * @return string
     */
    protected function getBucket(): string
    {
        return $this->bucket;
    }

    /**
     * QiNiuOssAdapter constructor.
     *
     * @param string $cdnHost
     * @param string $bucket
     * @param string $accessKey
     * @param string $secretKey
     */
    public function __construct($accessKey, $secretKey, $bucket, $cdnHost)
    {
        $this->makeHost($cdnHost);
        $this->auth = new Auth($accessKey, $secretKey);
        $this->bucket = $bucket;
        $this->client = new Client();
    }

    public function getAuth()
    {
        return $this->auth;
    }

    public function verifyUploadCallback($contentType, $authorization, $url, $callbackBody)
    {
        return $this->auth->verifyCallback($contentType, $authorization, $url, $callbackBody);
    }

    /**
     * @param $pathname 保存路径 path/to/filename.ext
     * @param int $expires token时限
     * @param array $policy 上传策略，关联数组 https://developer.qiniu.com/kodo/manual/1206/put-policy
     * @return string
     */
    public function getUploadToken($pathname, $expires = 3600, array $policy = [])
    {
        if (!$policy) {
            $policy = null;
        }
        $uploadToken = $this->auth->uploadToken($this->bucket, $pathname, $expires, $policy, false);
        return $uploadToken;
    }

    /**
     * @param string $path
     * @param string $contents
     * @param Config $config
     *
     * @return array|false
     *
     */
    public function write($path, $contents, Config $config)
    {
        $uploadToken = $this->auth->uploadToken($this->bucket);
        $response = $this->getUploadManager()->put($uploadToken, $path, $contents);
        $this->ossResponse($response);
        return $this->mapFileInfo($path, true, ['contents' => $contents]);
    }

    /**
     * @param string   $path
     * @param resource $resource
     * @param Config   $config
     *
     * @return array|false
     *
     */
    public function writeStream($path, $resource, Config $config)
    {
        return $this->write($path, stream_get_contents($resource), $config);
    }

    /**
     * @param string $path
     * @param string $contents
     * @param Config $config
     *
     * @return array|false|mixed
     *
     */
    public function update($path, $contents, Config $config)
    {
        $uploadToken = $this->auth->uploadToken($this->bucket, $path);
        $this->getUploadManager()->put($uploadToken, $path, $contents);
        return $this->mapFileInfo($path);
    }

    /**
     * @param string   $path
     * @param resource $resource
     * @param Config   $config
     *
     * @return array|false|mixed
     *
     */
    public function updateStream($path, $resource, Config $config)
    {
        return $this->update($path, stream_get_contents($resource), $config);
    }

    /**
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function rename($path, $newpath)
    {
        $error = $this->getBucketManager()->rename($this->bucket, $path, $newpath);
        return !$error;
    }

    /**
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function copy($path, $newpath, $force = false)
    {
        $error = $this->getBucketManager()->copy($this->bucket, $path, $this->bucket, $newpath, $force);
        return !$error;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function delete($path)
    {
        $response = $this->getBucketManager()->delete($this->bucket, $path);
        return !$response;
    }

    /**
     * @param string $dirname
     *
     * @return bool
     */
    public function deleteDir($dirname)
    {
        return true;
    }

    /**
     * @param string $dirname
     * @param Config $config
     *
     * @return array|false
     *
     */
    public function createDir($dirname, Config $config)
    {
        $this->write($dirname.'/.init', date('Y-m-d H:i:s'), $config);
        return $this->mapDirInfo($dirname);
    }

    /**
     * @param string $path
     * @param string $visibility
     *
     * @return array|false|void
     */
    public function setVisibility($path, $visibility)
    {
        // TODO: Implement setVisibility() method.  七牛云没有此功能，只能对整个bucket设置私有或公共
    }

    /**
     * @param string $path
     *
     * @return array|bool|null
     */
    public function has($path)
    {
        $response = $this->getBucketManager()->stat($this->bucket, $path);
        return !$response[1];
    }

    /**
     * @param string $path
     *
     * @return array|false|mixed
     *
     */
    public function read($path)
    {
        $this->getBucketManager()->stat($this->bucket, $path);
        $path = $this->urlEncode($path);
        $privateUrl = $this->privateDownloadUrl($path);
        $result = $this->mapFileInfo($path, false, [
            'contents' => file_get_contents($privateUrl),
        ]);
        return $result;
    }

    /**
     * @param string $path
     *
     * @return array|false|mixed
     *
     */
    public function readStream($path)
    {
        $path = $this->urlEncode($path);
        $url = $path;
        if (!stripos($path, 'token')) {
            $url = $this->privateDownloadUrl($this->host.$path);
        }
        $stream = fopen($url, 'rb');
        return $this->mapFileInfo($path, false, ['stream' => $stream]);
    }

    /**
     * @param string $directory
     * @param bool   $recursive
     *
     * @return array
     *
     */
    public function listContents($directory = '', $recursive = false)
    {
        $directory = $recursive ? '' : $directory;
        $response = $this->getBucketManager()->listFiles($this->getBucket(), $directory);
        $response = $response[0] ?? [];
        $getDir = function ($path, $currentDir) {
            $tmp = strtr($path, [
                $currentDir.'/' => '',
            ]);
            return substr($tmp, 0, stripos($tmp, '/'));
        };
        $files = $response['items'] ?? [];
        $results = [];
        foreach ($files as $file) {
            $dir = $getDir($file['key'], $directory);
            if ($dir) {
                $result = $this->mapDirInfo($directory.'/'.$dir);
            } else {
                $result = $this->mapFileInfo($file['key'], false, [
                    'timestamp' => (int) ceil($file['putTime'] / 1000 / 10000),
                    'size' => $file['fsize'],
                ]);
            }
            $results[] = $result;
        }
        $results = array_unique($results, SORT_REGULAR);
        return $results;
    }

    /**
     * @param string $path
     *
     * @return array|false|mixed
     *
     */
    public function getMetadata($path)
    {
        return $this->mapFileInfo($path, true);
    }

    /**
     * @param string $path
     *
     * @return array|false|mixed
     *
     */
    public function getSize($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * @param string $path
     *
     * @return array|false|mixed
     *
     */
    public function getMimetype($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * @param string $path
     *
     * @return array|false|mixed
     *
     */
    public function getTimestamp($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * @param string $path
     *
     * @return array|false|void
     */
    public function getVisibility($path)
    {
        // TODO: Implement getVisibility() method. 七牛暂没有此功能
    }

    /**
     * @param string $baseUrl         请求url
     * @param bool   $isBucketPrivate bucket是否为私有，如果是私有m3u8文件会对相关ts文件进行授权处理(https://developer.qiniu.com/dora/api/1292/private-m3u8-pm3u8)
     * @param int    $expires
     *
     * @return string
     */
    public function privateDownloadUrl($baseUrl, $isBucketPrivate = false, $expires = 3600)
    {
        if (0 !== strpos($baseUrl, 'http')) {
            $baseUrl = $this->host.$baseUrl;
        }

        if ($isBucketPrivate && strstr($baseUrl, 'm3u8')) {
            if (strstr($baseUrl, '?')) {
                $baseUrl .= '&pm3u8/0';
            } else {
                $baseUrl .= '?pm3u8/0';
            }
        }

        return $this->auth->privateDownloadUrl($baseUrl, $expires);
    }

    public function makeBucket($bucket, $region = 'z0')
    {
        $response = $this->getBucketManager()->createBucket($bucket, $region);
        return $response;
    }

    public function fetchBucket($bucket)
    {
        $response = $this->getBucketManager()->bucketInfo($bucket);
        return $response;
    }

    /**
     * @return BucketManager
     */
    protected function getBucketManager()
    {
        if (!$this->bucketManager) {
            $this->bucketManager = new BucketManager($this->auth);
        }
        return $this->bucketManager;
    }

    /**
     * @return UploadManager
     */
    protected function getUploadManager()
    {
        if (!$this->uploadManager) {
            $this->uploadManager = new UploadManager();
        }
        return $this->uploadManager;
    }

    protected function getFopManager()
    {
        if (!$this->fopManager) {
            $this->fopManager = new PersistentFop($this->auth);
        }
        return $this->fopManager;
    }

    // protected function normalizeResponse(array $response, $path = null)
    // {
    //     $result = [
    //         'path' => $path ?: $this->removePathPrefix(
    //             $response['Key'] ?? $response['Prefix']
    //         ),
    //     ];
    //     $result = array_merge($result, Util::pathinfo($result['path']));
    //     if (isset($response['LastModified'])) {
    //         $result['timestamp'] = strtotime($response['LastModified']);
    //     }
    //     if ($this->isOnlyDir($result['path'])) {
    //         $result['type'] = 'dir';
    //         $result['path'] = rtrim($result['path'], '/');
    //         return $result;
    //     }
    //     return array_merge($result, Util::map($response, static::$resultMap), ['type' => 'file']);
    // }

    /**
     * @param string $path
     *
     * @return string
     */
    protected function urlEncode($path)
    {
        return strtr($path, [
            ' ' => '%20',
        ]);
    }

    /**
     * @param string $path
     * @param array  $normalized
     *
     * @return array
     *
     */
    protected function getFileMeta($path, array $normalized)
    {
        $response = $this->getBucketManager()->stat($this->bucket, $path);
        $this->ossResponse($response);
        $normalized['mimetype'] = $response['mimeType'] ?? '';
        $normalized['timestamp'] = (int) ceil($response['putTime'] / 1000 / 10000);
        $normalized['size'] = $response['fsize'];
        return $normalized;
    }

    /**
     * @param string $path
     * @param bool   $requireMeta
     * @param array  $options
     *
     * @return array|mixed
     *
     */
    protected function mapFileInfo($path, $requireMeta = false, $options = [])
    {
        $normalized = [
            'type' => 'file',
            'path' => $path,
        ];

        if ($requireMeta) {
            $normalized = $this->getFileMeta($path, $normalized);
        }
        $normalized = array_merge($normalized, $options);
        return $normalized;
    }

    /**
     * @param string $dirname
     *
     * @return array
     */
    protected function mapDirInfo($dirname)
    {
        return ['path' => $dirname, 'type' => 'dir'];
    }

    private function makeHost($cdnHost)
    {
        $host = strripos($cdnHost, '/') + 1 === strlen($cdnHost) ? $cdnHost : $cdnHost.'/';
        $this->host = strtolower($host);
        if (0 !== strpos($this->host, 'http')) {
            $this->host = 'http://'.$this->host;
        }
    }

    protected function ossResponse(array &$response)
    {
        if ($response[1] instanceof Error) {
            $error = $response['1'];
            $this->createExceptionIfError($error);
        }
        $response = $response[0];
    }

    protected function createExceptionIfError($error = null)
    {
        if ($error instanceof Error) {
            throw new \Exception($error->message(), $error->code());
        }
    }
}
