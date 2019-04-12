<?php
/**
 * Created by PhpStorm.
 * User: Денис
 * Date: 14.03.2019
 * Time: 21:54
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Http;


use Leonied7\Yandex\Disk\Entity\Result;
use Leonied7\Yandex\Disk\Model\Stream;
use Leonied7\Yandex\Disk\Query\Data as QueryData;

class Request extends Message
{
    protected $url;
    protected $method = 'GET';
    protected $options = [];
    /** @var Stream */
    protected $inputFile;
    /** @var Stream */
    protected $outputFile;
    protected $beforeSend;
    protected $afterSend;

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getFullUrl()
    {
        $url = QueryData::getUrl($this->url);
        if (empty($this->getQueryParams())) {
            return $url;
        }
        $url .= '?' . http_build_query($this->getQueryParams());
        return $url;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function addOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $this->options[$key] = $value;
        }
        return $this;
    }

    public function setInputFile(Stream $stream = null)
    {
        $this->inputFile = $stream;
        return $this;
    }

    /**
     * @return Stream
     */
    public function getInputFile()
    {
        return $this->inputFile;
    }

    public function setOutputFile(Stream $stream = null)
    {
        $this->outputFile = $stream;
        return $this;
    }

    /**
     * @return Stream
     */
    public function getOutputFile()
    {
        return $this->outputFile;
    }

    public function setBeforeSend(callable $beforeSend)
    {
        $this->beforeSend = $beforeSend;
        return $this;
    }

    public function setAfterSend(callable $afterSend)
    {
        $this->afterSend = $afterSend;
        return $this;
    }

    public function send()
    {
        $this->getBuilder()->send();
    }


    public function beforeSend()
    {
        $this->fileActionBeforeSend();
        $this->getBuilder()->beforeSend();

        if (is_callable($this->beforeSend)) {
            \call_user_func($this->beforeSend, $this);
        }
    }


    public function afterSend(Result $result)
    {
        $this->fileActionAfterSend();
        $this->getBuilder()->afterSend($result);

        if (is_callable($this->afterSend)) {
            \call_user_func($this->afterSend, $this, $result);
        }
    }

    private function fileActionBeforeSend()
    {
        if ($this->inputFile) {
            $this->inputFile->create();
        }

        if ($this->outputFile) {
            $this->outputFile->create();
        }
    }

    private function fileActionAfterSend()
    {
        if ($this->inputFile) {
            $this->inputFile->close();
        }
        if ($this->outputFile) {
            $this->outputFile->close();
        }
    }
}