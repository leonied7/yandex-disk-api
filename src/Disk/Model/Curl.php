<?php

namespace Leonied7\Yandex\Disk\Model;

use Leonied7\Yandex\Disk\Curl\Response as CurlResponse;
use Leonied7\Yandex\Disk\Entity\Result;
use Leonied7\Yandex\Disk\Query\Builder;
use Leonied7\Yandex\Disk\Stream\Loop;

/**
 * Class Curl используется для работы с cUrl
 * @package Leonied7\Yandex\Disk\Model
 */
abstract class Curl
{
    /** @var array - заголовки */
    protected $headers = [];
    /** @var string - тип запроса */
    protected $requestType;
    /** @var string - url запроса */
    protected $uri;
    /** @var array - GET-параметры */
    protected $queryParams = [];
    /** @var string - тело запроса */
    protected $body = '';
    /** @var callable - обработчик для установки дополнительных заголовков */
    protected $handler;
    /** @var Builder */
    protected $builder;
    /** @var resource - поток curl */
    protected $curl;
    /** @var Stream */
    protected $inFile;
    /** @var Stream */
    protected $file;
    /** @var bool - устанавливать ли диапозон */
    protected $range = false;
    /** @var int - начало диапозона данных для загрузки */
    protected $rangeFrom;
    /** @var int - конец диапозона данных для загрузки */
    protected $rangeTo;

    protected $exec = false;

    /**
     * Wrapper constructor.
     * @param string $method
     * @param string $uri
     * @param Builder $builder
     */
    public function __construct($method, $uri, Builder $builder)
    {
        $this->uri = $uri;
        $this->inFile = $this->file = new Loop();
        $this->builder = $builder;

        $this->init();
        $this->setRequestType($method);
    }

    /**
     * @param callable $headerHandler
     * @return $this
     */
    public function setHeadHandler(callable $headerHandler)
    {
        $this->handler = $headerHandler;
        return $this;
    }

    /**
     * установка типа запроса
     * @param string $requestType
     * @return $this
     */
    public function setRequestType($requestType)
    {
        $this->requestType = $requestType;
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $this->requestType);
        return $this;
    }

    /**
     * установка тела запроса
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * установка заголовков
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers = [])
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * установка заголовка
     * @param $name
     * @param $value
     * @return $this
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * установка get-параметров
     * @param array $queryParams
     * @return $this
     */
    public function setQueryParams(array $queryParams = [])
    {
        $this->queryParams = $queryParams;
        return $this;
    }

    /**
     * установка потока для скачивание
     * @param Stream $inFile
     * @return $this
     */
    public function setInFile(Stream $inFile = null)
    {
        $this->inFile = $inFile;
        return $this;
    }

    /**
     * установка потока для загрузки
     * @param Stream $file
     * @return $this
     */
    public function setFile(Stream $file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * начало диапозона данных для загрузки
     * @param int $rangeFrom
     * @return $this
     */
    public function setRangeFrom($rangeFrom = null)
    {
        $this->rangeFrom = $rangeFrom;
        $this->checkRange();
        return $this;
    }

    /**
     * конец диапозона данных для загрузки
     * @param $rangeTo
     * @return $this
     */
    public function setRangeTo($rangeTo = null)
    {
        $this->rangeTo = $rangeTo;
        $this->checkRange();
        return $this;
    }

    /**
     * выполнен ли запрос
     * @return bool
     */
    public function isExec()
    {
        return $this->exec;
    }

    /**
     * возращает заголовки
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * возвращает заголовок по имени
     * @param $name
     * @return mixed|null
     */
    public function getHeader($name)
    {
        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }
        return null;
    }

    /**
     * получение типа запроса
     * @return string
     */
    public function getRequestType()
    {
        return $this->requestType;
    }

    /**
     * возвращает поток для скачивания
     * @return Stream
     */
    public function getInFile()
    {
        return $this->inFile;
    }

    /**
     * возвращает поток для загрузки
     * @return Stream
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * исполнение curl
     * @return Result
     */
    abstract public function exec();


    /**
     * @return mixed
     */
    abstract protected function getResultBody();

    /**
     * инит curl
     */
    protected function init()
    {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->curl, CURLINFO_HEADER_OUT, true);
    }

    /**
     * @return Result
     * @throws \Leonied7\Yandex\Disk\Exception\InvalidArgumentException
     */
    protected function createResult()
    {
        $this->exec = true;
        $response = new CurlResponse(
            $this->getResultBody(),
            curl_getinfo($this->curl, CURLINFO_HTTP_CODE),
            curl_getinfo($this->curl, CURLINFO_HEADER_OUT),
            curl_getinfo($this->curl, CURLINFO_CONTENT_TYPE)
        );

        return $this->builder->prepareResponse($response);
    }

    protected function beforeExec()
    {
        $this->inFileActions();
        $this->fileActions();

        $this->execUserFunc();
        $this->buildHeaders();

        curl_setopt($this->curl, CURLOPT_URL, $this->getUrl());

        if (!empty($this->body)) {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->body);
        }

        if ($this->range) {
            curl_setopt($this->curl, CURLOPT_RANGE, "{$this->rangeFrom}-{$this->rangeTo}");
        }
    }

    protected function afterExec()
    {
        $this->inFile->close();
        $this->file->close();

        curl_close($this->curl);
    }

    /**
     * @throws \Leonied7\Yandex\Disk\Exception\InvalidArgumentException
     */
    protected function inFileActions()
    {
        $this->inFile->create();
        if ($stream = $this->inFile->getStream()) {
            curl_setopt($this->curl, CURLOPT_FILE, $stream);
        }
    }

    /**
     * @throws \Leonied7\Yandex\Disk\Exception\InvalidArgumentException
     */
    protected function fileActions()
    {
        $this->file->create();
        if ($stream = $this->file->getStream()) {
            curl_setopt($this->curl, CURLOPT_INFILE, $stream);
            curl_setopt($this->curl, CURLOPT_PUT, true);
        }
    }

    protected function checkRange()
    {
        $this->range = (null !== $this->rangeFrom) || (null !== $this->rangeTo);
    }

    protected function buildHeaders()
    {
        if (empty($this->headers)) {
            return;
        }
        $headers = [];
        foreach ($this->headers as $name => $value) {
            $headers[] = "{$name}: {$value}";
        }
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
    }

    /**
     * формирует строку запроса с GET-параметрами
     * @return string
     */
    protected function getUrl()
    {
        $url = $this->uri;
        if (empty($this->queryParams)) {
            return $url;
        }
        $url .= '?' . http_build_query($this->queryParams);
        return $url;
    }

    protected function execUserFunc()
    {
        if (!is_callable($this->handler)) {
            return;
        }
        $handler = $this->handler;
        $result = $handler($this);

        if (!is_array($result)) {
            return;
        }

        foreach ($result as $name => $value) {
            $this->setHeader($name, $value);
        }
    }
}