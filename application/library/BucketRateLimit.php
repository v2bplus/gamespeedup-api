<?php
/**
 * [BucketRateLimit 令牌桶限流]
 */
class BucketRateLimit
{
    private $bucketName = '';
    public $period;
    public $tokens;
    public $maxLifetime = 86400;
    public function __construct($period, $maxRequests, $name = '')
    {
        //name为空说明全站只有一个桶（bucket）
        $this->bucketName = $name;
        $this->period = intval($period);
        $this->tokensNum = intval($maxRequests);
        $this->prefix = 'rate_limit';
        $this->rate = $this->tokensNum / $this->period;
    }

    public function check($uid, $useNum = 1)
    {
        //最后一次获取令牌时间
        $timeKey = $this->getKey('time', $uid);
        //已有令牌数
        $tokensKey = $this->getKey('tokens', $uid);
        $nowTime = time();
        if (LocalCache::check($timeKey)) {
            $timePassed = $nowTime - LocalCache::get($timeKey);
            LocalCache::set($timeKey, $nowTime, $this->maxLifetime);
            $tokens = LocalCache::get($tokensKey);
            //计算上一次获取令牌到现在过去的时间,增加相对应的令牌
            $tokens += $timePassed * $this->rate;

            if ($tokens > $this->tokensNum) {
                $tokens = $this->tokensNum;
            }
            //使用令牌数不能超过最大限制
            if ($tokens < $useNum) {
                LocalCache::set($tokensKey, $tokens, $this->maxLifetime);
                return 0;
            }
            // 消费令牌
            LocalCache::set($tokensKey, $tokens - $useNum, $this->maxLifetime);
            return (int) ceil($tokens);
        }
        //记录当前时间为最后一次处理时间
        LocalCache::set($timeKey, $nowTime, $this->maxLifetime);
        //没有令牌时初始化令牌
        LocalCache::set($tokensKey, $this->tokensNum - $useNum, $this->maxLifetime);
        return $this->tokensNum;
    }

    public function getKey($type, $id)
    {
        return $this->prefix.':'.$this->bucketName.':'.$type.':'.$id;
    }
}
