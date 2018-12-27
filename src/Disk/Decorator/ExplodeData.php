<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 17.01.2018
 * Time: 13:07
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Decorator;

use Leonied7\Yandex\Disk\Model\Decorator;

/**
 * Class ExplodeData используется для формата изменения ответа и разбиение строки на массив типа "ключ => значение"
 * @package Leonied7\Yandex\Disk\Decorator
 */
class ExplodeData implements Decorator
{
    /**
     * возвращает элемент по пути
     * @param mixed $result
     * @return array
     */
    public function convert($result)
    {
        $arResult = [];
        $arData = explode("\n", trim($result, "\n"));
        foreach($arData as $data)
        {
            $element = explode(':', $data);
            $arResult[$element[0]] = $element[1];
        }
        return $arResult;
    }
}