<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 13.03.2018
 * Time: 8:32
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Collection;


use Leonied7\Yandex\Disk\Exception\InvalidArgumentException;
use Leonied7\Yandex\Disk\Entity\Collection;
use Leonied7\Yandex\Disk\Entity\Result;

/**
 * Class ResultList хранит результаты ответов от Yandex.disk
 * @package Leonied7\Yandex\Disk\Collection
 */
class ResultList extends Collection
{
    /** @var ResultList */
    protected static $instance;

    protected function __construct()
    {
    }

    /**
     * Создает и возвращает объект класса
     * @return ResultList
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * сбрасывает объект класса
     */
    public static function reset()
    {
        static::$instance = null;
    }

    /**
     * добавляет элемент в коллекцию
     * @param Result $result
     * @return ResultList
     * @throws InvalidArgumentException
     */
    public function add(Result $result)
    {
        $this->offsetSet(null, $result);
        return $this;
    }

    /**
     * @return Result
     */
    public function getLast()
    {
        return end($this->collection);
    }

    /**
     * @param mixed $offset
     * @param Result $value
     * @throws InvalidArgumentException
     */
    public function offsetSet($offset, $value)
    {
        if (!$value instanceof Result) {
            throw new InvalidArgumentException('the value should be the heir of the "' . Result::class . '"');
        }

        parent::offsetSet($offset, $value);
    }
}