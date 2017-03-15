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

    private $body;

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
                    curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);
                    break;
                case 'query':
                    $this->get = http_build_query($option);
                    break;
                case 'body':
                    $this->body = $option;
                    curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->body);
                    break;
                case 'infile':
                    $this->stream = $option;
                    curl_setopt($this->curl, CURLOPT_FILE, $this->stream);
                    break;
                case 'file':
                    $this->stream = $option;
                    curl_setopt($this->curl, CURLOPT_INFILE, $this->stream);
                    curl_setopt($this->curl, CURLOPT_PUT, true);
                    break;
                case 'range':
                    $range = "{$option[0]}-{$option[1]}";
                    curl_setopt($this->curl, CURLOPT_RANGE, $range);
                    break;
                default:
                    throw new \Exception("unknown option '{$optionName}'");
                    break;
            }
        }
    }

    function __construct($method, $uri, $options = [], $handler = null)
    {
        $this->uri = $uri;

        $this->curlInit();

        $this->setCustomRequest($method);

        $this->prepareOptions($options);

        $this->setUrl();

        if($handler)
            $this->execUserFunc($handler);
    }

    protected function setUrl()
    {
        curl_setopt($this->curl, CURLOPT_URL, $this->getUrl());
    }

    protected function execUserFunc(callable $handler)
    {
        $this->setOpt($handler());
    }

    /**
     * Установка дополнительных параметров для Curl
     *
     * @param array $arOptions
     *
     * @return $this
     */
    public function setOpt(array $arOptions = array())
    {
        foreach($arOptions as $key => $val)
        {
            curl_setopt($this->curl, $key, $val);
        }

        return $this;
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
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $this->customRequest);
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
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->curl, CURLINFO_HEADER_OUT, true);
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