<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 15.01.2018
 * Time: 12:38
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Stream;

use Leonied7\Yandex\Disk\Model\Stream;

/**
 * Class File осуществляет работу с потоком файла, используется для записи/чтения файла
 * @package Leonied7\Yandex\Disk\Stream
 */
class File extends Stream
{
    const MODE_READ = 'rb';
    const MODE_WRITE = 'wb';
    const MODE_WRITE_APPEND = 'ab';

    private $mode;

    public function __construct($filePath, $mode = self::MODE_READ)
    {
        $this->mode = $mode;
        parent::__construct($filePath);
    }

    public function open()
    {
        return fopen($this->file, $this->mode);
    }

    /**
     * @return void
     */
    public function close()
    {
        @fclose($this->getStream());
    }
}