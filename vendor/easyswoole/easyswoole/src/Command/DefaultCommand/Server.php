<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Color;
use EasySwoole\Command\CommandManager;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\SysConst;
use EasySwoole\Utility\ArrayToTextTable;
use Swoole\Coroutine\Scheduler;

/**
 * 服务管理相关命令
 */
class Server implements CommandInterface
{
    public function commandName(): string
    {
        return 'server';
    }

    public function desc(): string
    {
        return 'Easyswoole server';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addAction('start', 'start-up');
        $commandHelp->addAction('stop', 'stop it');
        $commandHelp->addAction('reload', 'reload worker process');
        $commandHelp->addAction('restart', 'restart EasySwoole server');
        $commandHelp->addAction('status', 'view EasySwoole status');
        $commandHelp->addActionOpt('-d', 'start as a daemon');
        $commandHelp->addActionOpt('-force', 'stop by force');
        $commandHelp->addActionOpt('-mode=dev', 'run mode,dev is default mode');
        return $commandHelp;
    }

    public function exec(): ?string
    {
        $action = CommandManager::getInstance()->getArg(0); // 第1个参数是动作命令
        if (method_exists($this, $action) && $action != 'help') {
            Core::getInstance()->initialize();
            return $this->$action();
        }

        return CommandManager::getInstance()->displayCommandHelp($this->commandName());
    }

    /**
     * 服务启动
     */
    protected function start()
    {
        // 防止重复启动
        defined('EASYSWOOLE_RUNNING') or define('EASYSWOOLE_RUNNING', true);
        $conf = Config::getInstance();
        // php easyswoole server start -d
        $daemonize = CommandManager::getInstance()->issetOpt('d');

        if ($daemonize) { // 后台模式运行
            $conf->setConf("MAIN_SERVER.SETTING.daemonize", $daemonize);
        }

        $serverType = $conf->getConf('MAIN_SERVER.SERVER_TYPE');
        $displayItem = [];
        switch ($serverType) {
            case EASYSWOOLE_SERVER:
            {
                $serverType = 'SWOOLE_SERVER';
                break;
            }
            case EASYSWOOLE_WEB_SERVER:
            {
                $serverType = 'SWOOLE_WEB';
                break;
            }
            case EASYSWOOLE_WEB_SOCKET_SERVER:
            {
                $serverType = 'SWOOLE_WEB_SOCKET';
                break;
            }
            default:
            {
                $serverType = 'UNKNOWN';
            }
        }
        $displayItem['main server'] = $serverType;
        $displayItem['listen address'] = $conf->getConf('MAIN_SERVER.LISTEN_ADDRESS');
        $displayItem['listen port'] = $conf->getConf('MAIN_SERVER.PORT');
        $data = $conf->getConf('MAIN_SERVER.SETTING');
        if (empty($data['user'])) {
            $data['user'] = get_current_user();
        }
        $displayItem = $displayItem + $data;
        $displayItem['swoole version'] = phpversion('swoole');
        $displayItem['php version'] = phpversion();
        $displayItem['easyswoole version'] = SysConst::EASYSWOOLE_VERSION;
        $displayItem['run mode'] = Core::getInstance()->runMode();
        $displayItem['temp dir'] = EASYSWOOLE_TEMP_DIR;
        $displayItem['log dir'] = EASYSWOOLE_LOG_DIR;

        // 打印easyswoole log
        $msg = Color::green(Utility::easySwooleLog()) . "\n";
        foreach ($displayItem as $key => $value) {
            $msg .= Utility::displayItem($key, $value) . "\n";
        }
        echo $msg;
        Core::getInstance()->createServer()->start(); // 启动服务，进程中开启协程
    }

    /**
     * 停止服务
     * @return string
     */
    protected function stop()
    {
        // 找到进程id文件
        $pidFile = Config::getInstance()->getConf("MAIN_SERVER.SETTING.pid_file");
        $msg = '';
        if (file_exists($pidFile)) {
            $pid = intval(file_get_contents($pidFile));
            if (!\Swoole\Process::kill($pid, 0)) { // 查询进程是否存在
                $msg = Color::danger("pid :{$pid} not exist ");
                unlink($pidFile);
            } else {
                $force = CommandManager::getInstance()->issetOpt('force');
                if ($force) {
                    \Swoole\Process::kill($pid, SIGKILL); // 和其他信号不同，SIGKILL和SIGSTOP是不可被Catch的
                } else {
                    \Swoole\Process::kill($pid);
                }
                //等待5秒
                $time = time();
                while (true) {
                    usleep(1000);
                    if (!\Swoole\Process::kill($pid, 0)) { // 如果进程不存在，则删除pid文件
                        if (is_file($pidFile)) {
                            unlink($pidFile);
                        }
                        $msg = "server stop for pid {$pid} at " . date("Y-m-d H:i:s");
                        $msg = Color::success($msg);
                        break;
                    } else { 
                        if (time() - $time > 15) { // TODO 等待5秒 这里却是15s
                            $msg = Color::danger("stop server fail for pid:{$pid} , try [php easyswoole server stop -force] again");
                            break;
                        }
                    }
                }
            }
        } else {
            $msg = Color::danger("pid file does not exist, please check whether to run in the daemon mode!");
        }
        return $msg;
    }

    /**
     * 工作进程重新加载，不会影响用户自定义进程
     * @return string
     */
    protected function reload()
    {
        $pidFile = Config::getInstance()->getConf("MAIN_SERVER.SETTING.pid_file");
        if (file_exists($pidFile)) {
            Utility::opCacheClear();
            $pid = file_get_contents($pidFile);
            if (!\Swoole\Process::kill($pid, 0)) { // 如果进程不存在
                $msg = Color::danger("pid :{$pid} not exist ");
            } else {
                \Swoole\Process::kill($pid, SIGUSR1);
                $msg = "send server reload command to pid:{$pid} at " . date("Y-m-d H:i:s");
                $msg = Color::success($msg);
            }
        } else {
            $msg = Color::danger("pid file does not exist, please check whether to run in the daemon mode!");
        }
        return $msg;
    }

    /**
     * 重启服务
     * @return string
     */
    protected function restart()
    {
        $msg = $this->stop();
        $this->start();
        return $msg;
    }

    /**
     * 查看服务器状态
     * @return mixed
     */
    protected function status()
    {
        // 这里初始化一个协程调度器
        $run = new Scheduler();
        $run->add(function () use (&$result) {
            $result = Utility::bridgeCall('status', function (Package $package) {
                $data = $package->getArgs();
                $data['start_time'] = date('Y-m-d H:i:s', $data['start_time']); // 服务启动时间

                $final = [];

                foreach ($data as $key => $val){
                    $final[] = [
                        'item'=>$key,
                        'value'=>$val
                    ];
                }
                return new ArrayToTextTable($final);
            }, 'server');
        }); // 创建一个协程
        $run->start(); // 启动协程调度器
        return $result;
    }
}