<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 17.04.2017
 * Time: 13:45
 */

namespace Yandex\Common;

class PropPool
{
    /**
     * @var Prop[]
     */
    protected $props = array();

    /**
     * @param array ...$params
     *
     * set(new Prop)
     * set(name, namespace, value)
     * set(array(name1, name2), namespace, value)
     *
     *
     */
    function __construct(...$params)
    {
        $this->set(...$params);
    }

    /**
     * @param array ...$params
     *
     * set(new Prop)
     * set(name, namespace, value)
     * set(array(name1, name2), namespace, value)
     *
     *
     * @return $this
     */
    public function set(...$params)
    {
        $array = $params[0];

        //если уже является объектом
        if($array instanceof Prop)
        {
            $this->props[] = $array;
            return $this;
        }

        //если это строка, делаем массив
        if(!is_array($array))
        {
            $array = array(
                'name' => $params[0],
                'namespace' => $params[1],
                'value' => $params[2]
            );
        }

        //если присутствует ключ name значит надо обернуть ещё в массив
        if(isset($array['name']))
            $array = array($array);


        foreach($array as &$prop)
        {
            //если массив объектов Prop
            if($prop instanceof Prop)
            {
                $this->props[] = $prop;
                continue;
            }

            //если это строка, то делаем массив
            if(!is_array($prop))
            {
                $prop = array(
                    'name' => $prop
                );
            }

            if(!isset($prop['namespace']) && isset($params[1]))
                $prop['namespace'] = $params[1];

            if(!isset($prop['value']) && isset($params[2]))
                $prop['value'] = $params[2];

            $this->props[] = new Prop($prop);
        }

        return $this;
    }

    function getProps()
    {
        return $this->props;
    }

    /**
     * @return array
     */
    function getNamespaces()
    {
        $result = array();

        foreach($this->props as $prop)
        {
            if(!$prop->getNamespace())
                continue;

            $result[] = $prop->getNamespace();
        }

        return $result;
    }
}