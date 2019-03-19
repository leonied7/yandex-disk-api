<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 10.04.2017
 * Time: 14:03
 */

namespace Leonied7\Yandex\Disk\Body;

use FluidXml\FluidContext;
use FluidXml\FluidXml;
use Leonied7\Yandex\Disk\Collection\PropertyCollection;
use Leonied7\Yandex\Disk\Model\Body;

/**
 * отвечает за построение тела запроса для запросов типа PROPFIND
 * Class Propfind -
 * @package Leonied7\Yandex\Disk\Body
 */
class Propfind implements Body
{
    const METHOD_PROPERTIES = 'prop';
    const METHOD_ALL_PROPERTIES = 'allprop';
    const METHOD_PROPERTIES_NAME = 'propname';


    /** @var FluidXml объект xml */
    protected $xml;
    /** @var string запроса */
    protected $method = self::METHOD_ALL_PROPERTIES;
    /** @var PropertyCollection коллекция свойств */
    protected $propertyCollection;

    public function __construct()
    {
        $this->propertyCollection = new PropertyCollection();
        $this->xml = new FluidXml('propfind');
        $this->xml->setAttribute('xmlns', 'DAV:');
    }

    /**
     * установка коллекции свойств
     * @param PropertyCollection $propertyCollection
     * @return $this
     */
    public function setPropertyCollection(PropertyCollection $propertyCollection)
    {
        $this->propertyCollection = $propertyCollection;
        $this->setPropertiesMethod();
        return $this;
    }

    /**
     * установка метода - получить свойства и значения
     * @return $this
     */
    public function setPropertiesMethod()
    {
        $this->method = self::METHOD_PROPERTIES;
        return $this;
    }

    /**
     * установка метода - получить все свойства и значения
     * @return $this
     */
    public function setPropertiesAllMethod()
    {
        $this->method = self::METHOD_ALL_PROPERTIES;
        return $this;
    }

    /**
     * установка метода - получить все свойства без значений
     * @return $this
     */
    public function setPropertiesNameMethod()
    {
        $this->method = self::METHOD_PROPERTIES_NAME;
        return $this;
    }

    /**
     * формирование запроса
     * @return string
     */
    public function build()
    {
        $xml = new FluidXml($this->xml->xml());
        $method = $xml->addChild($this->method, true);

        if($this->method === self::METHOD_PROPERTIES)
        {
            $this->buildPropertiesXml($method);
        }

        return $xml->xml();
    }

    private function buildPropertiesXml(FluidContext $xml)
    {
        foreach($this->propertyCollection as $property)
        {
            $childValue = [];
            if($property->getNamespace()) {
                $childValue['@xmlns'] = $property->getNamespace();
            }

            $xml->addChild([
                $property->getName() => $childValue
            ]);
        }
    }
}