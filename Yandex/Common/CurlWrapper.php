<?php

namespace Yandex\Common;
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 20.01.2017
 * Time: 15:16
 */
class CurlWrapper
{
    /**
     * заголовок curl
     * @var array
     */
    private $headers = [];
    /**
     * запрос
     * @var
     */
    private $customRequest;
    /**
     * думаю можно не объяснять
     * @var string
     */
    private $uri;
    /**
     * GET-параметры
     * @var string
     */
    private $get;

    /**
     * результат curl
     * @var string
     */
    protected $result;
    /**
     * поток curl
     * @var resource
     */
    protected $curl;
    /**
     * поток файла
     * @var resource
     */
    protected $stream;

    protected function prepareOptions($options = [])
    {
        foreach($options as $optionName => $option)
        {
            switch($optionName)
            {
                case 'headers':
                    foreach($option as $key => $value)
                    {
                        $this->headers[] = "{$key}: {$value}";
                    }
                    break;
                case 'query':
                    $this->get = http_build_query($option);
                    break;
                default:
                    throw new \Exception("unknown option '{$optionName}'");
                    break;
            }
        }
    }

    function __construct($method, $uri, $options = [])
    {
        $this->setCustomRequest($method);
        $this->uri = $uri;
        $this->prepareOptions($options);

        $this->curlInit();
    }

    /**
     * формирует строку запроса с GET-параметрами
     * @return string
     */
    protected function getUrl()
    {
        $url = $this->uri;

        $url .= $this->get ? "?{$this->get}" : '';

        return $url;
    }

    /**
     * @deprecated
     * @param array $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        //curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);
    }

    /**
     * @param mixed $customRequest
     */
    public function setCustomRequest($customRequest)
    {
        $this->customRequest = $customRequest;
        //curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $this->customRequest);
    }

    /**
     * @param mixed $stream
     */
    public function setStream($stream)
    {
        $this->stream = $stream;
        curl_setopt($this->curl, CURLOPT_INFILE, $this->stream);
    }

    /**
     * инит curl
     */
    protected function curlInit()
    {
        $this->curl = curl_init($this->getUrl());
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->curl, CURLINFO_HEADER_OUT, true);

        if($this->customRequest)
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $this->customRequest);

        if($this->headers)
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);
    }

    /**
     * исполнение curl
     */
    public function exec()
    {
        $body = curl_exec($this->curl);
        $response = new CurlResponse(
            $body,
            curl_getinfo($this->curl, CURLINFO_HTTP_CODE),
            curl_getinfo($this->curl, CURLINFO_HEADER_OUT)
        );

        if($this->stream)
            fclose($this->stream);

        curl_close($this->curl);

        return $response;
    }
}