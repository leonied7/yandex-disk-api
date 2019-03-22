<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 11.04.2017
 * Time: 17:32
 */

namespace Leonied7\Yandex\Disk\Query;

use Leonied7\Yandex\Disk\Collection\ResultList;
use Leonied7\Yandex\Disk\Exception\InvalidArgumentException;
use Leonied7\Yandex\Disk\Http\Transport;
use Leonied7\Yandex\Disk\Http\Request;
use Leonied7\Yandex\Disk\Http\Response;
use Leonied7\Yandex\Disk\Model\Body;
use Leonied7\Yandex\Disk\Model\Decorator;
use Leonied7\Yandex\Disk\Entity\Result;
use Leonied7\Yandex\Disk\Model\Stream;
use Leonied7\Yandex\Disk\Query\Data as QueryData;

/**
 * Class Builder построитель запросов
 * @package Leonied7\Yandex\Disk\Query
 */
class Builder
{
    protected $request;
    /** @var string */
    protected $execHandler = \Leonied7\Yandex\Disk\Result\Get::class;
    /** @var Decorator */
    protected $resultDecorator;

    public function __construct()
    {
        $this->request = new Request($this);
    }

    /**
     * @param QueryData $queryData
     * @param $path
     * @return static
     */
    public static function createByData(QueryData $queryData, $path)
    {
        $builder = new static();
        $builder
            ->setHeaders([
                'Authorization' => "OAuth {$queryData->getToken()}",
                'Accept' => '*/*'
            ])
            ->setPath($path);
        return $builder;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * установка метода
     * @param $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->request->setMethod($method);
        return $this;
    }

    /**
     * установка пути
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->request->setUrl($path);
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->request->getUrl();
    }

    /**
     * установка заголовков
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers = [])
    {
        $this->request->setHeaders($headers);
        return $this;
    }

    /**
     * добавление заголовков
     * @param array $headers
     * @return $this
     */
    public function addHeaders(array $headers)
    {
        $this->request->addHeaders($headers);
        return $this;
    }

    /**
     * установка GET параметров
     * @param array $params
     * @return $this
     */
    public function setParams(array $params = [])
    {
        $this->request->setQueryParams($params);
        return $this;
    }

    /**
     * добавление GET параметров
     * @param array $params
     * @return $this
     */
    public function addParams(array $params = [])
    {
        $this->request->addQueryParams($params);
        return $this;
    }

    /**
     * установка тела запроса
     * @param Body $body
     * @return $this
     */
    public function setBody(Body $body)
    {
        $this->request->setContent($body->build());
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
    public function setBeforeSend(callable $handler)
    {
        $this->request->setBeforeSend($handler);
        return $this;
    }

    public function beforeSend()
    {
        if (($inputFile = $this->request->getInputFile()) && $inputFile->getStream()) {
            $this->request->addOptions([
                CURLOPT_FILE => $inputFile->getStream()
            ]);
        }

        if (($outputFile = $this->request->getOutputFile()) && $outputFile->getStream()) {
            $this->request->addOptions([
                CURLOPT_INFILE => $outputFile->getStream(),
                CURLOPT_PUT => true
            ]);
        }
    }

    public function afterSend(Result $result)
    {
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

    public function setInputFile(Stream $stream = null)
    {
        $this->request->setInputFile($stream);
        return $this;
    }

    /**
     * @return Stream
     */
    public function getInputFile()
    {
        return $this->request->getInputFile();
    }

    public function setOutputFile(Stream $stream = null)
    {
        $this->request->setOutputFile($stream);
        return $this;
    }

    /**
     * @return Stream
     */
    public function getOutputFile()
    {
        return $this->request->getOutputFile();
    }

    public function setRange($from = 0, $to = null)
    {
        $this->request->addOptions([
            CURLOPT_RANGE => "{$from}-{$to}"
        ]);
        return $this;
    }

    /**
     * выполнение запроса
     * @return Result
     */
    public function send()
    {
        $transport = new Transport();
        return $transport->send($this->request);
    }

    public function createResponse($content, $headers, $code, $type)
    {
        $response = new Response($this, $code, $type);
        $response->setContent($content);
        $response->setHeaders($headers);
        return $response;
    }

    public function createResult(Response $response)
    {
        /** @var Result $result */
        $result = new $this->execHandler($response);
        ResultList::getInstance()->add($result);
        $result->setDecorator($this->resultDecorator);
        return $result;
    }
}