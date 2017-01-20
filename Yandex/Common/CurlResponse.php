<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 20.01.2017
 * Time: 16:19
 */

namespace Yandex\Common;

class CurlResponse
{
    protected $data;

    function __construct($body, $code, $header)
    {
        $this->data['body'] = $body;
        $this->data['code'] = $code;
        $this->data['header'] = $header;
    }

    function getBody()
    {
        return $this->data['body'];
    }

    function getCode()
    {
        return $this->data['code'];
    }

    function getHeader()
    {
        return $this->data['header'];
    }

    function getData($key)
    {
        return $this->data[$key];
    }
}