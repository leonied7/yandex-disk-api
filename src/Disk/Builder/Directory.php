<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 16.01.2018
 * Time: 14:46
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Builder;


use Leonied7\Yandex\Disk\Body\Propfind;
use Leonied7\Yandex\Disk\Collection\PropertyCollection;
use Leonied7\Yandex\Disk\Query\Builder as QueryBuilder;
use Leonied7\Yandex\Disk\Exception\InvalidArgumentException;
use Leonied7\Yandex\Disk\Result\MkCol;
use Leonied7\Yandex\Disk\Result\Proppatch;

/**
 * Class Directory построитель запросов для директорий
 * @package Leonied7\Yandex\Disk\Builder
 */
class Directory extends Item
{
    /**
     * Создает директорию на Яндекс-диске
     * @link https://tech.yandex.ru/disk/doc/dg/reference/mkcol-docpage/
     * @return QueryBuilder
     * @throws InvalidArgumentException
     */
    public function create()
    {
        $builder = QueryBuilder::createByData($this->getQueryData(), $this->getPath());
        $builder->setMethod('MKCOL')->setExecHandler(MkCol::class);
        return $builder;
    }

    /**
     * Получить вложенные элементы с Яндекс-диска
     * @param PropertyCollection|null $propertyCollection
     * @param int $offset
     * @param null $amount
     * @return QueryBuilder
     * @throws InvalidArgumentException
     */
    public function getChildren(PropertyCollection $propertyCollection = null, $offset = 0, $amount = null)
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
            ->setExecHandler(Proppatch::class)
            ->addHeaders(['Depth' => '1'])
            ->setParams([
                'offset' => $offset,
                'amount' => $amount
            ])
            ->setBody($body);

        return $builder;
    }
}