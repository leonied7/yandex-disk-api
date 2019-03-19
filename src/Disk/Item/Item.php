<?php
/**
 * Created by PhpStorm.
 * User: dnkol
 * Date: 13.01.2018
 * Time: 0:34
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Item;

use Leonied7\Yandex\Disk\Collection\PropertyCollection;
use Leonied7\Yandex\Disk\Exception\InvalidArgumentException;
use Leonied7\Yandex\Disk\Query\Data as QueryData;
use Leonied7\Yandex\Disk\Builder\Item as ItemBuilder;

/**
 * Class Item Главный класс для работы с директорией/файлом
 * @package Leonied7\Yandex\Disk\Item
 */
abstract class Item implements Entity
{
    const FILE = 'file';
    const DIRECTORY = 'directory';

    protected $path;
    /** @var string */
    protected $type;
    /** @var QueryData */
    protected $queryData;
    /** @var PropertyCollection */
    protected $properties;
    /** @var ItemBuilder */
    protected $builder;

    /**
     * Item constructor.
     * @param $path
     * @param QueryData $queryData
     * @param PropertyCollection|null $property
     * @throws InvalidArgumentException
     */
    public function __construct($path, QueryData $queryData, PropertyCollection $property = null)
    {
        if (empty($path)) {
            throw new InvalidArgumentException('path is required parameter');
        }

        $this->queryData = $queryData;
        $this->path = $this->getQueryData()->correctUrl($path);
        $this->builder = $this->createBuilder();

        if ($property === null) {
            $this->properties = new PropertyCollection();
        } else {
            $this->properties = $property;
        }
    }

    /** @return ItemBuilder */
    abstract protected function createBuilder();

    /**
     * @return ItemBuilder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * информация для запросов, содержит токет, url для запросов
     * @return QueryData
     */
    public function getQueryData()
    {
        return $this->queryData;
    }

    /**
     * возвращает текущее расположение элемента на Яндекс-диске
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * возвращает тип элемента
     * возможные значения (directory|file)
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * является ли элемент директорией
     * @return bool
     */
    public function isDirectory()
    {
        return $this->getType() === self::DIRECTORY;
    }

    /**
     * является ли элемент файлом
     * @return bool
     */
    public function isFile()
    {
        return $this->getType() === self::FILE;
    }

    /**
     * удаляет элемент с Яндекс-диска
     * @link https://tech.yandex.ru/disk/doc/dg/reference/delete-docpage/
     * @return bool
     * @throws InvalidArgumentException
     */
    public function delete()
    {
        return $this->getBuilder()->delete()->send()->isSuccess();
    }

    /**
     * Копирование элемента на Яндекс-диске
     * @link https://tech.yandex.ru/disk/doc/dg/reference/copy-docpage/
     *
     * @param string $destination - конечный путь на Яндекс-диске
     * @param bool $overwrite - флаг перезаписи
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function copy($destination, $overwrite = true)
    {
        return $this->getBuilder()->copy($destination, $overwrite)->send()->isSuccess();
    }

    /**
     * Перемещение/переименование элемента на Яндекс-диске
     * @link https://tech.yandex.ru/disk/doc/dg/reference/move-docpage/
     *
     * @param string $destination - конечный путь на Яндекс-диске
     * @param bool $overwrite - флаг перезаписи
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function move($destination, $overwrite = true)
    {
        $result = $this->getBuilder()->move($destination, $overwrite)->send();
        if ($result->isSuccess()) {
            $this->path = QueryData::correctUrl($destination);
        }

        return $result->isSuccess();
    }

    /**
     * загрузка свойств элемента с Яндекс-диска
     * @link https://tech.yandex.ru/disk/doc/dg/reference/propfind_property-request-docpage/
     *
     * @param PropertyCollection $property
     * @return PropertyCollection
     * @throws InvalidArgumentException
     */
    public function loadProperties(PropertyCollection $property)
    {
        $this->properties = new PropertyCollection();
        $result = $this->getBuilder()->loadProperties($property)->send();

        if (!$result->isSuccess()) {
            return $this->properties;
        }

        $this->properties = $result->getResult();

        return $this->properties;
    }

    /**
     * получение загруженных свойств
     * @return PropertyCollection
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * получение существующих свойств с Яндекс-диска (свойства приходят без значений)
     * @return PropertyCollection
     * @throws InvalidArgumentException
     */
    public function getExistProperties()
    {
        $properties = new PropertyCollection();

        $result = $this->getBuilder()->getExistProperties()->send();

        if (!$result->isSuccess()) {
            return $properties;
        }

        return $result->getResult();
    }

    /**
     * изменение свойств элемента на Яндекс-диске
     * @link https://tech.yandex.ru/disk/doc/dg/reference/proppatch-docpage/
     *
     * @param PropertyCollection $property
     * @return bool
     * @throws InvalidArgumentException
     */
    public function changeProperties(PropertyCollection $property)
    {
        $result = $this->getBuilder()->changeProperties($property)->send();
        return $result->isSuccess();
    }

    /**
     * сохранение свойств элемента, обёртка над changeProperties
     * !!!Внимание. неизменяемые свойства не сохраняются
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function saveProperties()
    {
        $collection = new PropertyCollection();

        foreach ($this->getProperties()->getChangeable() as $property) {
            $collection->offsetSet(null, $property);
        }

        return $this->changeProperties($collection);
    }

    /**
     * Публикация элемента
     * @link https://tech.yandex.ru/disk/doc/dg/reference/publish-docpage/
     * @return bool
     * @throws InvalidArgumentException
     */
    public function startPublish()
    {
        return $this->getBuilder()->startPublish()->send()->isSuccess();
    }

    /**
     * Снятие публикации элемента
     * @link https://tech.yandex.ru/disk/doc/dg/reference/publish-docpage/
     * @return bool
     * @throws InvalidArgumentException
     */
    public function stopPublish()
    {
        return $this->getBuilder()->stopPublish()->send()->isSuccess();
    }

    /**
     * Проверка публикации элемента
     * @link https://tech.yandex.ru/disk/doc/dg/reference/publish-docpage/
     * @return bool
     * @throws InvalidArgumentException
     */
    public function checkPublish()
    {
        return $this->getBuilder()->checkPublish()->send()->isSuccess();
    }

    /**
     * Проверяет существование элемента на Яндекс-диске
     * @param PropertyCollection|null $propertyCollection - выбираемые свойства для элемента
     * @return bool
     * @throws InvalidArgumentException
     */
    public function has(PropertyCollection $propertyCollection = null)
    {
        $refreshProperty = $propertyCollection !== null;

        $result = $this->getBuilder()->has($propertyCollection)->send();

        if ($result->isSuccess()) {
            $properties = $result->getResult();

            if (!empty($properties) && ($refreshProperty || count($this->getProperties()) === 0)) {
                $this->properties = $properties;
            }
        }

        return $result->isSuccess();
    }
}