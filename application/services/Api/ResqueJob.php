<?php
namespace Services\Api;

use Exception;
use PHPResque;
use Resque\Worker;

class ResqueJob
{
    public static function info()
    {
        $status = PHPResque::stats();
        $hosts = PHPResque::hosts();
        $workers = [];
        $rawWorkers = PHPResque::allWorkers();
        foreach ($rawWorkers as $worker) {
            $packet = $worker->getPacket();
            $workers[] = new Worker(
                (string) $worker,
                $packet['status'],
                $packet['started'],
                !empty($packet['job_id']) ? $packet['job_id'] : null,
                $packet['job_started'],
                $packet['processed'],
                $packet['cancelled'],
                $packet['failed'],
                $packet['interval'],
                $packet['timeout'],
                $packet['memory'],
                $packet['memory_limit']
            );
        }
        return $workers;
    }

    public static function workerList()
    {
        try {
            $data = PHPResque::allWorkers();
            return [
                'status' => 1,
                'data' => $data,
                'msg' => ''
            ];
        } catch (Exception $e) {
            return [
                'status' => 0,
                'code' => EXCEPTION_ERROR_CODE,
                'msg' => $e->getMessage()
            ];
        }
    }

    public static function jobList()
    {
        try {
            $data = PHPResque::allJobs();
            return [
                'status' => 1,
                'data' => $data,
                'msg' => ''
            ];
        } catch (Exception $e) {
            return [
                'status' => 0,
                'code' => EXCEPTION_ERROR_CODE,
                'msg' => $e->getMessage()
            ];
        }
    }

    public static function stopJob($queue = 'meituan')
    {
        try {
            $data = PHPResque::stopWork($queue, 'meituan');
            return [
                'status' => 1,
                'data' => $data,
                'msg' => ''
            ];
        } catch (Exception $e) {
            return [
                'status' => 0,
                'code' => EXCEPTION_ERROR_CODE,
                'msg' => $e->getMessage()
            ];
        }
    }

    public static function add($name = null, $arguments = null, $delay = null, $queue = 'default')
    {
        try {
            $class = 'Jobs\\'.$name;
            if (!class_exists($class)) {
                throw new Exception($name.' class不存在');
            }
            if ($delay) {
                $job = PHPResque::later($delay, $class, $arguments, $queue);
            } else {
                $job = PHPResque::push($class, $arguments, $queue);
            }
            $jobId = $job->getId();
            if (!$jobId) {
                throw new Exception('添加'.$name.' Job错误');
            }
            $data = ['id' => $jobId];
            return [
                'status' => 1,
                'data' => $data,
                'msg' => ''
            ];
        } catch (Exception $e) {
            return [
                'status' => 0,
                'code' => EXCEPTION_ERROR_CODE,
                'msg' => $e->getMessage()
            ];
        }
    }

    public static function jobStatus($jobId = null)
    {
        try {
            if (!$jobId) {
                throw new Exception('JobId不能为空');
            }
            $statusId = PHPResque::jobStatus($jobId);
            $statusText = PHPResque::getJobStatusText($statusId);
            $data = ['id' => $statusId, 'content' => $statusText];
            return [
                'status' => 1,
                'data' => $data,
                'msg' => ''
            ];
        } catch (Exception $e) {
            return [
                'status' => 0,
                'code' => EXCEPTION_ERROR_CODE,
                'msg' => $e->getMessage()
            ];
        }
    }
}
