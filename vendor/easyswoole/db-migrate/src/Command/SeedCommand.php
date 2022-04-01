<?php

namespace EasySwoole\DatabaseMigrate\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\Color;
use EasySwoole\DatabaseMigrate\Command\AbstractInterface\CommandAbstract;
use EasySwoole\DatabaseMigrate\MigrateManager;
use EasySwoole\DatabaseMigrate\Utility\Util;
use EasySwoole\DatabaseMigrate\Validate\Validator;
use EasySwoole\Utility\File;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Class SeedCommand，数据填充工具，不加操作项即为执行填充数据操作，添加操作项即为创建填充模板
 * @package EasySwoole\DatabaseMigrate\Command\Migrate
 * @author heelie.hj@gmail.com
 * @date 2020/9/19 00:30:36
 */
final class SeedCommand extends CommandAbstract
{
    public function commandName(): string
    {
        return 'migrate fill';
    }

    public function desc(): string
    {
        return 'database migrate data fill';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addActionOpt('-cs, --class', 'The class name for data filling'); // 直接填写文件名或者类名，即为执行指定填充文件(多个文件用 ',' 隔开)；不加参数直接执行seed命令为执行数据填充目录下所有填充操作
        $commandHelp->addActionOpt('-cr, --create', 'Create a seeder template'); // 创建一个数据填充模板
        return $commandHelp;
    }

    /**
     * @return string|null
     */
    public function exec(): ?string
    {
        if ($createClass = $this->getOpt(['cr', 'create'])) {
            return $this->create($createClass); // 创建数据填充模板
        }
        if ($class = $this->getArg(1)) { // 获取需要执行的所有指定填充文件名称
            return $this->seederRun(explode(',', $class)); // 执行指定的数据填充文件
        }
        return $this->seederRun(Util::getAllSeederFiles()); // 执行所有的数据填充文件
    }

    /**
     * 创建数据填充模板
     * @param $className 指定表名称
     * @return string
     */
    private function create($className)
    {
        if (!Validator::isValidName($className)) {
            throw new InvalidArgumentException('The migrate table name can only consist of letters, numbers and underscores, and cannot start with numbers and underscore');
        }

        if (Validator::validClass($className, 'seeder')) {
            throw new InvalidArgumentException(sprintf('class "%s" already exists', $className));
        }

        $className = ucfirst(Util::lineConvertHump($className));

        $config         = MigrateManager::getInstance()->getConfig();
        $seederFilePath = $config->getSeederPath() . $className . '.php';

        if (!File::touchFile($seederFilePath, false)) { // 创建文件
            throw new RuntimeException(sprintf('seeder file "%s" create failed, file already exists or directory is not writable', $seederFilePath));
        }

        $contents = str_replace($config->getSeederTemplateClassName(), $className, file_get_contents($config->getSeederTemplate()));

        if (file_put_contents($seederFilePath, $contents) === false) {
            throw new RuntimeException(sprintf('Seeder file "%s" is not writable', $seederFilePath));
        }

        return Color::success(sprintf('Seeder file "%s" created successfully', $seederFilePath));
    }

    /**
     * 运行执行的数据填充文件
     * @param array $waitSeedFiles 数据填充文件
     * @return string
     */
    private function seederRun(array $waitSeedFiles)
    {
        $config = MigrateManager::getInstance()->getConfig();
        $outMsg = [];
        array_walk($waitSeedFiles, function ($className) use (&$outMsg, $config) {
            $className = pathinfo($className, PATHINFO_FILENAME);
            $filename  = $className . '.php';
            $filepath  = $config->getSeederPath() . $filename;
            $startTime = microtime(true);
            $outMsg[]  = "<brown>Seeding: </brown>" . $filename;
            if (!file_exists($filepath)) {
                return $outMsg[] = "<warning>Seeded:  </warning>" . sprintf('seeder file "%s" not found. go next.', $filename);
            }
            Util::requireOnce($filepath);
            try {
                $ref = new \ReflectionClass($className);
                call_user_func([$ref->newInstance(), 'run']); // 运行添加文件方法来填充数据
                return $outMsg[] = "<green>Seeded:  </green>{$filename} (" . round(microtime(true) - $startTime, 2) . " seconds)";
            } catch (\Throwable $e) {
                return $outMsg[] = "<warning>Seeded:  </warning>" . sprintf('seeder file "%s" error: %s', $filename, $e->getMessage());
            }
        });

        return Color::render(join(PHP_EOL, $outMsg));
    }
}
