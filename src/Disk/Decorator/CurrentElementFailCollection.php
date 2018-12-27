<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 17.01.2018
 * Time: 13:33
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Decorator;

use Leonied7\Yandex\Disk\Collection\PropertyFail;

/**
 * Class CurrentElementFailCollection используется для формата изменения ответа и получения "неудачных" свойств
 * одиночного элемента по пути
 * @package Leonied7\Yandex\Disk\Decorator
 */
class CurrentElementFailCollection extends CurrentElement
{
    /**
     * @param mixed $result
     * @return PropertyFail[]|null
     */
    public function convert($result)
    {
        $result = parent::convert($result);
        return empty($result) ? $result : $result['fail'];
    }
}