<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 26.04.2017
 * Time: 9:52
 */

namespace Leonied7\Yandex\Disk\Curl;

use Leonied7\Yandex\Disk\Entity\Result;
use Leonied7\Yandex\Disk\Query\Builder;

/**
 * Class MultiWrapper используется для мультизапросов
 * @package Leonied7\Yandex\Disk\Curl
 */
class MultiWrapper
{
    /** @var resource */
    protected $resource;
    /**
     * @var Multi[]
     */
    protected $cUrls = [];

    /** @var bool - активность мультикурла */
    protected $active = false;
    /** @var int - статус ответа */
    protected $mrc;


    public function __construct()
    {
        $this->resource = curl_multi_init();
    }

    public function addBuilder(Builder $builder)
    {
        $this->addCUrl($builder->createMulti());
        return $this;
    }

    public function addCUrl(Multi $cUrl)
    {
        $this->cUrls[] = $cUrl;
        return $this;
    }

    /**
     * @param Multi[] $multiCUrls
     * @return $this
     */
    public function addCUrls(array $multiCUrls = [])
    {
        /** @var Multi $multiCUrl */
        foreach ($multiCUrls as $multiCUrl) {
            $this->addCUrl($multiCUrl);
        }
        return $this;
    }


    /**
     * @return Result[]
     */
    public function exec()
    {
        $this->beforeExec();

        do {
            $this->mrc = curl_multi_exec($this->resource, $this->active);
        } while ($this->mrc === CURLM_CALL_MULTI_PERFORM);

        while ($this->active && $this->mrc === CURLM_OK) {
            if (curl_multi_select($this->resource) === -1) {
                continue;
            }

            do {
                $this->mrc = curl_multi_exec($this->resource, $this->active);
            } while ($this->mrc === CURLM_CALL_MULTI_PERFORM);
        }

        $result = [];

        foreach ($this->cUrls as $key => $multiCUrl) {
            $result[] = $multiCUrl->exec();

            $multiCUrl->after($this->resource);
            unset($this->cUrls[$key]);
        }

        $this->active = false;
        curl_multi_close($this->resource);

        return $result;
    }

    protected function beforeExec()
    {
        foreach ($this->cUrls as $multiCUrl) {
            $multiCUrl->before($this->resource);
        }
    }
}