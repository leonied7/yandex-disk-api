<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 16.01.2018
 * Time: 14:46
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Item;

use Leonied7\Yandex\Disk\Collection\PropertyCollection;
use Leonied7\Yandex\Disk\Query\Data as QueryData;

/**
 * Class Directory Главный класс для работы с директорией
 * @package Leonied7\Yandex\Disk\Item
 * @method \Leonied7\Yandex\Disk\Builder\Directory getBuilder()
 */
class Directory extends Item
{
    /**
     * Directory constructor.
     * @param $path
     * @param QueryData $queryData
     * @param PropertyCollection|null $property
     * @throws \Leonied7\Yandex\Disk\Exception\InvalidArgumentException
     */
    public function __construct($path, QueryData $queryData, PropertyCollection $property = null)
    {
        $this->type = self::DIRECTORY;
        parent::__construct($path, $queryData, $property);
    }

    public function getPath()
    {
        return rtrim(parent::getPath(), '/') . '/';
    }

    /**
     * Создает директорию на Яндекс-диске
     * @link https://tech.yandex.ru/disk/doc/dg/reference/mkcol-docpage/
     * @return bool
     * @throws \Leonied7\Yandex\Disk\Exception\InvalidArgumentException
     */
    public function create()
    {
        return $this->getBuilder()->create()->send()->isSuccess();
    }

    /**
     * получение содержимого директории с Яндекс-диска
     * @link https://tech.yandex.ru/disk/doc/dg/reference/propfind_contains-request-docpage/
     *
     * @param PropertyCollection|null $propertyCollection - выбираемые свойства для содержимого (и текущей директории, если свойства не заполнены или пустые)
     * @param int $offset - смещения выборки
     * @param int $amount - количество выбираемых элементов
     * @return Item[]
     * @throws \Leonied7\Yandex\Disk\Exception\InvalidArgumentException
     */
    public function getChildren(PropertyCollection $propertyCollection = null, $offset = 0, $amount = null)
    {
        $child = [];
        $result = $this->getBuilder()->getChildren($propertyCollection, $offset, $amount)->send();

        if(!$result->isSuccess()) {
            return $child;
        }

        /** @var array $responseResult */
        $responseResult = $result->getActualResult();

        if(isset($responseResult[$this->getPath()])) {
            if(count($this->getProperties()) === 0) {
                $this->properties = $responseResult[$this->getPath()]['apply'];
            }
            unset($responseResult[$this->getPath()]);
        }

        foreach ($responseResult as $name => $element)
        {
            if($name === QueryData::correctUrl($name)) {
                $child[] = new File($name, $this->getQueryData(), $element['apply']);
            } else {
                $child[] = new Directory($name, $this->getQueryData(), $element['apply']);
            }
        }

        return $child;
    }

    /**
     * @see getBuilder
     * @return \Leonied7\Yandex\Disk\Builder\Directory
     */
    protected function createBuilder()
    {
        return new \Leonied7\Yandex\Disk\Builder\Directory($this);
    }
}