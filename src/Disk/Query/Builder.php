<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 11.04.2017
 * Time: 17:32
 */

namespace Leonied7\Yandex\Disk\Query;

use Leonied7\Yandex\Disk\Collection\ResultList;
use Leonied7\Yandex\Disk\Curl\Multi;
use Leonied7\Yandex\Disk\Curl\Response;
use Leonied7\Yandex\Disk\Curl\Single;
use Leonied7\Yandex\Disk\Exception\InvalidArgumentException;
use Leonied7\Yandex\Disk\Model\Body;
use Leonied7\Yandex\Disk\Model\Curl;
use Leonied7\Yandex\Disk\Model\Decorator;
use Leonied7\Yandex\Disk\Entity\Result;
use Leonied7\Yandex\Disk\Model\Stream;
use Leonied7\Yandex\Disk\Stream\Loop;
use Leonied7\Yandex\Disk\Query\Data as QueryData;

/**
 * Class Builder построитель запросов
 * @package Leonied7\Yandex\Disk\Query
 */
class Builder
{
    protected $method;
    protected $url;
    protected $headers = [];
    protected $params = [];
    /** @var callable */
    protected $handler;
    /** @var string */
    protected $execHandler = \Leonied7\Yandex\Disk\Result\Loop::class;
    /** @var Decorator */
    protected $resultDecorator;
    protected $body = '';
    /** @var Stream */
    protected $infile;
    /** @var Stream */
    protected $file;
    protected $range = [0, null];
    /** @var QueryData */
    protected $queryData;
    /** @var ResultList */
    protected $queryList;

    public function __construct(QueryData $queryData)
    {
        $this->queryData = $queryData;
        $this->queryList = ResultList::getInstance();
        $this->infile = $this->file = new Loop();
        $this->resultDecorator = new \Leonied7\Yandex\Disk\Decorator\Loop();
    }

    /**
     * @param QueryData $queryData
     * @param $path
     * @return static
     */
    public static function createByData(QueryData $queryData, $path)
    {
        $builder = new static($queryData);
        $builder
            ->setHeaders([
                'Authorization' => "OAuth {$queryData->getToken()}",
                'Accept' => '*/*'
            ])
            ->setUrl(QueryData::getPath($path));
        return $builder;
    }

    /**
     * установка метода
     * @param $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * установка пути
     * @param $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * установка заголовков
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers = [])
    {
        $this->headers = [];
        $this->addHeaders($headers);
        return $this;
    }

    /**
     * добавление заголовков
     * @param array $headers
     * @return $this
     */
    public function addHeaders(array $headers = [])
    {
        foreach ($headers as $name => $value) {
            $this->headers[$name] = $value;
        }
        return $this;
    }

    /**
     * установка GET параметров
     * @param array $params
     * @return $this
     */
    public function setParams(array $params = [])
    {
        $this->params = [];
        $this->addParams($params);
        return $this;
    }

    /**
     * добавление GET параметров
     * @param array $params
     * @return $this
     */
    public function addParams(array $params = [])
    {
        foreach ($params as $name => $value) {
            $this->params[$name] = $value;
        }
        return $this;
    }

    /**
     * установка обработчика, вызывается перед отправкой запроса,
     * должен вернуть массив с дополнительными заголовками для curl'а вида:
     * ключ свойства => значение
     *
     * @param callable $handler
     * @return $this
     */
    public function setHeaderHandler(callable $handler)
    {
        $this->handler = $handler;
        return $this;
    }

    /**
     * @param $className
     * @return Builder
     * @throws InvalidArgumentException
     */
    public function setExecHandler($className)
    {
        if (!is_subclass_of($className, Result::class)) {
            throw new InvalidArgumentException("{$className} not sub class of " . Result::class);
        }
        $this->execHandler = $className;
        return $this;
    }

    /**
     * @param Decorator $resultDecorator
     * @return Builder
     */
    public function setResultDecorator(Decorator $resultDecorator)
    {
        $this->resultDecorator = $resultDecorator;
        return $this;
    }

    /**
     * установка тела запроса
     * @param Body $body
     * @return $this
     */
    public function setBody(Body $body)
    {
        $this->body = $body->xml();
        return $this;
    }

    public function setInFile(Stream $stream)
    {
        $this->infile = $stream;
        return $this;
    }

    public function setFile(Stream $stream)
    {
        $this->file = $stream;
        return $this;
    }

    public function setRange($from = 0, $to = null)
    {
        $this->range = [
            $from,
            $to
        ];
        return $this;
    }

    /**
     * выполнение запроса
     * @return Result
     */
    public function exec()
    {
        $query = $this->createSingle();
        return $query->exec();
    }

    public function createSingle()
    {
        $curl = new Single($this->method, $this->url, $this);
        $this->setOptions($curl);
        if ($this->handler) {
            $curl->setHeadHandler($this->handler);
        }

        return $curl;
    }

    public function createMulti()
    {
        $curl = new Multi($this->method, $this->url, $this);
        $this->setOptions($curl);
        if ($this->handler) {
            $curl->setHeadHandler($this->handler);
        }

        return $curl;
    }

    /**
     * @param Response $response
     * @return Result
     * @throws InvalidArgumentException
     */
    public function prepareResponse(Response $response)
    {
        /** @var Result $result */
        $result = new $this->execHandler($response);
        $this->queryList->add($result);
        $result->setDecorator($this->resultDecorator);
        return $result;
    }

    private function setOptions(Curl $curl)
    {
        $curl->setHeaders($this->headers)
            ->setQueryParams($this->params)
            ->setBody($this->body)
            ->setRangeFrom($this->range[0])
            ->setRangeTo($this->range[1])
            ->setInFile($this->infile)
            ->setFile($this->file);
    }
}