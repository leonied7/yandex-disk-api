<?php
/**
 * Created by PhpStorm.
 * User: dnkol
 * Date: 16.01.2018
 * Time: 18:43
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Property;

use Leonied7\Yandex\Disk\Model\Property;

/**
 * Class Mutable предстваляет изменяемое свойство директории/файла
 * @package Leonied7\Yandex\Disk\Property
 */
class Mutable extends Property
{
    public function __construct($name, $value, $namespace)
    {
        $this->change = true;
        parent::__construct($name, $value, $namespace);
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
}