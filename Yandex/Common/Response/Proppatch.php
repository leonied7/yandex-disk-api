<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 14.04.2017
 * Time: 15:48
 */

namespace Yandex\Common\Response;

use Yandex\Common\XmlReader;

class Proppatch implements ResponseInterface
{
    protected $data;

    protected $dom;

    public function __construct()
    {
    }

    /**
     * @param string $data
     *
     * @return $this
     */
    function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return mixed
     */
    function prepare()
    {
        return $this->getDecode();
    }

    protected function getDecode()
    {
        $this->dom = new \DOMDocument();

        $this->dom->loadXML($this->data);

        if(!$this->dom->hasChildNodes())
            return $this->data;

        $result = array(
            'href' => $this->getValue(".//d:href")
        );


        foreach(XmlReader::getElementsByQuery($this->dom, ".//d:propstat") as $propStat)
        {
            $status = $this->getValue("d:status", $propStat);

            $props = $this->getProps("d:prop", $propStat);

            $result['props'][$status] = $props;
        }


        return $result;
    }

    /**
     * получить значение узла по xPath строке
     *
     * @param $query
     * @param $element
     *
     * @return bool|string
     */
    protected function getValue($query, $element = null)
    {
        foreach(XmlReader::getElementsByQuery($this->dom, $query, $element) as $href)
            return XmlReader::getValue($href);

        return false;
    }


    protected function getProps($query, $element = null)
    {
        $props = array();

        foreach(XmlReader::getElementsByQuery($this->dom, $query, $element) as $prop)
        {
            $props = XmlReader::getArray($prop);
        }

        return $props['children'];
    }
}