<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 18.04.2017
 * Time: 11:56
 */

namespace Leonied7\Yandex\Disk\Result;

use Leonied7\Yandex\Disk\Entity\Result;

/**
 * Class MkCol осуществляет работу с результатов ответа типа MkCol
 * @package Leonied7\Yandex\Disk\Result
 */
class MkCol extends Result
{
    /**
     * должен возвращать список удовлетворяющих кодов ответов от диска
     * @return array
     */
    protected function getGoodCode()
    {
        return [
            201
        ];
    }

    /**
     * вызывается только если тип ответа xml формата
     * @return mixed - возвращаемое значение попадёт в prepare
     */
    protected function prepareDom()
    {
        return null;
    }
}