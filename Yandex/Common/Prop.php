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
    protected $props = array();

    /**
     * @param string|array $propName
     * @param mixed $value
     * @param string $namespace
     */
    function __construct($propName = null, $namespace = '', $value = null)
    {
        if($propName)
            $this->setProps($propName, $namespace, $value);
    }

    /**
     * @param string|array $propName
     *
     * Example:
     * array(
     *      'name'
     *      'value'
     * )
     *
     * @param mixed $value
     * @param string $namespace
     */
    function setProps($propName, $namespace = '', $value = null)
    {
        if(!is_array($propName))
            $propName = array(
                'name' => $propName,
                'value' => $value
            );


        if(isset($propName['name']))
            $propName = array($propName);

        foreach($propName as &$prop)
        {
            if(!is_array($prop))
            {
                $prop = array(
                    'name' => $prop
                );
            }

            if(!$prop['name'])
                continue;

            $this->props[$namespace][$prop['name']] = $prop['value'];
        }
    }

    /**
     * @return array
     */
    function getProps()
    {
        $result = array();

        foreach($this->props as $namespace => $props)
        {
            foreach($props as $prop => $value)
            {
                $result[] = array(
                    'name' => $prop,
                    'namespace' => $namespace,
                    'value' => $value
                );
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    function getNamespaces()
    {
        return array_keys($this->props);
    }
}