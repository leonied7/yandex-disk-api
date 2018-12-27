<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 15.01.2018
 * Time: 16:59
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Entity;

/**
 * Class Collection осуществляет работу с набором объектов
 * @package Leonied7\Yandex\Disk\Entity
 */
abstract class Collection implements \ArrayAccess, \Countable, \IteratorAggregate
{
    protected $collection = [];

    public function getIterator()
    {
        return new \ArrayIterator($this->collection);
    }

    public function offsetExists($offset) {
        return isset($this->collection[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->collection[$offset]) ? $this->collection[$offset] : null;
    }

    public function offsetSet($offset, $value) {
        if (null === $offset) {
            $this->collection[] = $value;
        } else {
            $this->collection[$offset] = $value;
        }
    }

    public function offsetUnset($offset) {
        unset($this->collection[$offset]);
    }

    public function count()
    {
        return count($this->collection);
    }
}