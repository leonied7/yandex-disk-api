<?php
/**
 * Created by PhpStorm.
 * User: Денис
 * Date: 20.12.2018
 * Time: 13:28
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Entity;

use Leonied7\Yandex\Disk\Item\Entity;

/**
 * Class Builder построитель запросов
 * @package Leonied7\Yandex\Disk\Entity
 */
abstract class Builder
{
    /** @var Entity */
    protected $item;

    /**
     * @param Entity $item
     */
    public function __construct(Entity $item)
    {
        $this->item = $item;
    }

    /**
     * @return \Leonied7\Yandex\Disk\Query\Data
     */
    public function getQueryData()
    {
        return $this->item->getQueryData();
    }
}