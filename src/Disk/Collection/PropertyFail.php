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
use Leonied7\Yandex\Disk\Property\Immutable;

/**
 * Class PropertyFail осуществляет работу с набором "неудачных" свойств
 * @package Leonied7\Yandex\Disk\Collection
 * @property Immutable [] $collection
 * @method Immutable[] getIterator()
 */
class PropertyFail extends \Leonied7\Yandex\Disk\Entity\PropertyCollection
{
    /** @var string */
    protected $status;

    public function __construct($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * добавляет свойство в коллекцию
     * @param $name
     * @param string $value
     * @param string $namespace
     * @return PropertyFail
     * @throws InvalidArgumentException
     */
    public function add($name, $namespace = '', $value = '')
    {
        $this->addImmutable($name, $namespace, $value);
        return $this;
    }
}