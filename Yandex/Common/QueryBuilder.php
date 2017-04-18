<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 11.04.2017
 * Time: 17:32
 */

namespace Yandex\Common;

use Yandex\Common\Response\ResponseInterface;
use Yandex\Protocol\Method\Method;
use Yandex\Protocol\Webdav;

class QueryBuilder
{
    protected $bodyBuilder;

    protected $method;
    protected $url;
    protected $headers = array();
    protected $params = array();
    protected $handler;
    protected $body = "";
    /**
     * @var resource
     */
    protected $infile;
    /**
     * @var resource
     */
    protected $file;
    protected $range = array();

    protected $responseHandler;

    function __construct(Webdav $bodyBuilder)
    {
        $this->bodyBuilder = $bodyBuilder;
    }

    /**
     * установка метода
     *
     * @param $method
     *
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * установка пути
     *
     * @param $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * установка заголовков
     *
     * @param array $headers
     *
     * @return $this
     */
    public function setHeaders($headers = array())
    {
        foreach($headers as $name => $value)
            $this->headers[$name] = $value;

        return $this;
    }

    /**
     * установка GET параметров
     *
     * @param array $params
     *
     * @return $this
     */
    public function setParams($params = array())
    {
        foreach($params as $name => $value)
            $this->params[$name] = $value;

        return $this;
    }

    /**
     * установка обработчика, вызывается перед отправкой запроса,
     * должен вернуть массив с доп параметрами для curl'а вида:
     *
     * ключ свойства => значение
     *
     * @param callable $handler
     */
    public function setHandler(callable $handler)
    {
        $this->handler = $handler;
    }

    /**
     * установка тела запроса
     *
     * @param Method $body
     *
     * @return $this
     */
    public function setBody(Method $body)
    {
        $this->body = $body->xml();

        return $this;
    }

    /**
     * установка обработчика ответа
     *
     * @param \ResponseInterface $handler
     *
     * @return $this
     */
    public function setResponseHandler(ResponseInterface $handler)
    {
        $this->responseHandler = $handler;

        return $this;
    }

    public function setInFile($stream)
    {
        $this->infile = $stream;

        return $this;
    }

    public function setFile($stream)
    {
        $this->file = $stream;

        return $this;
    }

    public function setRange($range = array())
    {
        $this->range = $range;

        return $this;
    }

    /**
     * выполнение запроса
     *
     * @return CurlResponse
     */
    public function exec()
    {
        $params = $this->prepareParams();

        $query = new CurlWrapper($this->method, $this->url, $params, $this->handler);

        if($this->responseHandler)
            $query->setResponseHandler($this->responseHandler);

        return $query->exec();
    }

    protected function prepareParams()
    {
        $result = array();

        $result['headers'] = $this->headers;

        $result['query'] = $this->params;

        $result['body'] = $this->body;

        $result['infile'] = $this->infile;

        $result['file'] = $this->file;

        $result['range'] = $this->range;

        $result = array_filter($result);

        return $result;
    }
}