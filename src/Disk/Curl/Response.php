<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 20.01.2017
 * Time: 16:19
 */

namespace Leonied7\Yandex\Disk\Curl;

/**
 * Class Response содержит ответ cUrl
 * @package Leonied7\Yandex\Disk\Curl
 */
class Response
{
    protected $data;

    public function __construct($body, $code, $header, $type)
    {
        $this->data['body'] = $body;
        $this->data['code'] = $code;
        $this->data['header'] = $header;
        $this->data['contentType'] = $type;
    }

    public function getBody()
    {
        return $this->getData('body');
    }

    public function getCode()
    {
        return $this->getData('code');
    }

    public function getHeader()
    {
        return $this->getData('header');
    }

    public function getType()
    {
        return $this->getData('contentType');
    }

    public function getData($key)
    {
        return $this->data[$key];
    }
}