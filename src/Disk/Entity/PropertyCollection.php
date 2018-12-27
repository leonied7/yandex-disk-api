<?php
/**
 * Created by PhpStorm.
 * User: Денис
 * Date: 21.12.2018
 * Time: 9:05
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Entity;

use Leonied7\Yandex\Disk\Exception\InvalidArgumentException;
use Leonied7\Yandex\Disk\Model\Property;
use Leonied7\Yandex\Disk\Property\Immutable;
use Leonied7\Yandex\Disk\Property\Mutable;

/**
 * Class PropertyCollection осуществляет работу с набором свойств
 * @package Leonied7\Yandex\Disk\Entity
 * @property Property[] $collection
 * @method Property[] getIterator()
 */
abstract class PropertyCollection extends Collection
{
    /**
     * добавляет свойство в коллекцию
     * @param $name
     * @param string $value
     * @param string $namespace
     * @return $this
     */
    abstract public function add($name, $namespace = '', $value = '');

    /**
     * возвращает список всех namespace присутствующих в пуле
     * @return array
     */
    public function getNamespaces()
    {
        $result = [];
        foreach ($this as $property) {
            if (empty($property->getNamespace())) {
                continue;
            }

            $result[] = $property->getNamespace();
        }
        return $result;
    }

    /**
     * возвращает первое найденное свойство
     * @param string $name название свойства
     * @param string $namespace
     * @return null|Property
     */
    public function find($name, $namespace = '')
    {
        foreach ($this as $property) {
            if ($property->getName() !== $name) {
                continue;
            }
            if (!empty($namespace) && $property->getNamespace() !== $namespace) {
                continue;
            }
            return $property;
        }

        return null;
    }

    /**
     * @param mixed $offset
     * @param Property $value
     * @throws InvalidArgumentException
     */
    public function offsetSet($offset, $value)
    {
        if (!$value instanceof Property) {
            throw new InvalidArgumentException('the value should be the heir of the "' . Property::class . '"');
        }

        parent::offsetSet($offset, $value);
    }

    /**
     * добавляет изменяемое свойство в коллекцию
     * @param $name
     * @param string $value
     * @param string $namespace
     * @return PropertyCollection
     * @throws InvalidArgumentException
     */
    protected function addMutable($name, $namespace = '', $value = '')
    {
        $this->offsetSet(null, new Mutable($name, $value, $namespace));
        return $this;
    }

    /**
     * добавляет неизменяемое свойство в коллекцию
     * @param $name
     * @param string $namespace
     * @param string $value
     * @return PropertyCollection
     * @throws InvalidArgumentException
     */
    protected function addImmutable($name, $namespace = '', $value = '')
    {
        $this->offsetSet(null, new Immutable($name, $value, $namespace));
        return $this;
    }
}