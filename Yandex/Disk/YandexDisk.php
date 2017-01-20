<?php

/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 20.01.2017
 * Time: 14:23
 */
namespace Yandex\Disk;

use Yandex\Common\CurlWrapper;

class YandexDisk
{
    protected $token;

    protected $url = "https://webdav.yandex.ru";

    function __construct($token)
    {
        if(!$token)
            throw new \InvalidArgumentException('token is required parameter');

        $this->token = $token;
    }

    private function getPath($path)
    {
        return $this->url . $this->correctPath($path);
    }

    public function directoryContents($url, $thisFolder = false)
    {
        if(!$url)
            throw new \Exception('url is required parameter');

        $response = new CurlWrapper(
            'PROPFIND',
            $this->getPath($url),
            [
                'headers' => [
                    'Depth' => '1',
                    'Authorization' => "OAuth {$this->token}"
                ]
            ]
        );

        $decodedBody = $this->getDecode($response->exec()->getBody());

        $contents = [];

        foreach($decodedBody->children('DAV:') as $element)
        {
            if(!$thisFolder && ($element->href->__toString() === $url))
                continue;

            $result = [];
            $this->recurseXML($element, $result);

            $result['collection'] = isset($result['collection']) ? 'dir' : 'file';

            $contents[] = $result;
        }

        return $contents;
    }

    private function getDecode($body)
    {
        return simplexml_load_string((string) $body);
    }


    function recurseXML($xml, &$result)
    {
        $child_count = 0;

        foreach($xml as $key=>$value)
        {
            $child_count++;
            if(!$this->recurseXML($value, $result))  // no childern, aka "leaf node"
            {
                $result[(string)$key]  = (string)$value;
            }
        }
        return $child_count;
    }

    /**
     * корректирует путь
     * @param $path
     *
     * @return string
     */
    private function correctPath($path)
    {
        return DIRECTORY_SEPARATOR . trim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
}