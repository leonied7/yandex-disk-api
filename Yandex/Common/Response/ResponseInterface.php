<?php

namespace Yandex\Common\Response;

/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 13.04.2017
 * Time: 17:29
 */
interface ResponseInterface
{
    function __construct();

    /**
     * @param string $data
     *
     * @return $this
     */
    function setData($data);

    /**
     * @return mixed
     */
    function prepare();
}