<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 17.01.2018
 * Time: 13:03
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Model;

/**
 * Interface Decorator используется для изменения формата ответ
 * @package Leonied7\Yandex\Disk\Model
 */
interface Decorator
{
    /**
     * конвертирует результат
     * @param mixed $result
     * @return mixed
     */
    public function convert($result);
}