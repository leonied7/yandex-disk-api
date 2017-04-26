<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 10.04.2017
 * Time: 15:26
 */

namespace Yandex\Protocol\Method;

use FluidXml\FluidXml;
use Yandex\Common\Prop;
use Yandex\Common\PropPool;

class Proppatch implements Method
{
    protected $xml;
    /**
     * @var FluidXml
     */
    protected $finalXml;

    /**
     * @var PropPool
     */
    protected $props;

    protected $propsSet = array();
    protected $propsRemove = array();

    /*protected $namespaces = array();*/

    function __construct(PropPool $prop)
    {
        $this->xml = new FluidXml('propertyupdate');

        $this->xml->setAttribute('xmlns', 'DAV:');

        $this->setProps($prop);
    }


    public function setProps(PropPool $prop)
    {
        $this->props = $prop;
    }


    /**
     * формирование запроса
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function xml()
    {
        $this->propsSet = array();
        $this->propsRemove = array();
        $this->finalXml = new FluidXml($this->xml->xml());

        $this->setNamespaces();

        $this->prepareProps();

        $this->generateSet();

        $this->generateRemove();

        return $this->finalXml->xml();
    }

    /**
     * установка namespace
     *
     * @throws \Exception
     */
    protected function setNamespaces()
    {
        foreach($this->props->getNamespaces() as $namespace)
            $this->finalXml->namespace($namespace, $namespace);

    }

    protected function prepareProps()
    {
        foreach($this->props->getProps() as $prop)
        {
            if($prop->getValue())
                $this->propsSet[$prop->getNamespace()][$prop->getName()] = $prop->getValue();
            else
                $this->propsRemove[$prop->getNamespace()][$prop->getName()] = $prop->getName();
        }
    }

    /**
     * генерирует свойства, которые нужно установить
     */
    protected function generateSet()
    {
        if(!$this->propsSet)
            return;

        $set = $this->finalXml->addChild('set', true)->addChild('prop', true);

        foreach($this->propsSet as $namespace => $props)
        {
            foreach($props as $prop => $value)
            {
                $arProp = array(
                    '@' => $value
                );

                if($namespace)
                    $arProp['@xmlns'] = $namespace;

                $set->addChild(array(
                    $prop => $arProp
                ));
            }
        }
    }

    /**
     * генерирует свойства, которые нужно удалить
     */
    protected function generateRemove()
    {
        if(!$this->propsRemove)
            return;

        $remove = $this->finalXml->addChild('remove', true)->addChild('prop', true);

        foreach($this->propsRemove as $namespace => $props)
        {
            foreach($props as $prop)
            {
                $arProp = array();

                if($namespace)
                    $arProp['@xmlns'] = $namespace;

                $remove->addChild(array(
                    $prop => $arProp
                ));
            }
        }
    }
}