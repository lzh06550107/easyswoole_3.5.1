<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/24
 * Time: 下午3:20
 */

namespace EasySwoole\Http\Message;

use EasySwoole\Http\Exception\FileException;
use EasySwoole\Utility\File;
use Psr\Http\Message\UploadedFileInterface;

/**
 * 文件上传实现类
 */
class UploadFile implements UploadedFileInterface
{
    private $tempName; // 临时文件路径
    private $stream; // 文件流
    private $size; // 文件大小
    private $error; // 上传文件错误状态
    private $clientFileName; // 文件原始名称
    private $clientMediaType; // 文件类型

    /**
     * 创建上传文件对象
     * @param $tempName
     * @param $size
     * @param $errorStatus
     * @param $clientFilename
     * @param $clientMediaType
     */
    function __construct( $tempName,$size, $errorStatus, $clientFilename = null, $clientMediaType = null)
    {
        $this->tempName = $tempName;
        $this->stream = new Stream(fopen($tempName,"r+"));
        $this->error = $errorStatus;
        $this->size = $size;
        $this->clientFileName = $clientFilename;
        $this->clientMediaType = $clientMediaType;
    }

    public function getTempName()
    {
        return $this->tempName;
    }

    public function getStream()
    {
        return $this->stream;
    }

    /**
     * 移动文件到指定的路径
     * @param $targetPath
     * @throws FileException
     */
    public function moveTo($targetPath)
    {
        if (!(is_string($targetPath) && false === empty($targetPath))) {
            throw new FileException('Please provide a valid path');
        }

        if ($this->size <= 0) {
            throw new FileException('Unable to retrieve stream');
        }

        $dir = dirname($targetPath);
        if (!File::createDirectory($dir)) { // 目录不存在，则创建
            throw new FileException(sprintf('Directory "%s" was not created', $dir));
        }

        $movedSize = file_put_contents($targetPath,$this->stream);
        if (!$movedSize) {
            throw new FileException(sprintf('Uploaded file could not be move to %s', $dir));
        }

        if ($movedSize !== $this->size) {
            throw new FileException(sprintf('File upload specified directory(%s) interrupted', $dir));
        }
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getClientFilename()
    {
        return $this->clientFileName;
    }

    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }

    public function __destruct()
    {
        $this->stream->close();
    }
}
