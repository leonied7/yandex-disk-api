<?php
/**
 * Created by PhpStorm.
 * User: Денис
 * Date: 22.02.2019
 * Time: 12:14
 * @author Denis Kolosov <kdnn@mail.ru>
 */

require_once __DIR__ . '/../vendor/autoload.php';

use \Leonied7\Yandex\Disk;

class YandexDiskBackup
{
    protected $sourcePath;
    protected $destinationPath;
    /** @var \Leonied7\Yandex\Disk */
    protected $disk;

    protected $directoriesByDepth = [];
    protected $directoryDepth = [];
    protected $files = [];

    /**
     * YandexDiskBackup constructor.
     * @param $token
     * @param $sourcePath
     * @param $destinationPath
     * @throws \Leonied7\Yandex\Disk\Exception\InvalidArgumentException
     */
    public function __construct($token, $sourcePath, $destinationPath = '/')
    {
        $this->sourcePath = $sourcePath;
        $this->destinationPath = rtrim($destinationPath, '/') . '/';
        $this->disk = new Disk($token);
    }

    public function run()
    {
        $fileSystem = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->sourcePath, FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_SELF), RecursiveIteratorIterator::SELF_FIRST);
        $this->prepareFileSystem($fileSystem);

        $this->checkAndDeleteExistDirectories();
        $this->createDirectories();
        $this->uploadFiles();
    }

    /**
     * @throws \Leonied7\Yandex\Disk\Exception\InvalidArgumentException
     */
    protected function checkAndDeleteExistDirectories()
    {
        $batch = new Disk\Curl\Batch();
        foreach ($this->directoriesByDepth as $directories) {
            foreach ($directories as $path) {
                $directory = $this->disk->directory($path);
                $batch->addBuilder($directory->getBuilder()->has());
            }
        }

        foreach ($batch->exec() as $result) {
            if ($result->isSuccess()) {
                $path = $result->getBuilder()->getPath();
                $depthDirectories = &$this->directoriesByDepth[$this->directoryDepth[$path]];
                if ($key = array_search($path, $depthDirectories, true)) {
                    unset($depthDirectories[$key]);
                }
            }
        }
    }

    /**
     * выполняем создание по каждому уровню вложенности отдельно, т.к. yandex не поддерживает создание папки без существования её родителя
     * @throws \Leonied7\Yandex\Disk\Exception\InvalidArgumentException
     */
    protected function createDirectories()
    {
        foreach ($this->directoriesByDepth as $directories) {
            if (empty($directories)) {
                continue;
            }
            $batch = new Disk\Curl\Batch();
            foreach ($directories as $path) {
                $directory = $this->disk->directory($path);
                $batch->addBuilder($directory->getBuilder()->create());
            }
            foreach ($batch->exec() as $result) {
//                print_r($result->isSuccess() . "\n"); TODO: лог успеха или не успеха создания
            }
        }
    }

    /**
     * загружает файлы на диск
     * @throws Disk\Exception\InvalidArgumentException
     */
    protected function uploadFiles()
    {
        $batch = new Disk\Curl\Batch();

        foreach ($this->files as $path => $path) {
            $file = $this->disk->file("{$this->destinationPath}{$path}");
            $batch->addBuilder($file->getBuilder()->upload(new Disk\Stream\File($path)));
        }
        foreach ($batch->exec() as $result) {
            //                print_r($result->isSuccess() . "\n"); TODO: лог успеха или не успеха загрузки
            if (!$result->isSuccess()) {

//                print_r($result->getResponse());
            }
        }
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
}