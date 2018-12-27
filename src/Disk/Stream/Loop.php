<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 15.01.2018
 * Time: 13:43
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Stream;


use Leonied7\Yandex\Disk\Model\Stream;

/**
 * Class Loop осуществляет работу с потоком, является заглушкой, используется для получения ответа в теле запроса
 * и сохранении через file_get_content и т.п.
 * @package Leonied7\Yandex\Disk\Stream
 */
class Loop extends Stream
{
    public function __construct($filePath = '')
    {
        parent::__construct($filePath);
    }

    public function open()
    {
    }

    /**
     * @return void
     */
    public function close()
    {
    }

    /**
     * проверка потока
     * @return bool
     */
    public function checkStream()
    {
        return true;
    }
}