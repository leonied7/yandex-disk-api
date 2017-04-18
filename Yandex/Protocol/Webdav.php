<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 11.04.2017
 * Time: 16:55
 */

namespace Yandex\Protocol;

use Yandex\Common\Prop;
use Yandex\Common\PropPool;
use Yandex\Protocol\Method\Propfind;
use Yandex\Protocol\Method\Proppatch;

class Webdav
{
    /**
     * @return Propfind
     */
    public function propfindMethod()
    {
        return new Propfind();
    }

    /**
     * @param PropPool $prop
     *
     * @return Proppatch
     */
    public function propPatchMethod(PropPool $prop)
    {
        return new Proppatch($prop);
    }

    private function getMethod()
    {}

    private function putMethod()
    {}

    private function mkColMethod()
    {}

    private function copyMethod()
    {}

    private function moveMethod()
    {}

    private function deleteMethod()
    {}
}