<?php
/**
 * Created by PhpStorm.
 * User: Денис
 * Date: 22.02.2019
 * Time: 12:14
 * @author Denis Kolosov <kdnn@mail.ru>
 */

use \Leonied7\Yandex\Disk;
use \Leonied7\Yandex\Disk\Http\Transport;

class YandexDiskBackup
{
    protected $sourcePath;
    protected $destinationPath;
    /** @var \Leonied7\Yandex\Disk */
    protected $disk;
    /** @var Transport */
    protected $transport;

    protected $directoriesByDepth = [];
    protected $directoryDepth = [];
    protected $files = [];

    /**
     * YandexDiskBackup constructor.
     * @param $token
     * @param $sourcePath
     * @param $destinationPath
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function __construct($token, $sourcePath, $destinationPath = '/')
    {
        $this->sourcePath = $sourcePath;
        $this->destinationPath = rtrim($destinationPath, '/') . '/';
        $this->disk = new Disk($token);
        $this->transport = new Transport();
    }

    /**
     * @throws Disk\Exception\Exception
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function run()
    {
        $fileSystem = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->sourcePath, FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_SELF), RecursiveIteratorIterator::SELF_FIRST);
        $this->prepareFileSystem($fileSystem);

        $this->checkAndRemoveExistDirectories();
        $this->createDirectories();
        $this->uploadFiles();
    }

    protected function prepareFileSystem(RecursiveIteratorIterator $fileSystem)
    {
        /** @var RecursiveDirectoryIterator $element */
        foreach ($fileSystem as $key => $element) {
            if ($element->isDir()) {
                $path = "{$this->destinationPath}{$element->getSubPathname()}/";
                $this->directoriesByDepth[$fileSystem->getDepth()][] = $path;
                $this->directoryDepth[$path] = $fileSystem->getDepth();
            } else {
                $this->files[$key] = $element->getSubPathname();
            }
        }
    }

    /**
     * @throws Disk\Exception\InvalidArgumentException
     */
    protected function checkAndRemoveExistDirectories()
    {
        $requests = [];
        foreach ($this->directoriesByDepth as $directories) {
            foreach ($directories as $path) {
                $directory = $this->disk->directory($path);
                $requests[$directory->getPath()] = $directory->getBuilder()->has()->getRequest();
            }
        }

        if (empty($requests)) {
            return;
        }

        foreach ($this->transport->batchSend($requests) as $path => $result) {
            if ($result->isSuccess()) {
                $depthDirectories = &$this->directoriesByDepth[$this->directoryDepth[$path]];
                if ($key = array_search($path, $depthDirectories, true)) {
                    unset($depthDirectories[$key]);
                }
            }
        }
    }

    /**
     * выполняем создание по каждому уровню вложенности отдельно, т.к. yandex не поддерживает создание папки без существования её родителя
     * @throws Disk\Exception\InvalidArgumentException
     * @throws Disk\Exception\Exception
     */
    protected function createDirectories()
    {
        foreach ($this->directoriesByDepth as $directories) {
            if (empty($directories)) {
                continue;
            }

            $requests = [];
            foreach ($directories as $path) {
                $directory = $this->disk->directory($path);
                $requests[$directory->getPath()] = $directory->getBuilder()->create()->getRequest();
            }
            foreach ($this->transport->batchSend($requests) as $path => $result) {
                if(!$result->isSuccess()) {
                    throw new Disk\Exception\Exception("'{$result->getResult()}' for directory {$path}", $result->getResponseCode());
                }
            }
        }
    }

    /**
     * загружает файлы на диск
     * @throws Disk\Exception\InvalidArgumentException
     * @throws Disk\Exception\Exception
     */
    protected function uploadFiles()
    {
        $requests = [];

        foreach ($this->files as $localPath => $path) {
            $file = $this->disk->file("{$this->destinationPath}{$path}");
            $requests[$file->getPath()] = $file->getBuilder()->upload(new Disk\Stream\File($localPath))->getRequest();
        }
        foreach ($this->transport->batchSend($requests) as $path => $result) {
            if (!$result->isSuccess()) {
                throw new Disk\Exception\Exception("'{$result->getResult()}' for file {$path}", $result->getResponseCode());
            }
        }
    }
}