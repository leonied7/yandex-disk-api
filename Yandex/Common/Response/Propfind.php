<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 14.04.2017
 * Time: 10:14
 */

namespace Yandex\Common\Response;

use Yandex\Common\XmlReader;

class Propfind extends ResponseInterface
{
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

        if(!$this->checkDom())
            return $this->data;

        $result = array();


        foreach($this->dom->getElementsByTagName('response') as $element)
        {
            $item = array();

            $item['href'] = $this->getValue("d:href", $element);

            foreach(XmlReader::getElementsByQuery($this->dom, "d:propstat", $element) as $propStat)
            {
                $status = $this->getValue("d:status", $propStat);

                $props = $this->getProps("d:prop", $propStat);

                $key = strpos($status, '200 OK') === false ? 'notFound' : 'found';

                $item['props'][$key] = $props;
            }


            $result[] = $item;
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
    protected function getValue($query, $element)
    {
        foreach(XmlReader::getElementsByQuery($this->dom, $query, $element) as $href)
            return XmlReader::getValue($href);

        return false;
    }


    protected function getProps($query, $element)
    {
        $props = array();

        foreach(XmlReader::getElementsByQuery($this->dom, $query, $element) as $prop)
        {
            $props = XmlReader::getArray($prop);
        }

        return $props['children'];
    }
}