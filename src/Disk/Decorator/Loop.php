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
 * Class Loop пустой декоратор, возвращает входящий результат без изменений
 * @package Leonied7\Yandex\Disk\Decorator
 */
class Loop implements Decorator
{
    /**
     * @param mixed $result
     * @return mixed
     */
    public function convert($result)
    {
        return $result;
    }
}