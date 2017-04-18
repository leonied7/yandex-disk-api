<?php

namespace Yandex\Common\Response;

use Yandex\Common\CurlResponse;

/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 13.04.2017
 * Time: 17:29
 */
abstract class ResponseInterface
{
    /**
     * @var CurlResponse
     */
    protected $response;
    protected $data;

    /**
     * @var \DOMDocument
     */
    protected $dom;

    protected $goodAnswerCode = array(200);

    protected $throwOnError = true;

    function __construct()
    {}

    public function setResponse(CurlResponse $response)
    {
        $this->response = $response;

        $this->setData();

        return $this;
    }


    protected function setData()
    {
        $this->data = $this->getResponse()->getBodyResponse();
    }

    /**
     * @return mixed
     */
    abstract function prepare();

    protected function checkDom()
    {
        if($this->dom->hasChildNodes())
            return true;

        if($this->throwOnError)
            throw new \Exception($this->data, $this->getResponse()->getCode());

        return false;
    }

    protected function checkCode()
    {
        if(is_array($this->goodAnswerCode))
        {
            if(in_array($this->getResponse()->getCode(), $this->goodAnswerCode))
                return true;
        }
        else
        {
            if($this->getResponse()->getCode() === $this->goodAnswerCode)
                return true;
        }


        if($this->throwOnError)
            throw new \Exception($this->data, $this->getResponse()->getCode());

        return false;
    }

    public function getResponse()
    {
        return $this->response;
    }
}