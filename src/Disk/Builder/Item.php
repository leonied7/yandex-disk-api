<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 14.02.2018
 * Time: 9:23
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Builder;

use Leonied7\Yandex\Disk\Body\Proppatch;
use Leonied7\Yandex\Disk\Body\Propfind;
use Leonied7\Yandex\Disk\Model\Property;
use Leonied7\Yandex\Disk\Query\Builder as QueryBuilder;
use Leonied7\Yandex\Disk\Exception\InvalidArgumentException;
use Leonied7\Yandex\Disk\Collection\PropertyCollection;
use Leonied7\Yandex\Disk\Decorator\CurrentElementCollection;
use Leonied7\Yandex\Disk\Decorator\CurrentElementCollectionItemValue;
use Leonied7\Yandex\Disk\Result\Copy;
use Leonied7\Yandex\Disk\Result\Delete;
use Leonied7\Yandex\Disk\Result\Move;
use Leonied7\Yandex\Disk\Item\Item as ItemElement;

/**
 * Class Item построитель запросов для директорий/файлов
 * @package Leonied7\Yandex\Disk\Builder
 * @property ItemElement item
 */
abstract class Item extends \Leonied7\Yandex\Disk\Entity\Builder
{
    /**
     * возвращает текущее расположение элемента на Яндекс-диске
     * @return string
     */
    public function getPath()
    {
        return $this->item->getPath();
    }

    /**
     * Проверяет существование элемента на Яндекс-диске
     * @param PropertyCollection|null $propertyCollection
     * @return QueryBuilder
     * @throws InvalidArgumentException
     */
    public function has(PropertyCollection $propertyCollection = null)
    {
        $builder = QueryBuilder::createByData($this->getQueryData(), $this->getPath());
        $refreshProperty = $propertyCollection !== null;

        $body = new Propfind();
        if ($refreshProperty) {
            $body->setPropertyCollection($propertyCollection);
        } else {
            $body->setPropertiesAllMethod();
        }

        $builder
            ->setMethod('PROPFIND')
            ->addHeaders(['Depth' => 0])
            ->setExecHandler(\Leonied7\Yandex\Disk\Result\Propfind::class)
            ->setResultDecorator(new CurrentElementCollection($this->getPath()))
            ->setBody($body);
        return $builder;
    }

    /**
     * удаляет элемент с Яндекс-диска
     * @link https://tech.yandex.ru/disk/doc/dg/reference/delete-docpage/
     * @return QueryBuilder
     * @throws InvalidArgumentException
     */
    public function delete()
    {
        $builder = QueryBuilder::createByData($this->getQueryData(), $this->getPath());
        $builder
            ->setExecHandler(Delete::class)
            ->setMethod('DELETE');
        return $builder;
    }

    /**
     * @param $destination
     * @param bool $overwrite
     * @return QueryBuilder
     * @throws InvalidArgumentException
     */
    public function copy($destination, $overwrite = true)
    {
        if (empty($destination)) {
            throw new InvalidArgumentException('destination is required parameter');
        }

        $builder = QueryBuilder::createByData($this->getQueryData(), $this->getPath());
        $builder
            ->setMethod('COPY')
            ->setExecHandler(Copy::class)
            ->addHeaders([
                'Destination' => $this->getQueryData()->correctUrl($destination),
                'Overwrite' => $overwrite ? 'T' : 'F'
            ]);
        return $builder;
    }

    /**
     * Копирование элемента на Яндекс-диске
     * @link https://tech.yandex.ru/disk/doc/dg/reference/copy-docpage/
     *
     * @param string $destination - конечный путь на Яндекс-диске
     * @param bool $overwrite - флаг перезаписи
     * @return QueryBuilder
     * @throws InvalidArgumentException
     */
    public function move($destination, $overwrite = true)
    {
        if (empty($destination)) {
            throw new InvalidArgumentException('destination is required parameter');
        }

        $builder = QueryBuilder::createByData($this->getQueryData(), $this->getPath());
        $builder
            ->setMethod('MOVE')
            ->setExecHandler(Move::class)
            ->addHeaders([
                'Destination' => $this->getQueryData()->correctUrl($destination),
                'Overwrite' => $overwrite ? 'T' : 'F'
            ]);
        return $builder;
    }

    /**
     * загрузка свойств элемента с Яндекс-диска
     * @link https://tech.yandex.ru/disk/doc/dg/reference/propfind_property-request-docpage/
     * @param PropertyCollection $property
     * @return QueryBuilder
     * @throws InvalidArgumentException
     */
    public function loadProperties(PropertyCollection $property)
    {
        $body = new Propfind();
        $body->setPropertyCollection($property);

        $builder = QueryBuilder::createByData($this->getQueryData(), $this->getPath());

        $builder
            ->setMethod('PROPFIND')
            ->setExecHandler(\Leonied7\Yandex\Disk\Result\Propfind::class)
            ->setResultDecorator(new CurrentElementCollection($this->getPath()))
            ->addHeaders(['Depth' => 0])
            ->setBody($body);

        return $builder;
    }

    /**
     * получение существующих свойств с Яндекс-диска (свойства приходят без значений)
     * @return QueryBuilder
     * @throws InvalidArgumentException
     */
    public function getExistProperties()
    {
        $body = new Propfind();
        $body->setPropertiesNameMethod();

        $builder = QueryBuilder::createByData($this->getQueryData(), $this->getPath());

        $builder
            ->setMethod('PROPFIND')
            ->setExecHandler(\Leonied7\Yandex\Disk\Result\Propfind::class)
            ->setResultDecorator(new CurrentElementCollection($this->getPath()))
            ->addHeaders(['Depth' => 0])
            ->setBody($body);

        return $builder;
    }

    /**
     * изменение свойств элемента на Яндекс-диске
     * @link https://tech.yandex.ru/disk/doc/dg/reference/proppatch-docpage/
     * @param PropertyCollection $property
     * @return QueryBuilder
     * @throws InvalidArgumentException
     */
    public function changeProperties(PropertyCollection $property)
    {
        $body = new Proppatch();
        $body->setPropertyCollection($property);

        $builder = QueryBuilder::createByData($this->getQueryData(), $this->getPath());
        $builder
            ->setMethod('PROPPATCH')
            ->setExecHandler(\Leonied7\Yandex\Disk\Result\Proppatch::class)
            ->setResultDecorator(new CurrentElementCollection($this->getPath()))
            ->setBody($body);

        return $builder;
    }

    /**
     * Публикация элемента
     * @link https://tech.yandex.ru/disk/doc/dg/reference/publish-docpage/
     * @return QueryBuilder
     * @throws InvalidArgumentException
     */
    public function startPublish()
    {
        $collection = new PropertyCollection();
        $collection->add('public_url', Property::IMMUTABLE_NAMESPACES['urn'], true);

        $builder = $this->changeProperties($collection);
        $builder->setResultDecorator(new CurrentElementCollectionItemValue($this->getPath(), 'public_url', 'urn:yandex:disk:meta'));
        return $builder;
    }

    /**
     * Снятие публикации элемента
     * @link https://tech.yandex.ru/disk/doc/dg/reference/publish-docpage/
     * @return QueryBuilder
     * @throws InvalidArgumentException
     */
    public function stopPublish()
    {
        $collection = new PropertyCollection();
        $collection->add('public_url', Property::IMMUTABLE_NAMESPACES['urn']);

        $builder = $this->changeProperties($collection);
        $builder->setResultDecorator(new CurrentElementCollectionItemValue($this->getPath(), 'public_url', 'urn:yandex:disk:meta'));
        return $builder;
    }

    /**
     * Проверка публикации элемента
     * @link https://tech.yandex.ru/disk/doc/dg/reference/publish-docpage/
     * @return QueryBuilder
     * @throws InvalidArgumentException
     */
    public function checkPublish()
    {
        $collection = new PropertyCollection();
        $collection->add('public_url', Property::IMMUTABLE_NAMESPACES['urn']);

        $builder = $this->loadProperties($collection);
        $builder->setResultDecorator(new CurrentElementCollectionItemValue($this->getPath(), 'public_url', 'urn:yandex:disk:meta'));
        return $builder;
    }
}