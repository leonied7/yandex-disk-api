<?php
/**
 * Created by PhpStorm.
 * User: dnkol
 * Date: 14.01.2018
 * Time: 20:08
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Builder;

use Leonied7\Yandex\Disk\Entity\Builder;
use Leonied7\Yandex\Disk\Decorator\ExplodeData;
use Leonied7\Yandex\Disk\Model\Property;
use Leonied7\Yandex\Disk\Query\Builder as QueryBuilder;
use Leonied7\Yandex\Disk\Collection\PropertyCollection;
use Leonied7\Yandex\Disk\Exception\InvalidArgumentException;
use Leonied7\Yandex\Disk\Result\Get;

/**
 * Class Disk построитель запросов для диска
 * @package Leonied7\Yandex\Disk\Builder
 */
class Disk extends Builder
{
    /**
     * получение свободного/занятого места
     * @link https://tech.yandex.ru/disk/doc/dg/reference/space-request-docpage/
     * @return QueryBuilder
     * @throws InvalidArgumentException
     */
    public function spaceInfo()
    {
        $property = new PropertyCollection();
        $property
            ->add('quota-available-bytes', Property::IMMUTABLE_NAMESPACES['dav'])
            ->add('quota-used-bytes', Property::IMMUTABLE_NAMESPACES['dav']);

        $directory = new \Leonied7\Yandex\Disk\Item\Directory('/', $this->getQueryData());
        return $directory->getBuilder()->loadProperties($property);
    }

    /**
     * Возвращает информацию о пользователе
     * @link https://tech.yandex.ru/disk/doc/dg/reference/userinfo-docpage/
     * @return QueryBuilder
     * @throws InvalidArgumentException
     */
    public function getInfo()
    {
        $builder = QueryBuilder::createByData($this->getQueryData(), '/');

        $builder
            ->setMethod('GET')
            ->setParams([
                'userinfo' => ''
            ])
            ->setExecHandler(Get::class)
            ->setResultDecorator(new ExplodeData());

        return $builder;
    }
}