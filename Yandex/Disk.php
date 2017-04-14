<?php

/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 06.04.2017
 * Time: 9:42
 */

namespace Yandex;

use Yandex\Common\CurlResponse;
use Yandex\Common\CurlWrapper;
use Yandex\Common\Prop;
use Yandex\Common\QueryBuilder;
use Yandex\Common\Response\Propfind;
use Yandex\Common\Response\Proppatch;
use Yandex\Protocol\Webdav;

class Disk
{
    protected $token;
    protected $url = "https://webdav.yandex.ru";

    protected $handler = null;
    /**
     * последний ответ курла
     * @var CurlResponse
     */
    protected $lastResponse;
    protected $queryBuilder;

    function __construct($token)
    {
        if(!$token)
            throw new \InvalidArgumentException('token is required parameter');

        $this->token = $token;
    }

    public function setHandler($handler)
    {
        $this->handler = $handler;
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }


    /**
     * получение содержимого папки $path, так же по стандарту из результата удаляется текущая папка, если требуется оставить установить $thisFolder в true
     * $offset и $amount указаны в документации
     *
     * @link https://tech.yandex.ru/disk/doc/dg/reference/propfind_contains-request-docpage/
     *
     * @param $path
     * @param int $offset
     * @param null $amount
     * @param bool $thisFolder
     *
     * @return array
     * @throws \Exception
     */
    public function directoryContents($path, $offset = 0, $amount = null, $thisFolder = false)
    {
        if(!$path)
            throw new \Exception('path is required parameter');

        $this->lastResponse = $this->createQuery()
            ->setMethod('PROPFIND')
            ->setUrl($this->getPath($path))
            ->setHeaders(array('Depth' => '1'))
            ->setParams(array(
                'offset' => $offset,
                'amount' => $amount
            ))
            ->setResponseHandler(new Propfind())
            ->exec();

        $contents = $this->lastResponse->getBody();

        if($thisFolder)
            return $contents;

        foreach($contents as $key => $content)
        {
            if($content['href'] === $this->correctPath($path) . DIRECTORY_SEPARATOR)
                unset($contents[$key]);
        }

        return $contents;
    }

    /**
     * получение свободного/занятого места
     *
     * @link https://tech.yandex.ru/disk/doc/dg/reference/propfind_space-request-docpage/
     *
     * @return array|string
     */
    public function spaceInfo()
    {
        $info = array(
            "available" => "quota-available-bytes",
            "used"      => "quota-used-bytes"
        );

        $webDav = new Webdav();


        $body = $webDav->propfindMethod()
            ->setProp(new Prop($info))
            ->getProp();

        $this->lastResponse = $this->createQuery()
            ->setMethod('PROPFIND')
            ->setUrl($this->getPath('/'))
            ->setHeaders(array("Depth" => 0))
            ->setBody($body)
            ->setResponseHandler(new Propfind())
            ->exec();

        $props = array();

        foreach($this->lastResponse->getBody() as $folder)
        {
            foreach($folder['props']['found'] as $arProp)
            {
                if($key = array_search($arProp['name'], $info))
                    $props[$key] = $arProp['value'];
            }
        }

        return $props;
    }

    /**
     * получение свойств файла/каталога
     *
     * @link https://tech.yandex.ru/disk/doc/dg/reference/propfind_property-request-docpage/
     *
     * @param $path
     * @param Prop $props
     *
     * @return array
     * @throws \Exception
     */
    public function getProperties($path, Prop $props)
    {
        if(!$path)
            throw new \Exception('path is required parameter');

        $webDav = new Webdav();

        $body = $webDav->propfindMethod()
            ->setProp($props)
            ->getProp();

        $this->lastResponse = $this->createQuery()
            ->setMethod('PROPFIND')
            ->setUrl($this->getPath('/'))
            ->setHeaders(array("Depth" => 0))
            ->setBody($body)
            ->setResponseHandler(new Propfind())
            ->exec();

        $result = array();

        foreach($this->lastResponse->getBody() as $folder)
        {
            $result = $folder['props'];
        }

        return $result;
    }

    /**
     * установка/удаление свойств для файла/папки
     *
     * @link https://tech.yandex.ru/disk/doc/dg/reference/proppatch-docpage/
     *
     * @param $path
     * @param Prop $props
     *
     * @return string|array
     * @throws \Exception
     */
    public function changeProperties($path, Prop $props)
    {
        if(!$path)
            throw new \Exception('path is required parameter');

        $webDav = new Webdav();

        $body = $webDav->propPatchMethod($props);


        $this->lastResponse = $this->createQuery()
            ->setMethod('PROPPATCH')
            ->setUrl($this->getPath($path))
            ->setBody($body)
            ->setResponseHandler(new Proppatch())
            ->exec();

        $result = $this->lastResponse->getBody();

        if(!is_array($result))
            return $result;

        return $result['props'];
    }

    /**
     * Удаление свойств у файла/папки
     *
     * @deprecated
     * @link https://tech.yandex.ru/disk/doc/dg/reference/proppatch-docpage/
     *
     * @param string $path
     * @param string|array $props
     * @param string $namespace
     *
     * @return bool
     * @throws \Exception
     */
    public function removeProperties($path, $props, $namespace = 'default')
    {
        if(!$props)
            throw new \Exception('props is required parameter');

        $props = is_array($props) ? $props : array($props);

        $arProps = [];

        foreach($props as $prop)
        {
            $arProps[$prop] = false;
        }

        return $this->setProperties($path, $arProps, $namespace);
    }

    /**
     * Публикация файла/папки
     * если папка/файл найдена то возвращает ссылку, иначе false
     *
     * @link https://tech.yandex.ru/disk/doc/dg/reference/publish-docpage/
     *
     * @param $path
     *
     * @return bool|string
     */
    public function startPublish($path)
    {
        $propObj = new Prop('public_url', 'urn:yandex:disk:meta', true);

        foreach($this->changeProperties($path, $propObj) as $props)
        {
            foreach($props as $prop)
                return $prop['value'];
        }

        return false;
    }

    /**
     * Снятие публикации файла/папки возвращает true/false
     *
     * @link https://tech.yandex.ru/disk/doc/dg/reference/publish-docpage/
     *
     * @param $path
     *
     * @return bool
     */
    public function stopPublish($path)
    {
        return $this->removeProperties($path, 'public_url', 'urn:yandex:disk:meta');
    }

    /**
     * Проверка публикации файла/папки
     * если папка/файл опубликован то вернет ссылка, иначе false
     *
     * @link https://tech.yandex.ru/disk/doc/dg/reference/publish-docpage/
     *
     * @param $path
     *
     * @return mixed
     */
    public function checkPublish($path)
    {
        return $this->getProperties($path, ['public_url'], 'urn:yandex:disk:meta')['public_url'];
    }

    /**
     * Получение превью картинки
     *
     * @link https://tech.yandex.ru/disk/doc/dg/reference/preview-docpage/
     *
     * @param $path
     * @param string $size
     * @param bool|resource $stream ресурс файла, в который писать ответ
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function getPreviewImage($path, $size = 'XXXS', $stream = false)
    {
        if(!$path)
            throw new \Exception('path is required parameter');

        $options = [
            'headers' => [
                'Authorization' => "OAuth {$this->token}"
            ],
            'query'   => [
                'preview' => '',
                'size'    => $size
            ]
        ];

        if($stream)
            $options['infile'] = $stream;

        $response = $this->createWrapper('GET', $this->getPath($path), $options);

        $this->lastResponse = $response->exec();

        if($this->lastResponse->getCode() != 200)
            return false;

        if($stream)
            return true;

        return $this->lastResponse->getBody();
    }

    /**
     * Запрос логина пользователя
     * TODO:Можно возвращать массив значений
     *
     * @link https://tech.yandex.ru/disk/doc/dg/reference/userinfo-docpage/
     *
     * @param string $params
     *
     * @return bool|string
     */
    public function getLogin($params = 'login')
    {
        $response = $this->createWrapper('GET', $this->getPath('/'), [
            'headers' => [
                'Authorization' => "OAuth {$this->token}",
            ],
            'query'   => [
                'userinfo' => ''
            ]
        ]);

        $this->lastResponse = $response->exec();

        if($this->lastResponse->getCode() != 200)
            return false;

        $arResult = [];

        $arData = explode("\n", trim($this->lastResponse->getBody(), "\n"));

        foreach($arData as $data)
        {
            $element = explode(':', $data);

            $arResult[$element[0]] = $element[1];
        }

        return $arResult['login'];
    }

    /**
     * Скачивание файла, поддерживает дозагрузку файла
     *
     * @link https://tech.yandex.ru/disk/doc/dg/reference/get-docpage/
     *
     * @param string $path
     * @param resource $stream
     * @param bool|int $from
     * @param bool|int $to
     *
     * @return bool
     * @throws \Exception
     */
    public function getFile($path, $stream, $from = false, $to = false)
    {
        if(!$path)
            throw new \Exception('path is required parameter');

        if(gettype($stream) != "resource")
            throw new \Exception('stream is not resource');

        $response = $this->createWrapper('GET', $this->getPath($path), [
            'headers' => [
                'Authorization' => "OAuth {$this->token}"
            ],
            'infile'  => $stream,
            'range'   => [$from, $to]
        ]);

        $this->lastResponse = $response->exec();

        if(!in_array($this->lastResponse->getCode(), [200, 206]))
            return false;

        return true;
    }

    /**
     * загрузка файла, работает с открытыми потоками
     *
     * @link https://tech.yandex.ru/disk/doc/dg/reference/put-docpage/
     *
     * @param string $path
     * @param resource $stream
     *
     * @return bool
     * @throws \Exception
     */
    public function putFile($path, $stream)
    {
        if(!$path)
            throw new \Exception('path is required parameter');

        if(gettype($stream) != "resource")
            throw new \Exception('stream is not resource');

        $streamMeta = stream_get_meta_data($stream);

        $response = $this->createWrapper('PUT', $this->getPath($path), [
            'headers' => [
                'Authorization' => "OAuth {$this->token}",
                'Etag'          => md5_file($streamMeta['uri']),
                'Sha256'        => hash_file('sha256', $streamMeta['uri']),
                'Content-Type'  => mime_content_type($streamMeta['uri'])
            ],
            'file'    => $stream
        ]);

        $this->lastResponse = $response->exec();

        if($this->lastResponse->getCode() == 201)
            return true;

        return false;
    }

    /**
     * Создание каталога
     *
     * @link https://tech.yandex.ru/disk/doc/dg/reference/mkcol-docpage/
     *
     * @param string $path
     *
     * @return bool
     * @throws \Exception
     */
    public function createDir($path)
    {
        if(!$path)
            throw new \Exception('path is required parameter');

        $response = $this->createWrapper('MKCOL', $this->getPath($path), [
            'headers' => [
                'Authorization' => "OAuth {$this->token}"
            ]
        ]);

        $this->lastResponse = $response->exec();

        if(in_array($this->lastResponse->getCode(), [201, 405]))
            return true;

        return false;
    }

    /**
     * Копирование файла/папки
     *
     * @link https://tech.yandex.ru/disk/doc/dg/reference/copy-docpage/
     *
     * @param string $path
     * @param string $destination
     * @param bool $overwrite
     *
     * @return bool
     * @throws \Exception
     */
    public function copy($path, $destination, $overwrite = true)
    {
        if(!$path)
            throw new \Exception('path is required parameter');

        if(!$destination)
            throw new \Exception('destination point is required parameter');

        $overwrite = $overwrite ? 'T' : 'F';

        $response = $this->createWrapper('COPY', $this->getPath($path), [
            'headers' => [
                'Authorization' => "OAuth {$this->token}",
                'Destination'   => $this->correctPath($destination),
                'Overwrite'     => $overwrite
            ]
        ]);

        $this->lastResponse = $response->exec();

        if(in_array($this->lastResponse->getCode(), [201]))
            return true;

        return false;
    }

    /**
     * Перемещение/переименование файла/папки
     *
     * @link https://tech.yandex.ru/disk/doc/dg/reference/move-docpage/
     *
     * @param string $path
     * @param string $destination
     * @param bool $overwrite
     *
     * @return bool
     * @throws \Exception
     */
    public function move($path, $destination, $overwrite = true)
    {
        if(!$path)
            throw new \Exception('path is required parameter');

        if(!$destination)
            throw new \Exception('destination point is required parameter');

        $overwrite = $overwrite ? 'T' : 'F';

        $response = $this->createWrapper('MOVE', $this->getPath($path), [
            'headers' => [
                'Authorization' => "OAuth {$this->token}",
                'Destination'   => $this->correctPath($destination),
                'Overwrite'     => $overwrite
            ]
        ]);

        $this->lastResponse = $response->exec();

        if(in_array($this->lastResponse->getCode(), [201]))
            return true;

        return false;
    }

    /**
     * Удаление файла/папки
     *
     * @link https://tech.yandex.ru/disk/doc/dg/reference/delete-docpage/
     *
     * @param string $path
     *
     * @return bool
     * @throws \Exception
     */
    public function delete($path)
    {
        if(!$path)
            throw new \Exception('path is required parameter');

        $response = $this->createWrapper('DELETE', $this->getPath($path), [
            'headers' => [
                'Authorization' => "OAuth {$this->token}"
            ]
        ]);

        $this->lastResponse = $response->exec();

        if(in_array($this->lastResponse->getCode(), [204, 200]))
            return true;

        return false;
    }

    /**
     * создание запросов
     *
     * @return QueryBuilder
     */
    public function createQuery()
    {
        $builder = new QueryBuilder(new Webdav());

        return $builder->setHeaders(array("Authorization" => "OAuth {$this->token}"));
    }


    protected function createWrapper($method, $uri, $params)
    {
        return new CurlWrapper($method, $uri, $params, $this->handler);
    }

    protected function getDecode()
    {
        $dom = new \DOMDocument();

        $dom->loadXML($this->lastResponse->getBody());

        print_r($this->lastResponse->getBody());

        print_r($dom->hasChildNodes());

        $result = [];

        foreach($dom->getElementsByTagName('response') as $element)
        {
            $result[] = $this->getArray($element);
        }

        return $result;
    }

    //    protected function recurseXML($xml, &$result)
    //    {
    //        $child_count = 0;
    //
    //        foreach($xml as $key => $value)
    //        {
    //            $child_count++;
    //            if(!$this->recurseXML($value, $result))
    //            {
    //                $result[(string)$key] = (string)$value;
    //            }
    //        }
    //
    //        return $child_count;
    //    }

    /**
     * @param \DOMNode $node
     *
     * @return array
     */
    protected function getArray($node)
    {
        if(!$node->hasChildNodes())
            return array(
                'name'      => $node->localName,
                'value'     => $node->nodeValue,
                'namespace' => $node->namespaceURI
            );

        if($node->firstChild->nodeType === XML_TEXT_NODE)
        {
            $array = array(
                'name'      => $node->localName,
                'value'     => $node->nodeValue,
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
                    $children[] = $this->getArray($childNode);
                }
            }

            $array = array(
                'name'      => $node->localName,
                'namespace' => $node->namespaceURI,
                'children'  => $children
            );
        }

        return $array;
    }

    protected function prepareResponse()
    {
    }

    /**
     * корректирует путь
     *
     * @param $path
     *
     * @return string
     */
    protected function correctPath($path)
    {
        $path = trim($path, DIRECTORY_SEPARATOR);

        return DIRECTORY_SEPARATOR . trim($path, DIRECTORY_SEPARATOR);
    }

    private function getPath($path)
    {
        return $this->url . $this->correctPath($path);
    }
}