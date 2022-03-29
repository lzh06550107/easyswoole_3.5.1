<?php

namespace EasySwoole\Utility;

use \Exception;
use \Throwable;
use \FilesystemIterator;

/**
 * Class FileSystem
 * @package EasySwoole\Utility
 * reference link https://github.com/laravel/framework/blob/8.x/src/Illuminate/Filesystem/Filesystem.php
 */
class FileSystem
{
    /**
     * 检查文件或者目录是否存在
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * 检查问或者目录是否不存在
     * @param string $path
     * @return bool
     */
    public function missing(string $path): bool
    {
        return !$this->exists($path);
    }

    /**
     * 是否用锁读取文件内容
     * @param string $path
     * @param bool $lock
     * @return false|string
     * @throws Exception
     */
    public function get(string $path, bool $lock = false)
    {
        if (!$this->isFile($path)) {
            throw new Exception("File does not exist at path {$path}.");
        }
        return $lock ? $this->sharedGet($path) : file_get_contents($path);
    }

    /**
     * 使用共享锁读取文件
     * @param string $path
     * @return false|string
     */
    public function sharedGet(string $path)
    {
        $contents = '';
        $handle = fopen($path, 'rb');
        if (!$handle) {
            return $contents;
        }
        try {
            if (!flock($handle, LOCK_SH)) { // 共享锁锁住文件
                return $contents;
            }
            clearstatcache(true, $path); // 清除文件缓存
            $contents = fread($handle, $this->size($path) ?: 1);
            flock($handle, LOCK_UN); // 释放文件锁
        } finally {
            fclose($handle);
        }
        return $contents;
    }

    /**
     * 根据文件内容生成md5
     * @param string $path
     * @return false|string
     */
    public function hash(string $path)
    {
        return md5_file($path);
    }

    /**
     * 是否使用排它锁写入内容到文件
     * @param string $path
     * @param string $contents
     * @param bool $lock
     * @return false|int
     */
    public function put(string $path, string $contents, bool $lock = false)
    {
        return file_put_contents($path, $contents, $lock ? LOCK_EX : 0);
    }

    /**
     * 替换指定路径文件内容
     * @param string $path
     * @param string $content
     */
    public function replace(string $path, string $content)
    {
        clearstatcache(true, $path);
        $path = realpath($path) ?: $path;
        $tempPath = tempnam(dirname($path), basename($path));
        chmod($tempPath, 0777 - umask());
        file_put_contents($tempPath, $content);
        rename($tempPath, $path);
    }

    /**
     * 在头部追加内容到指定的文件路径
     * @param string $path
     * @param string $data
     * @return false|int
     * @throws Exception
     */
    public function prepend(string $path, string $data)
    {
        if ($this->exists($path)) {
            return $this->put($path, $data . $this->get($path));
        }
        return $this->put($path, $data);
    }

    /**
     * 在尾部追加内容到指定的文件路径
     * @param string $path
     * @param string $data
     * @return false|int
     */
    public function append(string $path, string $data)
    {
        return file_put_contents($path, $data, FILE_APPEND);
    }

    /**
     * 获取或者设置文件权限
     * @param string $path
     * @param int|null $mode
     * @return bool|string
     */
    public function chmod(string $path, ?int $mode = null)
    {
        if ($mode) {
            return chmod($path, $mode);
        }
        return substr(sprintf('%o', fileperms($path)), -4);
    }

    /**
     * 删除指定路径文件
     * @param string|array $paths
     * @return bool
     */
    public function delete($paths)
    {
        $paths = is_array($paths) ? $paths : func_get_args();
        $ret = true;
        foreach ($paths as $path) {
            try {
                if (!unlink($path)) {
                    $ret = false;
                }
            } catch (Throwable $throwable) {
                $ret = false;
            }
        }
        return $ret;
    }

    /**
     * 重命名文件
     * @param string $path
     * @param string $target
     * @return bool
     */
    public function move(string $path, string $target)
    {
        return rename($path, $target);
    }

    /**
     * 复制文件
     * @param string $path
     * @param string $target
     * @return bool
     */
    public function copy(string $path, string $target)
    {
        return copy($path, $target);
    }

    /**
     * 获取文件名称，不包含后缀的文件名
     * @param string $path
     * @return string
     */
    public function name(string $path)
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * 获取文件名称，包含后缀的文件名
     * @param string $path
     * @return string
     */
    public function basename(string $path)
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }

    /**
     * 获取文件的目录路径
     * @param string $path
     * @return string
     */
    public function dirname(string $path)
    {
        return pathinfo($path, PATHINFO_DIRNAME);
    }

    /**
     * 获取文件后缀名
     * @param string $path
     * @return string
     */
    public function extension(string $path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * 返回指定文件或目录的类型，文件还是目录或者其它类型
     * @param string $path
     * @return string
     */
    public function type(string $path)
    {
        return filetype($path);
    }

    /**
     * 返回文件大小的字节数
     * @param string $path
     * @return false|int
     */
    public function size(string $path)
    {
        return filesize($path);
    }

    /**
     * 以 Unix 时间戳形式返回文件内容的上次修改时间
     * @param string $path
     * @return int
     */
    public function lastModified(string $path)
    {
        return filemtime($path);
    }

    /**
     * 判断是否是目录
     * @param string $directory
     * @return bool
     */
    public function isDirectory(string $directory)
    {
        return is_dir($directory);
    }

    /**
     * 判断文件是否可读
     * @param string $path
     * @return bool
     */
    public function isReadable(string $path)
    {
        return is_readable($path);
    }

    /**
     * 判断文件是否可写
     * @param string $path
     * @return bool
     */
    public function isWritable(string $path)
    {
        return is_writable($path);
    }

    /**
     * 判断文件名是否是一个正规的文件
     * @param string $file
     * @return bool
     */
    public function isFile(string $file)
    {
        return is_file($file);
    }

    /**
     * 返回一个包含匹配指定模式的文件名或目录的数组
     * @param string $pattern 检索模式
     * @param int $flags
     * @return array|false
     */
    public function glob(string $pattern, int $flags = 0)
    {
        return glob($pattern, $flags);
    }

    /**
     * 确保路径目录存在，不存在则自动递归创建
     * @param string $path
     * @param int $mode
     * @param bool $recursive
     * @return bool
     */
    public function ensureDirectoryExists(string $path, $mode = 0755, bool $recursive = true)
    {
        if (!$this->isDirectory($path)) {
            return $this->makeDirectory($path, $mode, $recursive);
        }
        return true;
    }

    /**
     * 递归创建目录
     * @param string $path
     * @param int $mode
     * @param bool $recursive
     * @param bool $force
     * @return bool
     */
    public function makeDirectory(string $path, $mode = 0755, bool $recursive = false, bool $force = false): bool
    {
        if ($force) {
            return @mkdir($path, $mode, $recursive);
        }
        return mkdir($path, $mode, $recursive);
    }

    /**
     * 重命名目录
     * @param string $from
     * @param string $to
     * @param bool $overwrite
     * @return bool
     */
    public function moveDirectory(string $from, string $to, bool $overwrite = false): bool
    {
        if ($overwrite && $this->isDirectory($to) && !$this->deleteDirectory($to)) {
            return false;
        }
        return @rename($from, $to) === true;
    }

    /**
     * Copy a directory from one location to another.
     *
     * @param string $directory
     * @param string $destination
     * @param int|null $options
     * @return bool
     */
    public function copyDirectory(string $directory, string $destination, ?int $options = null): bool
    {
        if (!$this->isDirectory($directory)) {
            return false;
        }
        $options = $options ?: FilesystemIterator::SKIP_DOTS;
        // If the destination directory does not actually exist, we will go ahead and
        // create it recursively, which just gets the destination prepared to copy
        // the files over. Once we make the directory we'll proceed the copying.
        $this->ensureDirectoryExists($destination, 0777);
        $items = new FilesystemIterator($directory, $options);
        foreach ($items as $item) {
            // As we spin through items, we will check to see if the current file is actually
            // a directory or a file. When it is actually a directory we will need to call
            // back into this function recursively to keep copying these nested folders.
            $target = $destination . '/' . $item->getBasename();
            if ($item->isDir()) {
                $path = $item->getPathname();
                if (!$this->copyDirectory($path, $target, $options)) {
                    return false;
                }
            }
            // If the current items is just a regular file, we will just copy this to the new
            // location and keep looping. If for some reason the copy fails we'll bail out
            // and return false, so the developer is aware that the copy process failed.
            else {
                if (!$this->copy($item->getPathname(), $target)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 递归删除指定目录的文件和目录
     * @param string $directory
     * @param bool $preserve 是否删除目录
     * @return bool
     */
    public function deleteDirectory(string $directory, bool $preserve = false): bool
    {
        if (!$this->isDirectory($directory)) {
            return false;
        }
        $items = new FilesystemIterator($directory);
        foreach ($items as $item) {
            // If the item is a directory, we can just recurse into the function and
            // delete that sub-directory otherwise we'll just delete the file and
            // keep iterating through each file until the directory is cleaned.
            if ($item->isDir() && !$item->isLink()) {
                $this->deleteDirectory($item->getPathname());
            }
            // If the item is just a file, we can go ahead and delete it since we're
            // just looping through and waxing all of the files in this directory
            // and calling directories recursively, so we delete the real path.
            else {
                $this->delete($item->getPathname()); // 删除文件
            }
        }
        if (!$preserve) {
            @rmdir($directory);
        }
        return true;
    }

    /**
     * 清理整个目录
     * @param string $directory
     * @return bool
     */
    public function cleanDirectory(string $directory): bool
    {
        return $this->deleteDirectory($directory, true);
    }
}
