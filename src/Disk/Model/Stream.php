<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 15.01.2018
 * Time: 12:16
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Model;

use Leonied7\Yandex\Disk\Exception\InvalidArgumentException;
use Leonied7\Yandex\Disk\Http\Request;

/**
 * Class Stream используется для работы с потоками
 * @package Leonied7\Yandex\Disk\Model
 */
abstract class Stream
{
    protected $file;
    /** @var resource */
    protected $stream;

    /**
     * Stream constructor.
     * @param $filePath
     */
    public function __construct($filePath)
    {
        $this->file = $filePath;
    }

    /**
     * открывает и проверяет поток
     * @throws InvalidArgumentException
     */
    public function create()
    {
        $this->stream = $this->open();

        if (!$this->checkStream()) {
            throw new InvalidArgumentException("error opening stream to file '{$this->file}'");
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * получение мета-данных о потоке
     * @return array
     */
    public function getMetaData()
    {
        return stream_get_meta_data($this->stream);
    }

    /**
     * @param Request $request
     */
    public static function addMetaData(Request $request)
    {
        $file = $request->getBuilder()->getOutputFile();
        $streamMeta = $file->getMetaData();
        $stream = $file->getStream();
        $md5 = hash_init('md5');
        hash_update_stream($md5, $stream);
        $sha256 = hash_init('sha256');
        hash_update_stream($sha256, $stream);
        rewind($stream); //скидываем указатель потока, т.к. hash_update_stream его сдвигает
        $request->addHeaders([
            'Etag' => hash_final($md5),
            'Sha256' => hash_final($sha256),
            'Content-Type' => mime_content_type($streamMeta['uri'])
        ]);
    }

    /**
     * @return resource
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * проверка потока
     * @return bool
     */
    protected function checkStream()
    {
        return is_resource($this->getStream());
    }

    /**
     * @return resource
     */
    abstract public function open();

    /**
     * @return void
     */
    abstract public function close();
}