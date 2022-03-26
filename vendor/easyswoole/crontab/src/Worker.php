<?php

namespace EasySwoole\Crontab;

use EasySwoole\Component\Process\Socket\AbstractUnixProcess;
use EasySwoole\Crontab\Protocol\Pack;
use EasySwoole\Crontab\Protocol\Command;
use EasySwoole\Crontab\Protocol\Response;
use Swoole\Coroutine\Socket;
use Swoole\Table;

/**
 * 定时任务工作进程类
 */
class Worker extends AbstractUnixProcess
{
    /** @var Crontab */
    private $crontabInstance; // 定时任务管理器
    /** @var Table */
    private $workerStatisticTable; // 定时任务工作进程信息记录共享内存表
    private $jobs = []; // 所有的定时任务对象
    private $workerIndex = 0; // 工作进程索引号
    /** @var Table */
    private $schedulerTable; // 定时任务调度器信息记录共享内存表

    /**
     * @param $arg 进程对象实例时传入的参数
     */
    public function run($arg)
    {
        $this->crontabInstance = $arg['crontabInstance']; // 定时任务管理器实例对象
        $this->workerStatisticTable = $arg['workerStatisticTable']; // 定时任务工作进程共享内存表
        $this->workerIndex = $arg['workerIndex']; // 工作进程索引号
        $this->workerStatisticTable->set($this->workerIndex, [
            'runningNum' => 0
        ]); // 初始化表
        $this->schedulerTable = $arg['schedulerTable']; // 定时任务调度器共享内存表对象
        $this->jobs = $arg['jobs']; // 所有的定时任务
        parent::run($arg);
    }

    function onAccept(Socket $socket)
    {
        $response = new Response();

        $header = $socket->recvAll(4, 1);
        if (strlen($header) != 4) {
            $response->setStatus(Response::STATUS_PACKAGE_READ_TIMEOUT)->setMsg('recv from client timeout');
            $this->reply($socket, $response);
            return;
        }
        $allLength = Pack::packDataLength($header);
        $data = $socket->recvAll($allLength, 1);
        if (strlen($data) != $allLength) {
            $response->setStatus(Response::STATUS_PACKAGE_READ_TIMEOUT)->setMsg('recv from client timeout');
            $this->reply($socket, $response);
            return;
        }
        $data = unserialize($data);
        if (!$data instanceof Command) {
            $response->setStatus(Response::STATUS_ILLEGAL_PACKAGE)->setMsg('unserialize request as an Command instance fail');
            $this->reply($socket, $response);
            return;
        }
        // 执行定时任务命令
        if ($data->getCommand() === Command::COMMAND_EXEC_JOB) {
            $jobName = $data->getArg();
            if (isset($this->jobs[$jobName])) {
                /** @var JobInterface $job */
                $job = $this->jobs[$jobName];
                $this->workerStatisticTable->incr($this->workerIndex, 'runningNum', 1);
                $this->schedulerTable->incr($jobName, 'taskRunTimes', 1);
                $this->schedulerTable->set($jobName, ['taskCurrentRunTime' => time()]);
                try {
                    $ret = $job->run();
                    $response->setResult($ret);
                    $response->setStatus(Response::STATUS_OK);
                } catch (\Throwable $throwable) {
                    $response->setStatus(Response::STATUS_JOB_EXEC_ERROR);
                    try {
                        $job->onException($throwable);
                    } catch (\Throwable $t) {
                        $call = $this->crontabInstance->getConfig()->getOnException();
                        if (is_callable($call)) {
                            call_user_func($call, $t);
                        } else {
                            throw $t;
                        }
                    }
                } finally {
                    $this->workerStatisticTable->decr($this->workerIndex, 'runningNum', 1);
                    $this->reply($socket, $response);
                }
            } else {
                $response->setStatus(Response::STATUS_JOB_NOT_EXIST);
                $this->reply($socket, $response);
            }
        } else {
            $response->setStatus(Response::STATUS_UNKNOWN_COMMAND);
            $this->reply($socket, $response);
        }
    }

    private function reply(Socket $socket, Response $response)
    {
        $data = serialize($response);
        $socket->sendAll(Pack::pack($data));
        $socket->close();
    }
}