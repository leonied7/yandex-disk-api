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
use Yandex\Common\PropPool;
use Yandex\Common\QueryBuilder;
use Yandex\Common\Response\Copy;
use Yandex\Common\Response\Delete;
use Yandex\Common\Response\Get;
use Yandex\Common\Response\MkCol;
use Yandex\Common\Response\Propfind;
use Yandex\Common\Response\Proppatch;
use Yandex\Common\Response\Put;
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
     * через $props можно выбрать необходимые свойства для всех элементов
     * $offset и $amount указаны в документации
     *
     * @link https://tech.yandex.ru/disk/doc/dg/reference/propfind_contains-request-docpage/
     *
     * @param $path
     * @param PropPool|null $props
     * @param int $offset
     * @param null $amount
     * @param bool $thisFolder
     *
     * @return array
     * @throws \Exception
     */
    public function directoryContents($path, PropPool $props = null, $offset = 0, $amount = null, $thisFolder = false)
    {
        if(!$path)
            throw new \Exception('path is required parameter');

        $queryBuilder = $this->createQuery()
            ->setMethod('PROPFIND')
            ->setUrl($this->getPath($path))
            ->setHeaders(array('Depth' => '1'))
            ->setParams(array(
                'offset' => $offset,
                'amount' => $amount
            ))
            ->setResponseHandler(new Propfind());

        //формирует и передаёт тело запроса, если передан $props
        if($props instanceof PropPool)
        {
            $webDav = new Webdav();

            $body = $webDav->propfindMethod()->setProp($props)->getProp();

            $queryBuilder->setBody($body);
        }

        $this->lastResponse = $queryBuilder->exec();

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

        $propPool = new PropPool($info);

        $body = $webDav->propfindMethod()
            ->setProp($propPool)
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
     * @param PropPool $props
     *
     * @return array
     * @throws \Exception
     */
    public function getProperties($path, PropPool $props)
    {
        if(!$path)
            throw new \Exception('path is required parameter');

        $webDav = new Webdav();

        $body = $webDav->propfindMethod()
            ->setProp($props)
            ->getProp();

        $this->lastResponse = $this->createQuery()
            ->setMethod('PROPFIND')
            ->setUrl($this->getPath($path))
            ->setHeaders(array("Depth" => 0))
            ->setBody($body)
            ->setResponseHandler(new Propfind())
            ->exec();

        $result = array();

        $answer = $this->lastResponse->getBody();

        if(!is_array($answer))
            return $answer;

        foreach($answer as $folder)
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
     * @param PropPool $props
     *
     * @return string|array
     * @throws \Exception
     */
    public function changeProperties($path, PropPool $props)
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
        $propObj = new PropPool('public_url', 'urn:yandex:disk:meta', true);

        $result = $this->changeProperties($path, $propObj);

        foreach($result as $props)
        {
            foreach($props as $prop)
                return $prop['value'];
        }

        return $result;
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
        $propObj = new PropPool('public_url', "urn:yandex:disk:meta");

        $result = $this->changeProperties($path, $propObj);

        foreach($result as $key => $props)
        {
            if(strpos($key, '200 OK') === false)
                return false;

            return true;
        }

        return $result;
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
        $propObj = new PropPool('public_url', "urn:yandex:disk:meta");

        $result = $this->getProperties($path, $propObj);

        foreach($result as $key => $props)
        {
            if($key !== 'found')
                return false;

            foreach($props as $prop)
                return $prop['value'];
        }

        return $result;
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

        $this->lastResponse = $this->createQuery()
            ->setMethod('GET')
            ->setUrl($this->getPath($path))
            ->setParams(array(
                'preview' => '',
                'size'    => $size
            ))
            ->setInFile($stream)
            ->setResponseHandler(new Get())
            ->exec();

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
        $this->lastResponse = $this->createQuery()
            ->setMethod('GET')
            ->setUrl($this->getPath('/'))
            ->setParams(array(
                'userinfo' => ''
            ))
            ->setResponseHandler(new Get())
            ->exec();

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

        if(!is_resource($stream))
            throw new \Exception('stream is not resource');

        $this->lastResponse = $this->createQuery()
            ->setMethod('GET')
            ->setUrl($this->getPath($path))
            ->setInFile($stream)
            ->setRange(array($from, $to))
            ->setResponseHandler(new Get())
            ->exec();

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

        if(!is_resource($stream))
            throw new \Exception('stream is not resource');

        $streamMeta = stream_get_meta_data($stream);

        $this->lastResponse = $this->createQuery()
            ->setMethod('PUT')
            ->setUrl($this->getPath($path))
            ->setHeaders(array(
                'Etag'          => md5_file($streamMeta['uri']),
                'Sha256'        => hash_file('sha256', $streamMeta['uri']),
                'Content-Type'  => mime_content_type($streamMeta['uri'])
            ))
            ->setFile($stream)
            ->setResponseHandler(new Put())
            ->exec();

        return true;
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

        $this->lastResponse = $this->createQuery()
            ->setMethod('MKCOL')
            ->setUrl($this->getPath($path))
            ->setResponseHandler(new MkCol())
            ->exec();

        return true;
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

        $this->lastResponse = $this->createQuery()
            ->setMethod('COPY')
            ->setUrl($this->getPath($path))
            ->setHeaders(array(
                'Destination'   => $this->correctPath($destination),
                'Overwrite'     => $overwrite
            ))
            ->setResponseHandler(new Copy())
            ->exec();

        return true;
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

        $this->lastResponse = $this->createQuery()
            ->setMethod('MOVE')
            ->setUrl($this->getPath($path))
            ->setHeaders(array(
                'Destination'   => $this->correctPath($destination),
                'Overwrite'     => $overwrite
            ))
            ->setResponseHandler(new Copy())
            ->exec();

        return true;
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

        $this->lastResponse = $this->createQuery()
            ->setMethod('DELETE')
            ->setUrl($this->getPath($path))
            ->setResponseHandler(new Delete())
            ->exec();

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

    /**
     * @deprecated
     * @param $method
     * @param $uri
     * @param $params
     *
     * @return CurlWrapper
     */
    protected function createWrapper($method, $uri, $params)
    {
        return new CurlWrapper($method, $uri, $params, $this->handler);
    }

    /**
     * @deprecated
     * @return array
     */
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

    /**
     * @deprecated
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