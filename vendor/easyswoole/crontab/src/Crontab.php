<?php


namespace EasySwoole\Crontab;


use EasySwoole\Component\Process\Socket\UnixProcessConfig;
use EasySwoole\Crontab\Exception\Exception;
use EasySwoole\Crontab\Protocol\Command;
use EasySwoole\Crontab\Protocol\Pack;
use EasySwoole\Crontab\Protocol\Response;
use EasySwoole\Crontab\Protocol\UnixClient;
use Swoole\Server;
use Swoole\Table;
use EasySwoole\Component\Process\Config as ProcessConfig;

/**
 * 定时任务管理器
 */
class Crontab
{
    private $schedulerTable; // 记录所有定时任务执行信息的共享内存表
    private $workerStatisticTable;
    private $jobs = [];
    /** @var Config */
    private $config;

    function __construct(?Config $config = null)
    {
        if ($config == null) {
            $config = new Config();
        }
        $this->config = $config;
        $this->schedulerTable = new Table(1024); // 调度统计
        $this->schedulerTable->column('taskRule', Table::TYPE_STRING, 35);
        $this->schedulerTable->column('taskRunTimes', Table::TYPE_INT, 8);
        $this->schedulerTable->column('taskNextRunTime', Table::TYPE_INT, 10);
        $this->schedulerTable->column('taskCurrentRunTime', Table::TYPE_INT, 10);
        $this->schedulerTable->column('isStop', Table::TYPE_INT, 1);
        $this->schedulerTable->create();

        $this->workerStatisticTable = new Table(1024); // 工作统计
        $this->workerStatisticTable->column('runningNum', Table::TYPE_INT, 8);
        $this->workerStatisticTable->create();
    }

    function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * 注册一个定时任务
     * @param JobInterface $job
     * @return $this
     * @throws Exception
     */
    public function register(JobInterface $job): Crontab
    {
        if (!isset($this->jobs[$job->jobName()])) {
            $this->jobs[$job->jobName()] = $job;
            return $this;
        } else {
            throw new Exception("{$job->jobName()} hash been register");
        }
    }

    /**
     * 关联定时任务进程到主进程中
     * @param Server $server
     * @throws \EasySwoole\Component\Process\Exception
     */
    public function attachToServer(Server $server)
    {
        if (empty($this->jobs)) {
            return;
        }
        $c = new ProcessConfig();
        $c->setEnableCoroutine(true); // 定时任务调度进程开启协程
        $c->setProcessName("{$this->config->getServerName()}.CrontabScheduler"); // 定时任务调度进程
        $c->setProcessGroup("{$this->config->getServerName()}.Crontab");
        $c->setArg([
            'jobs' => $this->jobs, // 所有定时任务
            'schedulerTable' => $this->schedulerTable, // 调度器共享内存表
            'crontabInstance' => $this // 定时任务管理器对象
        ]);
        $server->addProcess((new Scheduler($c))->getProcess());

        for ($i = 0; $i < $this->config->getWorkerNum(); $i++) {
            //设置统计table信息
            $this->workerStatisticTable->set($i, [ // 表的索引是定时任务工作进程索引号
                'runningNum' => 0
            ]);
            $c = new UnixProcessConfig();
            $c->setEnableCoroutine(true); // 定时任务工作进程开启协程
            $c->setProcessName("{$this->config->getServerName()}.CrontabWorker.{$i}"); // 定时任务工作进程
            $c->setProcessGroup("{$this->config->getServerName()}.Crontab");
            $c->setArg([
                'jobs' => $this->jobs,
                'schedulerTable' => $this->schedulerTable,
                'workerStatisticTable' => $this->workerStatisticTable,
                'crontabInstance' => $this,
                'workerIndex' => $i
            ]);
            $c->setSocketFile($this->indexToSockFile($i));
            $server->addProcess((new Worker($c))->getProcess());
        }
    }

    /**
     * 立即运行指定名称的定时任务
     * @param string $jobName
     * @return Response|null
     */
    public function rightNow(string $jobName): ?Response
    {
        $request = new Command();
        $request->setCommand(Command::COMMAND_EXEC_JOB);
        $request->setArg($jobName);
        // 把命令从定时任务调度进程发送到定时任务工作进程中
        return $this->sendToWorker($request, $this->idleWorkerIndex());
    }

    /**
     * 停止指定名称的定时任务
     * @param string $jobName
     * @return bool
     */
    public function stop(string $jobName): bool
    {
        if (isset($this->jobs[$jobName])) {
            $this->schedulerTable->set($jobName, ['isStop' => 1]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 停止所有定时任务的执行
     * @return bool
     */
    public function stopAll(): bool
    {
        foreach ($this->schedulerTable as $key => $item) {
            $this->schedulerTable->set($key, ['isStop' => 1]);
        }
        return true;
    }

    /**
     * 恢复指定名称的定时任务执行
     * @param string $jobName
     * @return bool
     */
    public function resume(string $jobName): bool
    {
        if (isset($this->jobs[$jobName])) {
            $this->schedulerTable->set($jobName, ['isStop' => 0]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 恢复所有定时任务执行
     * @return bool
     */
    public function resumeAll(): bool
    {
        foreach ($this->schedulerTable as $key => $item) {
            $this->schedulerTable->set($key, ['isStop' => 0]);
        }
        return true;
    }

    /**
     * 修改指定名称定时任务的规则
     * @param $jobName
     * @param $taskRule
     * @return bool
     */
    function resetJobRule($jobName, $taskRule): bool
    {
        if (isset($this->jobs[$jobName])) {
            $this->schedulerTable->set($jobName, ['taskRule' => $taskRule]);
            return true;
        } else {
            return false;
        }
    }

    function schedulerTable(): Table
    {
        return $this->schedulerTable;
    }

    /**
     * 获取空闲定时任务进程索引
     * @return int
     */
    private function idleWorkerIndex(): int
    {
        $index = 0;
        $min = null;
        foreach ($this->workerStatisticTable as $key => $item) {
            $runningNum = intval($item['runningNum']);
            if ($min === null) {
                $min = $runningNum;
            }
            if ($runningNum < $min) {
                $index = $key;
                $min = $runningNum;
            }
        }
        return $index;
    }

    /**
     * 定时任务工作进程索引转换为定时任务进程pid
     * @param int $index
     */
    private function indexToSockFile(int $index): string
    {
        return $this->config->getTempDir() . "/{$this->config->getServerName()}.CrontabWorker.{$index}.sock";
    }

    /**
     * 发送定时任务给定时任务工作进程
     * @param Command $command
     * @param int $index 定时任务工作进程索引
     * @return Response|null
     */
    private function sendToWorker(Command $command, int $index): ?Response
    {
        $data = Pack::pack(serialize($command)); // 系列化数据并封包
        $client = new UnixClient($this->indexToSockFile($index), 10 * 1024 * 1024); // 包最大长度
        $client->send($data);
        $data = $client->recv(3);
        if ($data) {
            $data = Pack::unpack($data); // 解包
            $data = unserialize($data); // 反系列化数据
            if ($data instanceof Response) {
                return $data;
            } else {
                return (new Response())->setStatus(Response::STATUS_ILLEGAL_PACKAGE)->setMsg('unserialize response as an Response instance fail');
            }
        } else {
            return (new Response())->setStatus(Response::STATUS_PACKAGE_READ_TIMEOUT)->setMsg('recv timeout from worker');
        }
    }

}