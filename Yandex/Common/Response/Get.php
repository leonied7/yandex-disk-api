<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 17.04.2017
 * Time: 17:01
 */

namespace Yandex\Common\Response;

class Get extends ResponseInterface
{
    protected $goodAnswerCode = array(200, 206);

    /**
     * @return mixed
     */
    function prepare()
    {
        $this->checkCode();

        return $this->data;
    }
}