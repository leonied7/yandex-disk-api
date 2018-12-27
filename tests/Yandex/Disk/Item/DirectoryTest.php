<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 22.01.2018
 * Time: 8:05
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Item;

use Leonied7\Yandex\Disk;
use PHPUnit\Framework\TestCase;
use Leonied7\Yandex\Disk\Collection\PropertyFail;
use Leonied7\Yandex\Disk\Collection\ResultList;
use Leonied7\Yandex\Disk\Decorator\CurrentElementFailCollection;
use Leonied7\Yandex\Disk\Collection\PropertyCollection;
use Leonied7\Yandex\Disk\Property\Mutable;

class DirectoryTest extends TestCase
{
    protected $class = __CLASS__;

    /**
     * @return array
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function dataProvider()
    {
        $result = [];
        foreach (json_decode(file_get_contents(realpath(__DIR__ . '/../../../data.json')), true) as $key => $data) {
            $result[$key] = [
                new Disk($data)
            ];
        }
        return $result;
    }

    public function getTestedDirectory(Disk $disk)
    {
        return "/{$disk->getQueryData()->getToken()}/";
    }

    public function getTestedCopyDirectory(Disk $disk)
    {
        return "/{$disk->getQueryData()->getToken()}_copy/";
    }

    /**
     * @dataProvider dataProvider
     * @covers       Directory::create
     * @param Disk $disk
     * @requires extension curl
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testCreate(Disk $disk)
    {
        $directory = $disk->directory($this->getTestedDirectory($disk));
        if($directory->has()) {
            $this->assertTrue($directory->delete(), ResultList::getInstance()->getLast()->getActualResult());
        }
        $this->assertTrue($directory->create(), ResultList::getInstance()->getLast()->getActualResult());
    }

    /**
     * @dataProvider dataProvider
     * @covers       Directory::has
     * @param Disk $disk
     * @depends      testCreate
     * @requires extension curl
     * @requires extension mbstring
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testHas(Disk $disk)
    {
        $directory = $disk->directory($this->getTestedDirectory($disk));

        $this->assertTrue($directory->has(), ResultList::getInstance()->getLast()->getActualResult());
        $this->assertNotCount(1, $directory->getProperties());
        $this->assertGreaterThan(0, count($directory->getProperties()));

        $collection = new PropertyCollection();
        $collection
            ->add('getcontenttype', 'DAV:')
            ->add('displayname', 'DAV:')
            ->add('myprop', 'mynamespace');
        $this->assertTrue($directory->has($collection), ResultList::getInstance()->getLast()->getActualResult());
        $this->assertCount(1, $directory->getProperties());
        /** @var PropertyFail[] $convertedResult */
        $convertedResult = ResultList::getInstance()->getLast()->getDecorateResult(new CurrentElementFailCollection($directory->getPath()));
        foreach ($convertedResult as $failCollection) {
            if (!$myProperty = $failCollection->find('myprop', 'mynamespace')) {
                print_r($convertedResult);
                $this->fail('ошибка коллекции');
            }

            $this->assertContains('404', $failCollection->getStatus());
            $this->assertEquals(['myprop', 'mynamespace'], [$myProperty->getName(), $myProperty->getNamespace()]);
        }

        $this->assertTrue($directory->has(), ResultList::getInstance()->getLast()->getActualResult());
        $this->assertCount(1, $directory->getProperties());
    }

    /**
     * @dataProvider dataProvider
     * @covers       Directory::copy
     * @param Disk $disk
     * @depends      testCreate
     * @requires extension curl
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testCopy(Disk $disk)
    {
        $directory = $disk->directory($this->getTestedDirectory($disk));
        $this->assertTrue($directory->copy($this->getTestedCopyDirectory($disk)), print_r(ResultList::getInstance()->getLast(), true));
    }

    /**
     * @dataProvider dataProvider
     * @covers       Directory::move
     * @param Disk $disk
     * @depends      testCreate
     * @requires extension curl
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testMove(Disk $disk)
    {
        $directory = $disk->directory($this->getTestedCopyDirectory($disk));
        $this->assertTrue($directory->move($this->getTestedDirectory($disk)), print_r(ResultList::getInstance()->getLast(), true));
        $this->assertEquals($this->getTestedDirectory($disk), $directory->getPath());
    }

    /**
     * @dataProvider dataProvider
     * @param Disk $disk
     * @depends      testCreate
     * @requires extension curl
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testProperties(Disk $disk)
    {
        $directory = $disk->directory($this->getTestedDirectory($disk));
        $propertyCollection = $directory->getExistProperties();
        $this->assertTrue(ResultList::getInstance()->getLast()->isSuccess(), print_r(ResultList::getInstance()->getLast(), true));
        $this->assertInstanceOf(PropertyCollection::class, $propertyCollection, print_r(ResultList::getInstance()->getLast(), true));
        $this->assertGreaterThan(0, count($propertyCollection), print_r(ResultList::getInstance()->getLast(), true));
        $this->assertCount(0, $directory->getProperties());

        $propertyCollection = new PropertyCollection();
        $propertyCollection
            ->add('myprop', 'mynamespace', 'foo')
            ->add('propmy', 'mynamespace', 'bar')
            ->add('propprop', 'mynamespace');

        $this->assertTrue($directory->changeProperties($propertyCollection), ResultList::getInstance()->getLast()->getActualResult());
        $this->assertGreaterThan(0, count(ResultList::getInstance()->getLast()->getResult()), ResultList::getInstance()->getLast()->getActualResult());
        $loadCollection = $directory->loadProperties($propertyCollection);
        $this->assertTrue(ResultList::getInstance()->getLast()->isSuccess(), ResultList::getInstance()->getLast()->getActualResult());
        $this->assertCount(2, $loadCollection);
        /** @var Mutable $myProp */
        $myProp = $loadCollection->find('myprop');
        $myProp->setValue('baz');
        $loadCollection
            ->add('propprop', 'mynamespace', 'foo');

        $this->assertEquals($loadCollection, $directory->getProperties());
        $this->assertTrue($directory->saveProperties(), print_r(ResultList::getInstance()->getLast(), true));
    }

    /**
     * @dataProvider dataProvider
     * @covers       Directory::startPublish
     * @param Disk $disk
     * @depends      testCreate
     * @requires extension curl
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testStartPublish(Disk $disk)
    {
        $directory = $disk->directory($this->getTestedDirectory($disk));
        $this->assertTrue($directory->startPublish(), ResultList::getInstance()->getLast()->getActualResult());
        $this->assertNotEmpty(ResultList::getInstance()->getLast()->getResult(), ResultList::getInstance()->getLast()->getActualResult());
        $this->assertTrue(ResultList::getInstance()->getLast()->isSuccess(), ResultList::getInstance()->getLast()->getActualResult());
    }

    /**
     * @dataProvider dataProvider
     * @covers       Directory::checkPublish
     * @param Disk $disk
     * @depends      testStartPublish
     * @requires extension curl
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testCheckPublish(Disk $disk)
    {
        $directory = $disk->directory($this->getTestedDirectory($disk));
        $this->assertTrue($directory->checkPublish(), ResultList::getInstance()->getLast()->getActualResult());
        $this->assertNotEmpty(ResultList::getInstance()->getLast()->getResult(), ResultList::getInstance()->getLast()->getActualResult());
        $this->assertTrue(ResultList::getInstance()->getLast()->isSuccess(), ResultList::getInstance()->getLast()->getActualResult());
    }

    /**
     * @dataProvider dataProvider
     * @covers       Directory::stopPublish
     * @param Disk $disk
     * @depends      testCheckPublish
     * @requires extension curl
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testStopPublish(Disk $disk)
    {
        $directory = $disk->directory($this->getTestedDirectory($disk));
        $this->assertTrue($directory->stopPublish(), ResultList::getInstance()->getLast()->getActualResult());
        $this->assertEmpty(ResultList::getInstance()->getLast()->getResult(), ResultList::getInstance()->getLast()->getActualResult());
        $this->assertTrue(ResultList::getInstance()->getLast()->isSuccess(), ResultList::getInstance()->getLast()->getActualResult());
    }

    /**
     * @dataProvider dataProvider
     * @covers       Directory::getChildren
     * @param Disk $disk
     * @depends      testCreate
     * @requires extension curl
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testGetChildren(Disk $disk)
    {
        $directory = $disk->directory($this->getTestedDirectory($disk));
        $directoryResult = ResultList::getInstance()->getLast();
        $subDirectory = $disk->directory($this->getTestedDirectory($disk) . 'test/');
        $subDirectoryResult = ResultList::getInstance()->getLast();
        $this->assertTrue($subDirectory->create(), $subDirectoryResult->getActualResult());
        $arChild = $directory->getChildren();
        $this->assertTrue($directoryResult->isSuccess(), $directoryResult->getActualResult());
        $this->assertCount(1, $arChild);

        foreach ($arChild as $child)
        {
            $this->assertEquals('directory', $child->getType());
            $this->assertGreaterThan(0, count($child->getProperties()));
        }
    }

    /**
     * @dataProvider dataProvider
     * @covers       Directory::delete
     * @param Disk $disk
     * @depends      testCreate
     * @requires extension curl
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testDelete(Disk $disk)
    {
        $directory = $disk->directory($this->getTestedDirectory($disk));
        $this->assertTrue($directory->delete(), ResultList::getInstance()->getLast()->getActualResult());
    }
}
