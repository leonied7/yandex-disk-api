<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 17.01.2018
 * Time: 13:07
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Decorator;


use Leonied7\Yandex\Disk\Model\Decorator;

/**
 * Class CurrentElement используется для изменения формата ответа и получения одиночного элемента по пути
 * @package Leonied7\Yandex\Disk\Decorator
 */
class CurrentElement implements Decorator
{
    protected $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * возвращает элемент по пути
     * @param mixed $result
     * @return mixed
     */
    public function convert($result)
    {
        if(isset($result[$this->getPath()])) {
            return $result[$this->getPath()];
        }
        $path = rtrim($this->getPath(), '/');
        if(isset($result[$path])) {
            return $result[$path];
        }
        return null;
    }
}