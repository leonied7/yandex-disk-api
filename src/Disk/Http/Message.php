<?php
/**
 * Created by PhpStorm.
 * User: Денис
 * Date: 14.03.2019
 * Time: 22:44
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Http;

use Leonied7\Yandex\Disk\Query\Builder;

class Message
{
    private $builder;

    protected $headers = [];
    protected $queryParams = [];
    protected $content;

    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @return Builder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setHeaders(array $headers = [])
    {
        $this->headers = $headers;
        return $this;
    }

    public function addHeaders(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->headers[$name] = $value;
        }
        return $this;
    }

    public function hasHeaders()
    {
        return !empty($this->headers);
    }

    public function setQueryParams(array $queryParams = [])
    {
        $this->queryParams = $queryParams;
        return $this;
    }

    public function getQueryParams()
    {
        return $this->queryParams;
    }

    public function addQueryParams(array $queryParams)
    {
        if (empty($this->queryParams)) {
            $this->queryParams = $queryParams;
        } else {
            $this->queryParams = array_merge($this->queryParams, $queryParams);
        }
        return $this;
    }

    public function buildHeaders()
    {
        if (empty($this->headers)) {
            return [];
        }
        $headers = [];
        foreach ($this->headers as $name => $value) {
            $headers[] = "{$name}: {$value}";
        }
        return $headers;
    }
}