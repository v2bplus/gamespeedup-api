<?php
// \Resque\Event::listen(\Resque\Event::JOB_QUEUE, ['ResquePlugin', 'jobQueue']);

Resque\Event::listen(Resque\Event::WORKER_STARTUP, function ($event, $worker) {

    //手动生成pid文件
    $queues = $worker->getQueues();
    $id = $worker->getId();
    if (count($queues) == 1) {
        $fileName = array_values($queues)[0];
        $pidFile = CACHE_PATH.$fileName.'.pid';
        file_put_contents($pidFile, $id);
    }
});

Resque\Event::listen(Resque\Event::WORKER_SHUTDOWN, function ($event, $worker) {
    $queues = $worker->getQueues();
    $id = $worker->getId();
    if (count($queues) == 1) {
        $queue = array_values($queues)[0];
        if ($queue == 'crawler') {
            $message = 'workerId:'.$id.' Queue:'.$queue;
            \Services\Api\Push::notice($message, \Services\Api\Push::NOTICE_RESQUE);
        }
    }
});
