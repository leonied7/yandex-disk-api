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

    function __construct($body, $code, $header, $type, $handler)
    {
        $this->data['bodyResponse'] = $this->data['body'] = $body;
        $this->data['code'] = $code;
        $this->data['header'] = $header;
        $this->data['contentType'] = $type;

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
        return $this->getData('body');
    }

    function getBodyResponse()
    {
        return $this->getData('bodyResponse');
    }

    function getCode()
    {
        return $this->getData('code');
    }

    function getHeader()
    {
        return $this->getData('header');
    }

    function getType()
    {
        return $this->getData('contentType');
    }

    function getData($key)
    {
        return $this->data[$key];
    }

    protected function prepareResponse()
    {
        if(!$this->handler instanceof ResponseInterface)
            return;

        $this->data['body'] = $this->handler->setResponse($this)->prepare();
    }
}