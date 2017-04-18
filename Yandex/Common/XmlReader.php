<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 14.04.2017
 * Time: 10:38
 */

namespace Yandex\Common;

class XmlReader
{
    /**
     * @param \DOMDocument $dom
     * @param \DOMNode $domNode
     * @param string $query
     *
     * @return \DOMNodeList
     */
    static function getElementsByQuery(\DOMDocument $dom, $query, \DOMNode $domNode = null)
    {
        $xPath = new \DOMXPath($dom);

        return $xPath->query($query, $domNode);
    }

    /**
     * @param \DOMNode $node
     *
     * @return array
     */
    static function getArray(\DOMNode $node)
    {
        if(!$node->hasChildNodes())
            return array(
                'name' => $node->localName,
                'value' => $node->nodeValue,
                'namespace' => $node->namespaceURI
            );

        if($node->firstChild->nodeType === XML_TEXT_NODE)
        {
            $array = array(
                'name' => $node->localName,
                'value' => $node->nodeValue,
                'namespace' => $node->namespaceURI
            );
        }
        else
        {
            $children = array();
            /**
             * @var $childNode \DOMNode
             */
            foreach($node->childNodes as $childNode)
            {
                if($childNode->nodeType != XML_TEXT_NODE)
                {
                    $children[] = self::getArray($childNode);
                }
            }

            $array = array(
                'name' => $node->localName,
                'namespace' => $node->namespaceURI,
                'children' => $children
            );
        }

        return $array;
    }

    static function getValue(\DOMNode $node)
    {
        return $node->nodeValue;
    }
}