<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 15.01.2018
 * Time: 13:35
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Result;


use Leonied7\Yandex\Disk\Entity\Result;

/**
 * Class Copy осуществляет работу с результатов ответа типа COPY
 * @package Leonied7\Yandex\Disk\Result
 */
class Copy extends Result
{
    /**
     * {@inheritdoc}
     */
    protected function getGoodCode()
    {
        return [
            201,
            202
        ];
    }
}