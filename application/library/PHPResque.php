<?php

class PHPResque
{
    protected static $_instance = null;

    public static function init($type = 'default')
    {
        if (! is_null(self::$_instance)) {
            return true;
        }
        $resque = Yaf_Registry::get('_resqueConfig');
        if ($type == 'default') {
            $configFile = APPLICATION_PATH.'conf'.DS.$resque['config'];
        } elseif ($type == 'crawler') {
            $configFile = APPLICATION_PATH.'conf'.DS.'resque_crawler.yml';
        }
        if (!is_file($configFile)) {
            return false;
        }
        self::$_instance = true;
        \Resque::loadConfig($configFile);
        return self::$_instance;
    }

    public static function stats()
    {
        $return = [];
        $init = self::init();
        if (!$init) {
            return $return;
        }
        return \Resque::stats();
    }

    public static function hosts()
    {
        $return = [];
        $init = self::init();
        if (!$init) {
            return $return;
        }
        return \Resque\Redis::instance()->smembers('hosts');
    }

    public static function allWorkers()
    {
        $return = [];
        $init = self::init();
        if (!$init) {
            return $return;
        }
        $workers = [];
        $rawWorkers = \Resque\Worker::allWorkers();
        foreach ($rawWorkers as $worker) {
            $info = $worker->toArray();
            if ($info) {
                $info['statusText'] = self::getWorkStatusText($info['status']);
                $workers[] = $info;
            }
        }
        return $workers;
    }

    public static function stopWork($type = 'crawler')
    {
        $return = [];
        $init = self::init($type);
        if (!$init) {
            return $return;
        }
        $queueName = $type;
        $workers = \Resque\Worker::allWorkers();
        foreach ($workers as $worker) {
            $queues = $worker->getQueues();
            $return[] = $queues;
            if (in_array($queueName, $queues)) {
                $worker->shutdown();
            }
        }
        return $return;
    }

    public static function allJobs()
    {
        $return = [];
        $init = self::init();
        if (!$init) {
            return $return;
        }
        $jobs = [];
        $jobKeys = \Resque\Redis::instance()->keys('job:*');

        foreach ($jobKeys as $key) {
            $keyArray = explode(':', $key);
            $id = array_pop($keyArray);
            $info = self::jobInfo($id);
            if ($info) {
                unset($info['payload'],$info['output']);
                $info['statusText'] = self::getJobStatusText($info['status']);
                $jobs[] = $info;
            }
        }
        return $jobs;
    }

    public static function jobInfo($id)
    {
        $return = [];
        $init = self::init();
        if (!$init) {
            return $return;
        }
        $data = \Resque\Redis::instance()->hgetall($id);
        if (!$data = \Resque\Redis::instance()->hgetall('job:'.$id)) {
            return $return;
        }
        return $data;
    }

    public static function jobStatus($jobId = null)
    {
        $return = [];
        $init = self::init();
        if (!$init) {
            return $return;
        }
        $job = \Resque\Job::load($jobId);
        return $job->getStatus();
    }

    public static function getJobStatusText($status)
    {
        switch ($status) {
            case \Resque\Job::STATUS_WAITING:
                return 'Waiting';
            case \Resque\Job::STATUS_DELAYED:
                return 'Delayed';
            case \Resque\Job::STATUS_RUNNING:
                return 'Running';
            case \Resque\Job::STATUS_COMPLETE:
                return 'Complete';
            case \Resque\Job::STATUS_CANCELLED:
                return 'Cancelled';
            case \Resque\Job::STATUS_FAILED:
                return 'Failed';
            default:
                return null;
        }
    }

    public static function getWorkStatusText($status)
    {
        if (!array_key_exists($status, \Resque\Worker::$statusText)) {
            return null;
        }
        return \Resque\Worker::$statusText[$status];
    }

    public static function workers()
    {
        $return = [];
        $init = self::init();
        if (!$init) {
            return $return;
        }
        return \Resque\Redis::instance()->smembers('workers');
    }

    public static function queueStats($name = 'default')
    {
        $return = [];
        $init = self::init();
        if (!$init) {
            return $return;
        }
        $key = 'queue:'.$name.':stats';
        return \Resque\Redis::instance()->hgetall($key);
    }

    /**
     * Push a new job onto the queue
     *
     * @param  string $job   The job class
     * @param  mixed  $data  The job data
     * @param  string $queue The queue to add the job to
     * @return Job    job instance
     */
    public static function push($job, $data = null, $queue = 'default')
    {
        $return = [];
        $init = self::init();
        if (!$init) {
            return $return;
        }
        return \Resque::push($job, $data, $queue);
    }

    /**
     * @param  int    $delay This can be number of seconds or unix timestamp
     * @param  string $job   The job class
     * @param  mixed  $data  The job data
     * @param  string $queue The queue to add the job to
     * @return Job    job instance
     */
    public static function later($delay, $job, $data = null, $queue = 'default')
    {
        $return = [];
        $init = self::init();
        if (!$init) {
            return $return;
        }
        return \Resque::later($delay, $job, $data, $queue);
    }
}
