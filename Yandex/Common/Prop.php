<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 14.04.2017
 * Time: 13:40
 */

namespace Yandex\Common;

class Prop
{
    protected $name;
    protected $namespace;
    protected $value;

    /**
     * Prop constructor.
     *
     * @param array $arParams
     */
    function __construct($arParams = array())
    {
        $this->name = $arParams['name'];
        $this->namespace = $arParams['namespace'];
        $this->value = $arParams['value'];
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return array
     */
//    function getProps()
//    {
//        $result = array();
//
//        foreach($this->props as $namespace => $props)
//        {
//            foreach($props as $prop => $value)
//            {
//                $result[] = array(
//                    'name' => $prop,
//                    'namespace' => $namespace,
//                    'value' => $value
//                );
//            }
//        }
//
//        return $result;
//    }
}