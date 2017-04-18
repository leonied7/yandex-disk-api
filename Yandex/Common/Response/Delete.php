<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 18.04.2017
 * Time: 12:28
 */

namespace Yandex\Common\Response;

class Delete extends  ResponseInterface
{
    protected $goodAnswerCode = array(200, 204);

    /**
     * @return mixed
     */
    function prepare()
    {
        $this->checkCode();

        return $this->data;
    }
}