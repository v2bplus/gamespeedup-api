<?php

namespace Logger\Monolog\Processor;

use Monolog\Handler\AbstractProcessingHandler;

class SessionProcessor extends AbstractProcessingHandler
{
    private $uid;
    private $duration;
    private $post;
    private $statement;

    public function __construct()
    {
        if (defined('YAF_BEGIN_TIME')) {
            $this->duration = YAF_BEGIN_TIME;
        }
    }

    public function __invoke(array $record)
    {
        if (isset($_SESSION['user'])) {
            $user = $_SESSION['user'];
            $uid = $user['uid'] ?? '';
            $record['extra']['uid'] = $uid;
        }
        if (isset($_POST)) {
            $record['extra']['post'] = $_POST;
        }
        if (defined('YAF_BEGIN_TIME')) {
            $record['extra']['duration'] = number_format(microtime(true) - $this->duration, 3);
        }
        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $record['extra']['real_ip'] = $_SERVER['HTTP_X_REAL_IP'];
        }

        return $record;
    }

    public function write(array $record): void
    {
        $this->statement->execute([
            'channel' => $record['channel'],
            'level' => $record['level'],
            'message' => $record['formatted'],
            'time' => $record['datetime']->format('U'),
        ]);
    }
}
