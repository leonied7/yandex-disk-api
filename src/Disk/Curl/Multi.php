<?php

namespace Leonied7\Yandex\Disk\Curl;

use Leonied7\Yandex\Disk\Entity\Result;
use Leonied7\Yandex\Disk\Model\Curl;

/**
 * Class Multi обёртка над cUrl для мультазапроса
 * @package Leonied7\Yandex\Disk\Curl
 */
class Multi extends Curl
{
    public function before($resource)
    {
        $this->beforeExec();
        curl_multi_add_handle($resource, $this->curl);
    }

    public function after($resource)
    {
        curl_multi_remove_handle($resource, $this->curl);
        $this->afterExec();
    }

    /**
     * @return mixed
     */
    protected function getResultBody()
    {
        return curl_multi_getcontent($this->curl);
    }

    /**
     * исполнение curl
     * @return Result
     */
    public function exec()
    {
        return $this->createResult();
    }
}