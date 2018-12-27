<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 15.01.2018
 * Time: 16:59
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Collection;

use Leonied7\Yandex\Disk\Exception\InvalidArgumentException;
use Leonied7\Yandex\Disk\Model\Property;
use Leonied7\Yandex\Disk\Property\Mutable;

/**
 * Class PropertyCollection осуществляет работу с набором свойств
 * @package Leonied7\Yandex\Disk\Collection
 */
class PropertyCollection extends \Leonied7\Yandex\Disk\Entity\PropertyCollection
{
    /**
     * добавляет свойство в коллекцию
     * @param string $name
     * @param mixed $value
     * @param string $namespace
     * @return PropertyCollection
     * @throws InvalidArgumentException
     */
    public function add($name, $namespace = '', $value = '')
    {
        in_array(trim($namespace), Property::IMMUTABLE_NAMESPACES, true)
            ? $this->addImmutable($name, $namespace, $value)
            : $this->addMutable($name, $namespace, $value);
        return $this;
    }

    /**
     * получить список изменяемых свойств
     * @return Mutable[]
     */
    public function getChangeable()
    {
        $result = [];

        foreach ($this as $property) {
            if (!$property->canChanged()) {
                continue;
            }

            $result[] = $property;
        }
        return $result;
    }
}