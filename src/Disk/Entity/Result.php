<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 15.01.2018
 * Time: 9:07
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Entity;

use FluidXml\FluidXml;
use Leonied7\Yandex\Disk\Curl\Response as CurlResponse;
use Leonied7\Yandex\Disk\Decorator\Loop;
use Leonied7\Yandex\Disk\Model\Decorator;

/**
 * Class Result осуществляет работу с результатов ответа
 * @package Leonied7\Yandex\Disk\Entity
 */
abstract class Result
{
    const YANDEX_DISK_RESULT_OK_STATUS = '200 OK';
    const YANDEX_DISK_RESULT_XML = 'application/xml';
    const YANDEX_DISK_RESULT_TEXT = 'text/plain';

    /** @var CurlResponse */
    protected $response;
    /** @var FluidXml */
    protected $xml;

    protected $result;
    /** @var Decorator */
    protected $decorator;

    public function __construct(CurlResponse $response)
    {
        $this->decorator = new Loop();

        $this->response = $response;

        $result = $this->getResponse()->getBody();
        if ($this->getResponseType() === self::YANDEX_DISK_RESULT_XML) {
            @$this->xml = new FluidXml($result); // глушим предупреждение "xmlns: URI mynamespace is not absolute"
            $result = $this->prepareDom();
        }

        $this->result = $this->prepare($result);
    }

    /**
     * @return CurlResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return Decorator
     */
    public function getDecorator()
    {
        return $this->decorator;
    }

    /**
     * @param Decorator $decorator
     * @return Result
     */
    public function setDecorator(Decorator $decorator)
    {
        $this->decorator = $decorator;
        return $this;
    }

    /**
     * получение результата пропущеного через заранее установленный декоратор через setDecorator()
     * @return mixed
     */
    public function getResult()
    {
        return $this->getDecorator()->convert($this->result);
    }

    /**
     * возвращает декорированный результат через входной декоратор
     * @param Decorator $decorator
     * @return mixed
     */
    public function getDecorateResult(Decorator $decorator)
    {
        return $decorator->convert($this->result);
    }

    /**
     * получение результат
     * @return mixed
     */
    public function getActualResult()
    {
        return $this->result;
    }

    /**
     * возвращает формат ответа
     * @return string
     */
    public function getResponseType()
    {
        list($type) = explode(';', $this->getResponse()->getType());
        return $type;
    }

    /**
     * получение кода ответа
     * @return string
     */
    public function getResponseCode()
    {
        return $this->getResponse()->getCode();
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return in_array((int)$this->getResponse()->getCode(), $this->getGoodCode(), true);
    }


    /**
     * должен возвращать список удовлетворяющих кодов ответов от диска
     * @return array
     */
    abstract protected function getGoodCode();

    /**
     * преобразование результата, возращенное значение из функции будет записано в результат
     * @param $data
     * @return mixed
     */
    protected function prepare($data)
    {
        return $data;
    }

    /**
     * вызывается только если тип ответа xml формата
     * @return mixed - возвращаемое значение попадёт в prepare
     */
    abstract protected function prepareDom();
}