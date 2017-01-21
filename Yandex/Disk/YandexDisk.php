<?php

/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 20.01.2017
 * Time: 14:23
 */
namespace Yandex\Disk;

use Yandex\Common\CurlResponse;
use Yandex\Common\CurlWrapper;

class YandexDisk
{
    protected $token;

    protected $url = "https://webdav.yandex.ru";

    /**
     * последний ответ курла
     * @var CurlResponse
     */
    protected $lastResponse;

    public function getLastResponse()
    {
        return $this->lastResponse;
    }

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

    public function directoryContents($url, $offset = 0, $amount = null, $thisFolder = false)
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
                ],
                'query' => [
                    'offset' => $offset,
                    'amount' => $amount
                ]
            ]
        );

        $this->lastResponse = $response->exec();

        $decodedBody = $this->getDecode($this->lastResponse->getBody());

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


    private function recurseXML($xml, &$result)
    {
        $child_count = 0;

        foreach($xml as $key=>$value)
        {
            $child_count++;
            if(!$this->recurseXML($value, $result))
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
        $path = trim($path, DIRECTORY_SEPARATOR);

        return $path ? DIRECTORY_SEPARATOR . trim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR : DIRECTORY_SEPARATOR;
    }
}