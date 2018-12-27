<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 16.01.2018
 * Time: 10:01
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Model;

//TODO: добавить alias для более удобного поиска по коллекции

/**
 * Class Property предстваляет свойство директории/файла
 * @package Leonied7\Yandex\Disk\Model
 */
abstract class Property
{
    const IMMUTABLE_NAMESPACES = [
        'dav' => 'DAV:',
        'urn' => 'urn:yandex:disk:meta'
    ];

    protected $name;
    protected $namespace;
    protected $value;

    protected $change = false;

    /**
     * Prop constructor.
     *
     * @param $name
     * @param $value
     * @param $namespace
     */
    public function __construct($name, $value = '', $namespace = '')
    {
        $this->name = $name;
        $this->value = $value;
        $this->namespace = $namespace;
    }

    /**
     * @return string
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
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return bool
     */
    public function canChanged()
    {
        return $this->change;
    }
}