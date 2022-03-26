<?php


namespace EasySwoole\Component\Process;

use EasySwoole\Component\Singleton;
use Swoole\Process;
use Swoole\Server;
use Swoole\Table;

/**
 * 进程管理器
 */
class Manager
{
    use Singleton;

    /** @var AbstractProcess[] $processList */
    protected $processList = [];
    protected $autoRegister = [];
    protected $table;

    /**
     * 初始化共享内存表，来保存各个进程的基本信息
     */
    public function __construct()
    {
        $this->table = new Table(2048);
        $this->table->column('pid', Table::TYPE_INT, 8);
        $this->table->column('name', Table::TYPE_STRING, 50);
        $this->table->column('group', Table::TYPE_STRING, 50);
        $this->table->column('memoryUsage', Table::TYPE_INT, 8);
        $this->table->column('memoryPeakUsage', Table::TYPE_INT, 8);
        $this->table->column('startUpTime', Table::TYPE_INT, 8);
        $this->table->column('hash', Table::TYPE_STRING, 32);
        $this->table->create();
    }

    /**
     * 获取进程共享内存表
     * @return Table
     */
    public function getProcessTable(): Table
    {
        return $this->table;
    }

    /**
     * 通过进程id来获取指定的进程
     * @param int $pid
     * @return AbstractProcess|null
     */
    public function getProcessByPid(int $pid): ?AbstractProcess
    {
        $info = $this->table->get($pid);
        if($info){
            $hash = $info['hash'];
            if(isset($this->processList[$hash])){
                return $this->processList[$hash];
            }
        }
        return null;
    }

    /**
     * 根据进程名称来获取进程
     * @param string $name
     * @return array
     */
    public function getProcessByName(string $name): array
    {
        $ret = [];
        foreach ($this->processList as $process) {
            if ($process->getProcessName() === $name) {
                $ret[] = $process;
            }
        }

        return $ret;
    }

    /**
     * 获取指定进程组的所有进程
     * @param string $group
     * @return array
     */
    public function getProcessByGroup(string $group): array
    {
        $ret = [];
        foreach ($this->processList as $process) {
            if ($process->getConfig()->getProcessGroup() === $group) {
                $ret[] = $process;
            }
        }

        return $ret;
    }

    /**
     * 关闭指定的进程或进程组
     * @param $pidOrGroupName
     * @param $sig 信号
     * @return array
     */
    public function kill($pidOrGroupName, $sig = SIGTERM): array
    {
        $list = [];
        if (is_numeric($pidOrGroupName)) {
            $info = $this->table->get($pidOrGroupName);
            if ($info) {
                $list[$pidOrGroupName] = $pidOrGroupName;
            }
        } else {
            foreach ($this->table as $key => $value) {
                if ($value['group'] == $pidOrGroupName) {
                    $list[$key] = $value;
                }
            }
        }
        $this->clearPid($list);
        foreach ($list as $pid => $value) {
            Process::kill($pid, $sig);
        }
        return $list;
    }

    public function info($pidOrGroupName = null)
    {
        $list = [];
        if ($pidOrGroupName == null) {
            foreach ($this->table as $pid => $value) {
                $list[$pid] = $value;
            }
        } else if (is_numeric($pidOrGroupName)) {
            $info = $this->table->get($pidOrGroupName);
            if ($info) {
                $list[$pidOrGroupName] = $info;
            }
        } else {
            foreach ($this->table as $key => $value) {
                if ($value['group'] == $pidOrGroupName) {
                    $list[$key] = $value;
                }
            }
        }

        $sort = array_column($list, 'group');
        array_multisort($sort, SORT_DESC, $list);
        foreach ($list as $key => $value) {
            unset($list[$key]);
            $list[$value['pid']] = $value;
        }
        return $this->clearPid($list);
    }

    // 添加进程
    public function addProcess(AbstractProcess $process, bool $autoRegister = true): Manager
    {
        $hash = spl_object_hash($process->getProcess());
        $this->autoRegister[$hash] = $autoRegister;
        $this->processList[$hash] = $process;
        return $this;
    }

    // 关联进程到主服务中
    public function attachToServer(Server $server)
    {
        foreach ($this->processList as $hash => $process) {
            if ($this->autoRegister[$hash] === true) {
                $server->addProcess($process->getProcess());
            }
        }
    }

    public function pidExist(int $pid)
    {
        return Process::kill($pid, 0); // $signo=0，可以检测进程是否存在，不会发送信号
    }

    protected function clearPid(array $list)
    {
        foreach ($list as $pid => $value) {
            if (!$this->pidExist($pid)) {
                $this->table->del($pid); // 清除进程表中的当前进程数据
                unset($list[$pid]);
            }
        }
        return $list;
    }
}
