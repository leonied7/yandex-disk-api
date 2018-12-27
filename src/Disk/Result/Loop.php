<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 13.02.2018
 * Time: 14:12
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Result;


use Leonied7\Yandex\Disk\Entity\Result;

/**
 * Class Loop осуществляет работу с результатов ответа без типа, используется по умолчанию
 * @package Leonied7\Yandex\Disk\Result
 */
class Loop extends Result
{

    /**
     * должен возвращать список удовлетворяющих кодов ответов от диска
     * @return array
     */
    protected function getGoodCode()
    {
        return [];
    }

    public function isSuccess()
    {
        return true;
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