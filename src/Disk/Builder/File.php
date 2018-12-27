<?php
/**
 * Created by PhpStorm.
 * User: dnkol
 * Date: 13.01.2018
 * Time: 0:39
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Builder;

use Leonied7\Yandex\Disk\Model\Stream;
use Leonied7\Yandex\Disk\Query\Builder as QueryBuilder;
use Leonied7\Yandex\Disk\Result\Get;
use Leonied7\Yandex\Disk\Result\Put;
use Leonied7\Yandex\Disk\Stream\Loop;

/**
 * Class File построитель запросов для файлов
 * @package Leonied7\Yandex\Disk\Builder
 */
class File extends Item
{
    /**
     * загружает файл на Яндекс-диск
     * @link https://tech.yandex.ru/disk/doc/dg/reference/put-docpage/
     * @param Stream $stream
     * @return QueryBuilder
     * @throws \Leonied7\Yandex\Disk\Exception\InvalidArgumentException
     */
    public function upload(Stream $stream)
    {
        $builder = QueryBuilder::createByData($this->getQueryData(), $this->getPath());

        $builder
            ->setMethod('PUT')
            ->setExecHandler(Put::class)
            ->setHeaderHandler([Stream::class, 'addMetaData'])
            ->setFile($stream);
        return $builder;
    }

    /**
     * скачивает файл с Яндекс-диска, поддерживает дозагрузку файла
     * @link https://tech.yandex.ru/disk/doc/dg/reference/get-docpage/
     * @param Stream|null $stream
     * @param int $from
     * @param int $to
     * @return QueryBuilder
     * @throws \Leonied7\Yandex\Disk\Exception\InvalidArgumentException
     */
    public function download(Stream $stream = null, $from = null, $to = null)
    {
        if ($stream === null) {
            $stream = new Loop();
        }

        $builder = QueryBuilder::createByData($this->getQueryData(), $this->getPath());

        $builder
            ->setMethod('GET')
            ->setExecHandler(Get::class)
            ->setInFile($stream)
            ->setRange($from, $to);
        return $builder;
    }

    /**
     * Получение превью картинки c Яндекс-диска
     * @link https://tech.yandex.ru/disk/doc/dg/reference/preview-docpage/
     * @param string $size - могут быть переданы любые значения из документации
     * @param Stream|null $stream
     * @return QueryBuilder
     * @throws \Leonied7\Yandex\Disk\Exception\InvalidArgumentException
     */
    public function getPreview($size = 'M', Stream $stream = null)
    {
        if ($stream === null) {
            $stream = new Loop();
        }

        $builder = QueryBuilder::createByData($this->getQueryData(), $this->getPath());
        $builder
            ->setMethod('GET')
            ->setExecHandler(Get::class)
            ->setParams([
                'preview' => '',
                'size' => $size
            ])
            ->setInFile($stream);
        return $builder;
    }
}