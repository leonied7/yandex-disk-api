<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 20.01.2017
 * Time: 16:19
 */

namespace Yandex\Common;

use Yandex\Common\Response\ResponseInterface;

class CurlResponse
{
    protected $data;

    /**
     * @var ResponseInterface
     */
    protected $handler;

    function __construct($body, $code, $header, $handler)
    {
        $this->data['bodyResponse'] = $this->data['body'] = $body;
        $this->data['code'] = $code;
        $this->data['header'] = $header;

        $this->setHandler($handler);

        $this->prepareResponse();
    }

    function setHandler($handler)
    {
        $this->handler = $handler;

        return $this;
    }

    function getBody()
    {
        return $this->data['body'];
    }

    function getBodyResponse()
    {
        return $this->data['bodyResponse'];
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

    protected function prepareResponse()
    {
        if(!$this->handler instanceof ResponseInterface)
            return;

        $this->data['body'] = $this->handler->setData($this->getBodyResponse())->prepare();
    }
}