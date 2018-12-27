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
    protected $props;
    /** @var \Leonied7\Yandex\Disk\Model\Property[] */
    protected $propsSet = [];
    /** @var \Leonied7\Yandex\Disk\Model\Property[] */
    protected $propsRemove = [];

    public function __construct()
    {
        $this->props = new PropertyCollection();
        $this->xml = new FluidXml('propertyupdate');
        $this->xml->setAttribute('xmlns', 'DAV:');
    }

    /**
     * @param PropertyCollection $property
     * @return Proppatch
     */
    public function setProps(PropertyCollection $property)
    {
        $this->props = $property;
        return $this;
    }


    /**
     * формирование запроса
     * @return mixed|string
     * @throws \Exception
     */
    public function xml()
    {
        $this->setNamespaces();
        $this->prepareProps();
        $this->generateSet();
        $this->generateRemove();

        return $this->xml->xml();
    }

    /**
     * установка namespaces
     * @throws \Exception
     */
    protected function setNamespaces()
    {
        foreach ($this->props->getNamespaces() as $namespace) {
            $this->xml->namespace($namespace, $namespace);
        }

    }

    /**
     * перебирает свойтва, распределяет свойства для установки и удаления
     */
    protected function prepareProps()
    {
        foreach ($this->props as $prop) {
            if ($prop->getValue()) {
                $this->propsSet[] = $prop;
            } else {
                $this->propsRemove[] = $prop;
            }
        }
    }

    /**
     * генерирует свойства, которые нужно установить
     */
    protected function generateSet()
    {
        if (empty($this->propsSet)) {
            return;
        }

        $set = $this->xml->addChild('set', true)->addChild('prop', true);

        foreach ($this->propsSet as $prop) {
            $arProp = [
                '@' => $prop->getValue()
            ];

            if ($prop->getNamespace()) {
                $arProp['@xmlns'] = $prop->getNamespace();
            }

            $set->addChild([
                $prop->getName() => $arProp
            ]);
        }
    }

    /**
     * генерирует свойства, которые нужно удалить
     */
    protected function generateRemove()
    {
        if (empty($this->propsRemove)) {
            return;
        }

        $remove = $this->xml->addChild('remove', true)->addChild('prop', true);

        foreach ($this->propsRemove as $prop) {
            $arProp = [];

            if ($prop->getNamespace()) {
                $arProp['@xmlns'] = $prop->getNamespace();
            }

            $remove->addChild([
                $prop->getName() => $arProp
            ]);
        }
    }
}