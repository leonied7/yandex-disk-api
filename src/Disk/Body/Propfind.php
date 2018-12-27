<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 10.04.2017
 * Time: 14:03
 */

namespace Leonied7\Yandex\Disk\Body;

use FluidXml\FluidXml;
use Leonied7\Yandex\Disk\Collection\PropertyCollection;
use Leonied7\Yandex\Disk\Exception\Exception;
use Leonied7\Yandex\Disk\Model\Body;

/**
 * отвечает за построение тела запроса для запросов типа PROPFIND
 * Class Propfind -
 * @package Leonied7\Yandex\Disk\Body
 */
class Propfind implements Body
{
    /** @var FluidXml объект xml */
    protected $xml;
    /** @var string запроса */
    protected $method;
    /** @var PropertyCollection коллекция свойств */
    protected $props;

    public function __construct()
    {
        $this->props = new PropertyCollection();
        $this->xml = new FluidXml('propfind');
        $this->xml->setAttribute('xmlns', 'DAV:');
    }

    /**
     * установка коллекции свойств
     * @param PropertyCollection $prop
     * @return $this
     */
    public function setProps(PropertyCollection $prop)
    {
        $this->props = $prop;
        $this->get();
        return $this;
    }

    /**
     * установка метода - получить свойства и значения
     * @return $this
     */
    public function get()
    {
        $this->method = 'prop';
        return $this;
    }

    /**
     * установка метода - получить все свойства и значения
     * @return $this
     */
    public function getAll()
    {
        $this->method = 'allprop';
        return $this;
    }

    /**
     * установка метода - получить все свойства без значений
     * @return $this
     */
    public function getNames()
    {
        $this->method = 'propname';
        return $this;
    }

    /**
     * формирование запроса
     * @return string
     * @throws Exception
     */
    public function xml()
    {
        if(!$this->method) {
            throw new Exception('method is empty');
        }

        $xml = new FluidXml($this->xml->xml());

        $method = $xml->addChild($this->method, true);

        if($this->method === 'prop')
        {
            foreach($this->props as $prop)
            {
                $arProp = [];

                if($prop->getNamespace()) {
                    $arProp['@xmlns'] = $prop->getNamespace();
                }

                $method->addChild([
                    $prop->getName() => $arProp
                ]);
            }
        }

        return $xml->xml();
    }
}