<?php

namespace Leonied7\Yandex\Disk\Curl;

use Leonied7\Yandex\Disk\Entity\Result;
use Leonied7\Yandex\Disk\Model\Curl;

/**
 * Class Single обёртка над cUrl для одиночных запросов
 * @package Leonied7\Yandex\Disk\Curl
 */
class Single extends Curl
{
    /**
     * @return mixed
     */
    protected function getResultBody()
    {
        return curl_exec($this->curl);
    }

    /**
     * исполнение curl
     * @return Result
     */
    public function exec()
    {
        $this->beforeExec();

        $result = $this->createResult();

        $this->afterExec();
        return $result;
    }
}