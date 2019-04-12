<?php
/**
 * Created by PhpStorm.
 * User: dnkol
 * Date: 14.01.2018
 * Time: 20:08
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex;

use Leonied7\Yandex\Disk\Builder\Disk as DiskBuilder;
use Leonied7\Yandex\Disk\Collection\PropertyCollection;
use Leonied7\Yandex\Disk\Exception\InvalidArgumentException;
use Leonied7\Yandex\Disk\Item\Directory;
use Leonied7\Yandex\Disk\Item\Entity;
use Leonied7\Yandex\Disk\Item\File;
use Leonied7\Yandex\Disk\Query\Data as QueryData;

/**
 * Главный класс для работы с диском, запрашивает основную информацию о диске и клиенте,
 * а так же помогает работать с файлами и папками
 * Class Disk
 * @package Leonied7\Yandex
 */
class Disk implements Entity
{
    /** @var QueryData */
    protected $queryData;
    /** @var DiskBuilder */
    protected $builder;

    /**
     * Facade constructor.
     * @param $token
     * @throws InvalidArgumentException
     */
    public function __construct($token)
    {
        $this->queryData = new QueryData($token);
        $this->builder = new DiskBuilder($this);
    }

    /**
     * @return QueryData
     */
    public function getQueryData()
    {
        return $this->queryData;
    }

    /**
     * @return DiskBuilder построитель запросов, может быть использован для мультизапросов
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * создаёт объект для работы с файлов
     * @param string $path путь
     * @return File
     * @throws InvalidArgumentException
     */
    public function file($path)
    {
        return new File($path, $this->getQueryData());
    }

    /**
     * создаёт объект для работы с директорией
     * @param string $path путь
     * @return Directory
     * @throws InvalidArgumentException
     */
    public function directory($path)
    {
        return new Directory($path, $this->getQueryData());
    }

    /**
     * получение свободного/занятого места
     * @link https://tech.yandex.ru/disk/doc/dg/reference/space-request-docpage/
     * @return PropertyCollection
     * @throws InvalidArgumentException
     */
    public function spaceInfo()
    {
        return $this->getBuilder()->spaceInfo()->send()->getResult();
    }

    /**
     * Возвращает информацию о пользователе
     * @link https://tech.yandex.ru/disk/doc/dg/reference/userinfo-docpage/
     * @return array
     * @throws InvalidArgumentException
     */
    public function getInfo()
    {
        return $this->getBuilder()->getInfo()->send()->getResult();
    }
}