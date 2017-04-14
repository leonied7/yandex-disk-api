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

class Proppatch implements Method
{
    protected $xml;

    /**
     * @var Prop
     */
    protected $props;

    protected $propsSet = array();
    protected $propsRemove = array();

    /*protected $namespaces = array();*/

    function __construct(Prop $prop)
    {
        $this->xml = new FluidXml('propertyupdate');

        $this->xml->setAttribute('xmlns', 'DAV:');

        $this->setProps($prop);
    }


    public function setProps(Prop $prop)
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
        $this->setNamespaces();

        $this->prepareProps();

        $this->generateSet();

        $this->generateRemove();

        return $this->xml->xml();
    }

    /**
     * установка namespace
     *
     * @throws \Exception
     */
    protected function setNamespaces()
    {
        foreach($this->props->getNamespaces() as $namespace)
            $this->xml->namespace($namespace, $namespace);

    }

    protected function prepareProps()
    {
        foreach($this->props->getProps() as $prop)
        {
            if($prop['value'])
                $this->propsSet[$prop['namespace']][$prop['name']] = $prop['value'];
            else
                $this->propsRemove[$prop['namespace']][$prop['name']] = $prop['name'];
        }
    }

    /**
     * генерирует свойства, которые нужно установить
     */
    protected function generateSet()
    {
        if(!$this->propsSet)
            return;

        $set = $this->xml->addChild('set', true)->addChild('prop', true);

        foreach($this->propsSet as $namespace => $props)
        {
            foreach($props as $prop => $value)
            {
                $set->addChild(array(
                    $prop => array(
                        '@' => $value,
                        '@xmlns' => $namespace
                    )
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

        $remove = $this->xml->addChild('remove', true)->addChild('prop', true);

        foreach($this->propsRemove as $namespace => $props)
        {
            foreach($props as $prop)
            {
                $remove->addChild(array(
                    $prop => array(
                        '@xmlns' => $namespace
                    )
                ));
            }
        }
    }
}