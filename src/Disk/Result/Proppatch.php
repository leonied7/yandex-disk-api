<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 14.04.2017
 * Time: 15:48
 */

namespace Leonied7\Yandex\Disk\Result;

use Leonied7\Yandex\Disk\Collection\PropertyCollection;

/**
 * Class Proppatch осуществляет работу с результатов ответа типа Proppatch
 * @package Leonied7\Yandex\Disk\Result
 */
class Proppatch extends Property
{
    protected $success = false;

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    protected function onSuccessStatus(array $properties, PropertyCollection $collection)
    {
        $this->success = true;
        parent::onSuccessStatus($properties, $collection);
    }
}