<?php
/**
 * Created by PhpStorm.
 * User: dnkol
 * Date: 12.01.2018
 * Time: 23:49
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Query;


use Leonied7\Yandex\Disk\Exception\InvalidArgumentException;

/**
 * Class Data хранит информацию для запросов к API Yandex
 * @package Leonied7\Yandex\Disk\Query
 */
class Data
{
    protected $token;
    const URL = 'https://webdav.yandex.ru';

    /**
     * OAuth QueryData.
     * @param $token
     * @throws InvalidArgumentException
     */
    public function __construct($token)
    {
        if(empty($token)) {
            throw new InvalidArgumentException('token is required parameter');
        }

        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * корректирует путь
     * @param $path
     * @return string
     */
    public static function correctUrl($path)
    {
        $path = str_replace('\\', '/', $path);
        return '/' . trim($path, '/');
    }

    public static function getUrl($uri)
    {
        return static::URL . static::correctUrl($uri);
    }
}