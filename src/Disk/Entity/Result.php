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
use Leonied7\Yandex\Disk\Http\Response;
use Leonied7\Yandex\Disk\Model\Decorator;
use Leonied7\Yandex\Disk\Query\Builder;

/**
 * Class Result осуществляет работу с результатов ответа
 * @package Leonied7\Yandex\Disk\Entity
 */
abstract class Result
{
    /** @var Response */
    protected $response;
    protected $result;
    /** @var Decorator */
    protected $decorator;

    public function __construct(Response $response)
    {
        $this->response = $response;

        $result = $this->getResponse()->getContent();
        if ($this->getResponseType() === Response::TYPE_XML) {
            @$xml = new FluidXml($result); // глушим предупреждение "xmlns: URI mynamespace is not absolute"
            $result = $this->prepareDom($xml);
        }

        $this->result = $result;
    }

    /**
     * @return Builder
     */
    public function getBuilder()
    {
        return $this->getResponse()->getBuilder();
    }

    /**
     * @return Response
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
    public function setDecorator(Decorator $decorator = null)
    {
        $this->decorator = $decorator;
        return $this;
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
     * получение результата пропущеного через заранее установленный декоратор через setDecorator()
     * @return mixed
     */
    public function getResult()
    {
        return $this->getDecorator() ? $this->getDecorator()->convert($this->result) : $this->getActualResult();
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
     * вызывается только если тип ответа xml формата
     * @param FluidXml $xml
     * @return mixed - возвращаемое значение попадёт в prepare
     */
    protected function prepareDom(FluidXml $xml)
    {
        return null;
    }

    /**
     * должен возвращать список удовлетворяющих кодов ответов от диска
     * @return array
     */
    abstract protected function getGoodCode();
}