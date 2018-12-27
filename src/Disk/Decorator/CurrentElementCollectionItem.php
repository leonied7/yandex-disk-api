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
use Leonied7\Yandex\Disk\Property\Immutable;

/**
 * Class CurrentElementCollectionItem используется для изменения формата ответа и получения одного свойства
 * одиночного элемента по пути
 * @package Leonied7\Yandex\Disk\Decorator
 */
class CurrentElementCollectionItem extends CurrentElementCollection
{
    /** @var \Leonied7\Yandex\Disk\Model\Property */
    protected $property;

    public function __construct($path, $propertyName, $propertyNamespace = '')
    {
        $this->property = new Immutable($propertyName, '', $propertyNamespace);
        parent::__construct($path);
    }

    /**
     * @param PropertyCollection|null $result
     * @return \Leonied7\Yandex\Disk\Model\Property|null
     */
    public function convert($result)
    {
        $result = parent::convert($result);
        if($result === null) {
            return $result;
        }
        /** @var PropertyCollection $result */
        return $result->find($this->property->getName(), $this->property->getNamespace());
    }
}