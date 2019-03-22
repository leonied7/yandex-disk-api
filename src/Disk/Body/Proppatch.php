<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 10.04.2017
 * Time: 15:26
 */

namespace Leonied7\Yandex\Disk\Body;

use FluidXml\FluidXml;
use Leonied7\Yandex\Disk\Collection\PropertyCollection;
use Leonied7\Yandex\Disk\Model\Body;
use Leonied7\Yandex\Disk\Model\Property;

/**
 * отвечает за построение тела запроса для запросов типа PROPPATCH
 * Class Proppatch
 * @package Leonied7\Yandex\Disk\Body
 */
class Proppatch implements Body
{
    /** @var FluidXml объект xml */
    protected $xml;
    /** @var PropertyCollection коллекция свойств */
    protected $propertyCollection;

    public function __construct()
    {
        $this->propertyCollection = new PropertyCollection();
        $this->xml = new FluidXml('propertyupdate');
        $this->xml->setAttribute('xmlns', 'DAV:');
    }

    /**
     * @param PropertyCollection $propertyCollection
     * @return Proppatch
     */
    public function setPropertyCollection(PropertyCollection $propertyCollection)
    {
        $this->propertyCollection = $propertyCollection;
        return $this;
    }


    /**
     * формирование запроса
     * @return mixed|string
     * @throws \Exception
     */
    public function build()
    {
        $this->setNamespaces();
        list($setProperties, $removeProperties) = $this->prepareProps();
        $this->generateSet($setProperties);
        $this->generateRemove($removeProperties);

        return $this->xml->xml();
    }

    /**
     * установка namespaces
     * @throws \Exception
     */
    protected function setNamespaces()
    {
        foreach ($this->propertyCollection->getNamespaces() as $namespace) {
            $this->xml->namespace($namespace, $namespace);
        }

    }

    /**
     * распределяет свойства для установки/удаления
     * @return Property[][]
     */
    protected function prepareProps()
    {
        $setProperties = $removeProperties = [];
        foreach ($this->propertyCollection as $property) {
            if ($property->getValue()) {
                $setProperties[] = $property;
            } else {
                $removeProperties[] = $property;
            }
        }
        return [$setProperties, $removeProperties];
    }

    /**
     * генерирует свойства, которые нужно установить
     * @param Property[] $setProperties
     */
    protected function generateSet(array $setProperties = [])
    {
        if (empty($setProperties)) {
            return;
        }

        $set = $this->xml->addChild('set', true)->addChild('prop', true);

        foreach ($setProperties as $property) {
            $childValue = [
                '@' => $property->getValue()
            ];

            if ($property->getNamespace()) {
                $childValue['@xmlns'] = $property->getNamespace();
            }

            $set->addChild([
                $property->getName() => $childValue
            ]);
        }
    }

    /**
     * генерирует свойства, которые нужно удалить
     * @param Property[] $removeProperties
     */
    protected function generateRemove(array $removeProperties = [])
    {
        if (empty($removeProperties)) {
            return;
        }

        $remove = $this->xml->addChild('remove', true)->addChild('prop', true);

        foreach ($removeProperties as $property) {
            $childValue = [];
            if ($property->getNamespace()) {
                $childValue['@xmlns'] = $property->getNamespace();
            }

            $remove->addChild([
                $property->getName() => $childValue
            ]);
        }
    }
}