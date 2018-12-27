<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 17.01.2018
 * Time: 13:33
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Decorator;

use Leonied7\Yandex\Disk\Collection\PropertyCollection;

/**
 * Class CurrentElementCollection используется для изменения формата ответа и получения свойств одиночного элемента по пути
 * @package Leonied7\Yandex\Disk\Decorator
 */
class CurrentElementCollection extends CurrentElement
{
    /**
     * @param mixed $result
     * @return PropertyCollection|null
     */
    public function convert($result)
    {
        $result = parent::convert($result);
        return empty($result) ? $result : $result['apply'];
    }
}