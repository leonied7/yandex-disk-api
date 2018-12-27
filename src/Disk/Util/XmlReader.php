<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 14.04.2017
 * Time: 10:38
 */

namespace Leonied7\Yandex\Disk\Util;

/**
 * Class XmlReader вспомогательный класс для работы с XML
 * @package Leonied7\Yandex\Disk\Util
 */
class XmlReader
{
    /**
     * @param \DOMNode $node
     *
     * @return array
     */
    public static function getArray(\DOMNode $node)
    {

        if (!$node->hasChildNodes()) {
            return [
                'name' => $node->localName,
                'value' => $node->nodeValue,
                'namespace' => $node->namespaceURI
            ];
        }

        if ($node->firstChild->nodeType === XML_TEXT_NODE) {
            $array = [
                'name' => $node->localName,
                'value' => $node->nodeValue,
                'namespace' => $node->namespaceURI
            ];
        } else {
            $children = [];
            /** @var $childNode \DOMNode */
            foreach ($node->childNodes as $childNode) {
                if ($childNode->nodeType !== XML_TEXT_NODE) {
                    $children[] = self::getArray($childNode);
                }
            }

            $array = [
                'name' => $node->localName,
                'value' => '',
                'namespace' => $node->namespaceURI,
                'children' => $children
            ];
        }

        return $array;
    }

    public static function getValueByTag(\DOMElement $element, $tagName)
    {
        return $element->getElementsByTagName($tagName)[0]->nodeValue;
    }
}