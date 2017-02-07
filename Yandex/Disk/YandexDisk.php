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

        $response = new CurlWrapper('PROPFIND', $this->getPath($path), [
            'headers' => [
                'Depth'         => '1',
                'Authorization' => "OAuth {$this->token}"
            ],
            'query'   => [
                'offset' => $offset,
                'amount' => $amount
            ]
        ]);

        $this->lastResponse = $response->exec();

        $decodedBody = $this->getDecode($this->lastResponse->getBody());

        $contents = [];

        foreach($decodedBody as $element)
        {
            if(!$thisFolder && ($element['href'] === $this->correctPath($path)))
                continue;

            $result = $element['propstat']['prop'];

            $result['collection'] = isset($result['resourcetype']['collection']) ? 'dir' : 'file';

            $contents[] = $result;
        }

        return $contents;
    }

    /**
     * получение свободного/занятого места
     * можно получить что-то одно, если указать available/used
     *
     * @link https://tech.yandex.ru/disk/doc/dg/reference/propfind_space-request-docpage/
     *
     * @param string $info
     *
     * @return array|string
     */
    public function spaceInfo($info = '')
    {
        switch($info)
        {
            case 'available':
                $info = "<D:quota-available-bytes/>";
                break;
            case 'used':
                $info = "<D:quota-used-bytes/>";
                break;
            default:
                $info = '<D:quota-available-bytes/><D:quota-used-bytes/>';
                break;
        }

        $response = new CurlWrapper('PROPFIND', $this->getPath('/'), [
            'headers' => [
                'Depth'         => 0,
                'Authorization' => "OAuth {$this->token}"
            ],
            'body'    => "<D:propfind xmlns:D=\"DAV:\">
                                   <D:prop>{$info}</D:prop>
                              </D:propfind>"
        ]);

        $this->lastResponse = $response->exec();

        $decodedBody = $this->getDecode($this->lastResponse->getBody())[0];

        return $decodedBody['propstat']['prop'];
    }

    /**
     * получение свойств файла/каталога, вторым параметром передаётся массив свойств которые нужно вернуть
     * если свойство не найдено, оно не будет добавлено в результирующий массив
     * если оставить свойства пустыми то вернет стандартные свойства элемента как и при запросе содержимого
     *
     * @link https://tech.yandex.ru/disk/doc/dg/reference/propfind_property-request-docpage/
     *
     * @param $path
     * @param array $props
     *
     * @param string $namespace
     *
     * @return array
     * @throws \Exception
     */
    public function getProperties($path, $props = [], $namespace = 'default')
    {
        if(!$path)
            throw new \Exception('path is required parameter');

        if(!is_array($props))
            throw new \Exception('props must be array');

        $body = '';

        foreach($props as $prop)
        {
            $body .= "<{$prop} xmlns=\"{$namespace}\"/>";
        }

        unset($prop);

        $body = $body ? "<prop>{$body}</prop>" : "<allprop/>";

        $body = "<?xml version=\"1.0\" encoding=\"utf-8\" ?><propfind xmlns=\"DAV:\">{$body}</propfind>";

        $response = new CurlWrapper('PROPFIND', $this->getPath($path), [
            'headers' => [
                'Depth'          => 0,
                'Authorization'  => "OAuth {$this->token}",
                'Content-Length' => strlen($body),
                'Content-Type'   => 'application/x-www-form-urlencoded'
            ],
            'body'    => $body
        ]);

        $this->lastResponse = $response->exec();

        $decodedBody = $this->getDecode($this->lastResponse->getBody())[0];

        $arProps = $decodedBody['propstat'];

        $arProps = isset($arProps['status']) ? array($arProps) : $arProps;

        $result = [];

        foreach($arProps as $arProp)
        {
            if(strpos($arProp['status'], '200 OK') === false)
                continue;

            foreach($arProp['prop'] as $key => $prop)
            {
                $result[$key] = $prop;
            }
        }

        return $result;
    }

    /**
     * установка/удаление свойств для файла/папки
     *
     * @link https://tech.yandex.ru/disk/doc/dg/reference/proppatch-docpage/
     *
     * @param $path
     * @param array $props
     * @param string $namespace
     *
     * @return bool|array
     * @throws \Exception
     */
    public function setProperties($path, $props = [], $namespace = 'default')
    {
        if(!$path)
            throw new \Exception('path is required parameter');

        if(!is_array($props))
            throw new \Exception('props must be only array');

        $body = '';
        $set = '';
        $remove = '';

        foreach($props as $key => $value)
        {
            if($value)
                $set .= "<u:{$key}>{$value}</u:{$key}>";
            else
                $remove .= "<u:{$key}/>";
        }

        if($set)
            $body .= "<set><prop>{$set}</prop></set>";

        if($remove)
            $body .= "<remove><prop>{$remove}</prop></remove>";

        if(!$body)
            return false;

        $body = "<?xml version=\"1.0\" encoding=\"utf-8\" ?><propertyupdate xmlns=\"DAV:\" xmlns:u=\"{$namespace}\">{$body}</propertyupdate>";

        $response = new CurlWrapper('PROPPATCH', $this->getPath($path), [
            'headers' => [
                'Authorization'  => "OAuth {$this->token}",
                'Content-Length' => strlen($body),
                'Content-Type'   => 'application/x-www-form-urlencoded'
            ],
            'body'    => $body
        ]);

        $this->lastResponse = $response->exec();

        $decodedBody = $this->getDecode($this->lastResponse->getBody())[0];

        $result = [];

        foreach($decodedBody['propstat']['prop'] as $keyProp => $valueProp)
        {
            if(!$valueProp)
                continue;

            $result[$keyProp] = $valueProp;
        }

        if(strpos($decodedBody['propstat']['status'], '200 OK') === false)
            return false;

        return empty($result) ? true : $result;
    }

    /**
     * Удаление свойств у файла/папки
     *
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
        return $this->setProperties($path, ['public_url' => true], 'urn:yandex:disk:meta')['public_url'];
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

        $response = new CurlWrapper('GET', $this->getPath($path), $options);

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
        $response = new CurlWrapper('GET', $this->getPath('/'), [
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

        $response = new CurlWrapper('GET', $this->getPath($path), [
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

        $response = new CurlWrapper('PUT', $this->getPath($path), [
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

        $response = new CurlWrapper('MKCOL', $this->getPath($path), [
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

        $response = new CurlWrapper('COPY', $this->getPath($path), [
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

        $response = new CurlWrapper('MOVE', $this->getPath($path), [
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

    private function createStream($options)
    {
        $arParams = [];

        foreach($options as $key => $value)
        {
            switch($key)
            {
                case 'path':
                case 'host':
                case 'login':
                case 'password':
                    $arParams[$key] = $value;
                    break;
                //                case 'connect':
                //                    if(gettype($value) != "resource")
                //                        throw new \InvalidArgumentException("{$key} can be only resource");
                //
                //                    $arParams[$key] = $value;
                //                    break;
            }
        }
    }

    private function getDecode($body)
    {
        $dom = new \DOMDocument();

        $dom->loadXML($body);

        $result = [];

        foreach($dom->getElementsByTagName('response') as $element)
        {
            $result[] = $this->getArray($element);
        }

        return $result;
    }

    private function recurseXML($xml, &$result)
    {
        $child_count = 0;

        foreach($xml as $key => $value)
        {
            $child_count++;
            if(!$this->recurseXML($value, $result))
            {
                $result[(string)$key] = (string)$value;
            }
        }

        return $child_count;
    }

    /**
     * @param \DOMNode $node
     *
     * @return bool
     */
    private function getArray($node)
    {
        $array = false;

        if($node->hasChildNodes())
        {
            if($node->childNodes->length == 1)
            {
                if($node->firstChild->nodeType === XML_TEXT_NODE)
                    $array = $node->firstChild->nodeValue;
                else
                    $array[$node->firstChild->localName] = $node->firstChild->nodeValue;
            }
            else
            {
                foreach($node->childNodes as $childNode)
                {
                    if($childNode->nodeType != XML_TEXT_NODE)
                    {
                        $array[$childNode->localName] = $this->getArray($childNode);
                    }
                }
            }
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
    private function correctPath($path)
    {
        $path = trim($path, DIRECTORY_SEPARATOR);

        return DIRECTORY_SEPARATOR . trim($path, DIRECTORY_SEPARATOR);
    }
}