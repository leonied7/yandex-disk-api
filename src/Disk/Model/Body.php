<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 12.04.2017
 * Time: 11:18
 */

namespace Leonied7\Yandex\Disk\Model;

/**
 * Interface Body используется для построения тела запроса
 * @package Leonied7\Yandex\Disk\Model
 */
interface Body
{
    /**
     * формирование запроса
     * @return string
     */
    public function build();
}