<?php
/**
 * Created by PhpStorm.
 * User: Денис
 * Date: 20.12.2018
 * Time: 14:42
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Item;

use Leonied7\Yandex\Disk\Entity\Builder;

/**
 * Interface Entity Главный класс для работы с сущьностями
 * @package Leonied7\Yandex\Disk\Item
 */
interface Entity
{
    /**
     * @return \Leonied7\Yandex\Disk\Query\Data
     */
    public function getQueryData();

    /**
     * @return Builder построитель запросов, может быть использован для мультизапросов
     */
    public function getBuilder();
}