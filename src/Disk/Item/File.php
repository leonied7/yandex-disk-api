<?php
/**
 * Created by PhpStorm.
 * User: dnkol
 * Date: 13.01.2018
 * Time: 0:39
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Item;

use Leonied7\Yandex\Disk\Collection\PropertyCollection;
use Leonied7\Yandex\Disk\Model\Stream;
use Leonied7\Yandex\Disk\Query\Data as QueryData;

/**
 * Class File Главный класс для работы с файлом
 * @package Leonied7\Yandex\Disk\Item
 * @method \Leonied7\Yandex\Disk\Builder\File getBuilder()
 */
class File extends Item
{
    /**
     * File constructor.
     * @param $path
     * @param QueryData $queryData
     * @param PropertyCollection|null $property
     * @throws \Leonied7\Yandex\Disk\Exception\InvalidArgumentException
     */
    public function __construct($path, QueryData $queryData, PropertyCollection $property = null)
    {
        $this->type = self::FILE;
        parent::__construct($path, $queryData, $property);
    }

    /**
     * загружает файл на Яндекс-диск
     * @link https://tech.yandex.ru/disk/doc/dg/reference/put-docpage/
     * @param Stream $stream
     * @return bool
     * @throws \Leonied7\Yandex\Disk\Exception\InvalidArgumentException
     */
    public function upload(Stream $stream)
    {
        return $this->getBuilder()->upload($stream)->send()->isSuccess();
    }

    /**
     * скачивает файл с Яндекс-диска, поддерживает дозагрузку файла
     * @link https://tech.yandex.ru/disk/doc/dg/reference/get-docpage/
     * @param Stream|null $stream
     * @param int $from
     * @param int $to
     * @return bool
     * @throws \Leonied7\Yandex\Disk\Exception\InvalidArgumentException
     */
    public function download(Stream $stream = null, $from = 0, $to = null)
    {
        return $this->getBuilder()->download($stream, $from, $to)->send()->isSuccess();
    }

    /**
     * Получение превью картинки c Яндекс-диска
     * @link https://tech.yandex.ru/disk/doc/dg/reference/preview-docpage/
     * @param string $size - могут быть переданы любые значения из документации
     * @param Stream|null $stream
     * @return bool
     * @throws \Leonied7\Yandex\Disk\Exception\InvalidArgumentException
     */
    public function getPreview($size = 'M', Stream $stream = null)
    {
        return $this->getBuilder()->getPreview($size, $stream)->send()->isSuccess();
    }

    /** @return \Leonied7\Yandex\Disk\Builder\File */
    protected function createBuilder()
    {
        return new \Leonied7\Yandex\Disk\Builder\File($this);
    }
}