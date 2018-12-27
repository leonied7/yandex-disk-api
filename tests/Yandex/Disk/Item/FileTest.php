<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 19.01.2018
 * Time: 13:19
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Item;

use Leonied7\Yandex\Disk;
use PHPUnit\Framework\TestCase;
use Leonied7\Yandex\Disk\Collection\PropertyCollection;
use Leonied7\Yandex\Disk\Collection\PropertyFail;
use Leonied7\Yandex\Disk\Collection\ResultList;
use Leonied7\Yandex\Disk\Decorator\CurrentElementFailCollection;
use Leonied7\Yandex\Disk\Property\Mutable;
use Leonied7\Yandex\Disk\Stream\File as FileStream;

class FileTest extends TestCase
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

    public function getReadFile()
    {
        return __DIR__ . '/../../../file.txt';
    }

    public function getWriteFile(Disk $disk)
    {
        return __DIR__ . "/../../../{$disk->getQueryData()->getToken()}.txt";
    }

    public function getTestedFile(Disk $disk)
    {
        return "/{$disk->getQueryData()->getToken()}_file.txt";
    }

    public function getReadJpg()
    {
        return __DIR__ . '/../../../file.jpg';
    }

    public function getWriteJpg(Disk $disk)
    {
        return __DIR__ . "/../../../{$disk->getQueryData()->getToken()}.jpg";
    }

    public function getTestedJpg(Disk $disk)
    {
        return "/{$disk->getQueryData()->getToken()}.jpg";
    }

    /**
     * @dataProvider dataProvider
     * @covers       File::upload
     * @param Disk $disk
     * @requires extension curl
     * @requires extension fileinfo
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testUpload(Disk $disk)
    {
        $file = $disk->file($this->getTestedFile($disk));
        $this->assertTrue($file->upload(new FileStream($this->getReadFile(), FileStream::MODE_READ)), print_r(ResultList::getInstance()->getLast(), true));
    }

    /**
     * @dataProvider dataProvider
     * @covers       File::has
     * @param Disk $disk
     * @depends      testUpload
     * @requires extension curl
     * @requires extension mbstring
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testHas(Disk $disk)
    {
        $file = $disk->file($this->getTestedFile($disk));

        $this->assertTrue($file->has(), ResultList::getInstance()->getLast()->getActualResult());
        $this->assertNotCount(2, $file->getProperties());
        $this->assertGreaterThan(0, count($file->getProperties()));

        $collection = new PropertyCollection();
        $collection
            ->add('getcontenttype', 'DAV:')
            ->add('displayname', 'DAV:')
            ->add('myprop', 'mynamespace');
        $this->assertTrue($file->has($collection), ResultList::getInstance()->getLast()->getActualResult());
        $this->assertCount(2, $file->getProperties());
        /** @var PropertyFail[] $convertedResult */
        $convertedResult = ResultList::getInstance()->getLast()->getDecorateResult(new CurrentElementFailCollection($file->getPath()));
        foreach ($convertedResult as $failCollection) {
            if (!$myProperty = $failCollection->find('myprop', 'mynamespace')) {
                print_r($convertedResult);
                $this->fail('ошибка коллекции');
            }

            foreach ($failCollection as $property) {

            }

            $this->assertContains('404', $failCollection->getStatus());
            $this->assertEquals(['myprop', 'mynamespace'], [$myProperty->getName(), $myProperty->getNamespace()]);
        }

        $this->assertTrue($file->has(), ResultList::getInstance()->getLast()->getActualResult());
        $this->assertCount(2, $file->getProperties());
    }

    /**
     * @dataProvider dataProvider
     * @covers       File::copy
     * @param Disk $disk
     * @depends      testUpload
     * @requires extension curl
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testCopy(Disk $disk)
    {
        $file = $disk->file($this->getTestedFile($disk));
        $this->assertTrue($file->copy($this->getTestedFile($disk) . '_copy', false), print_r(ResultList::getInstance()->getLast(), true));
    }

    /**
     * @dataProvider dataProvider
     * @covers       File::move
     * @param Disk $disk
     * @depends      testCopy
     * @requires extension curl
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testMove(Disk $disk)
    {
        $file = $disk->file($this->getTestedFile($disk) . '_copy');
        $this->assertTrue($file->move($this->getTestedFile($disk)), print_r(ResultList::getInstance()->getLast(), true));
        $this->assertEquals($this->getTestedFile($disk), $file->getPath());
    }

    /**
     * @dataProvider dataProvider
     * @covers       File::upload
     * @param Disk $disk
     * @depends      testUpload
     * @requires extension curl
     * @requires extension fileinfo
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testDownload(Disk $disk)
    {
        $file = $disk->file($this->getTestedFile($disk));
        $fileName = $this->getWriteFile($disk);
        $this->assertTrue($file->download(new FileStream($fileName, FileStream::MODE_WRITE)), 'код ответ: ' . ResultList::getInstance()->getLast()->getResponse()->getCode());
        $this->assertFileEquals($this->getReadFile(), $this->getWriteFile($disk));
    }

    /**
     * @dataProvider dataProvider
     * @covers       File::upload
     * @param Disk $disk
     * @depends      testUpload
     * @requires extension curl
     * @requires extension fileinfo
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testPartDownload(Disk $disk)
    {
        $file = $disk->file($this->getTestedFile($disk));
        $fileName = $this->getWriteFile($disk);
        $this->assertTrue($file->download(new FileStream($fileName, FileStream::MODE_WRITE), 0, 5), 'код ответ: ' . ResultList::getInstance()->getLast()->getResponse()->getCode());
        $this->assertTrue($file->download(new FileStream($fileName, FileStream::MODE_WRITE_APPEND), 6), 'код ответ: ' . ResultList::getInstance()->getLast()->getResponse()->getCode());
        $this->assertFileEquals($this->getReadFile(), $this->getWriteFile($disk));
    }

    /**
     * @dataProvider dataProvider
     * @covers       File::upload
     * @param Disk $disk
     * @depends      testUpload
     * @requires extension curl
     * @requires extension fileinfo
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testDownloadWithOutSteam(Disk $disk)
    {
        $file = $disk->file($this->getTestedFile($disk));
        $fileName = $this->getWriteFile($disk);
        $this->assertTrue($file->download(), 'код ответ: ' . ResultList::getInstance()->getLast()->getResponse()->getCode());
        file_put_contents($fileName, ResultList::getInstance()->getLast()->getActualResult());
        $this->assertFileEquals($this->getReadFile(), $this->getWriteFile($disk));
    }

    /**
     * @dataProvider dataProvider
     * @param Disk $disk
     * @depends      testUpload
     * @requires extension curl
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testProperties(Disk $disk)
    {
        $file = $disk->file($this->getTestedFile($disk));
        $propertyCollection = $file->getExistProperties();
        $this->assertTrue(ResultList::getInstance()->getLast()->isSuccess(), ResultList::getInstance()->getLast()->getActualResult());
        $this->assertInstanceOf(PropertyCollection::class, $propertyCollection);
        $this->assertGreaterThan(0, count($propertyCollection));
        $this->assertCount(0, $file->getProperties());

        $propertyCollection = new PropertyCollection();
        $propertyCollection
            ->add('myprop', 'mynamespace', 'foo')
            ->add('propmy', 'mynamespace', 'bar')
            ->add('propprop', 'mynamespace');

        $this->assertTrue($file->changeProperties($propertyCollection), ResultList::getInstance()->getLast()->getActualResult());

        $loadCollection = $file->loadProperties($propertyCollection);
        $this->assertTrue(ResultList::getInstance()->getLast()->isSuccess(), ResultList::getInstance()->getLast()->getActualResult());
        $this->assertCount(2, $loadCollection);
        /** @var Mutable $myProp */
        $myProp = $loadCollection->find('myprop');
        $myProp->setValue('baz');
        $loadCollection
            ->add('propprop', 'mynamespace', 'foo');

        $this->assertEquals($loadCollection, $file->getProperties());
        $this->assertTrue($file->saveProperties(), ResultList::getInstance()->getLast()->getActualResult());
    }

    /**
     * @dataProvider dataProvider
     * @covers       File::startPublish
     * @param Disk $disk
     * @depends      testUpload
     * @requires extension curl
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testStartPublish(Disk $disk)
    {
        $file = $disk->file($this->getTestedFile($disk));
        $this->assertTrue($file->startPublish(), ResultList::getInstance()->getLast()->getActualResult());
        $this->assertNotEmpty(ResultList::getInstance()->getLast()->getResult(), ResultList::getInstance()->getLast()->getActualResult());
        $this->assertTrue(ResultList::getInstance()->getLast()->isSuccess(), ResultList::getInstance()->getLast()->getActualResult());
    }

    /**
     * @dataProvider dataProvider
     * @covers       File::checkPublish
     * @param Disk $disk
     * @depends      testStartPublish
     * @requires extension curl
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testCheckPublish(Disk $disk)
    {
        $file = $disk->file($this->getTestedFile($disk));
        $this->assertTrue($file->checkPublish(), ResultList::getInstance()->getLast()->getActualResult());
        $this->assertNotEmpty(ResultList::getInstance()->getLast()->getResult(), ResultList::getInstance()->getLast()->getActualResult());
        $this->assertTrue(ResultList::getInstance()->getLast()->isSuccess(), ResultList::getInstance()->getLast()->getActualResult());
    }

    /**
     * @dataProvider dataProvider
     * @covers       File::stopPublish
     * @param Disk $disk
     * @depends      testCheckPublish
     * @requires extension curl
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testStopPublish(Disk $disk)
    {
        $file = $disk->file($this->getTestedFile($disk));
        $this->assertTrue($file->stopPublish(), ResultList::getInstance()->getLast()->getActualResult());
        $this->assertEmpty(ResultList::getInstance()->getLast()->getResult(), ResultList::getInstance()->getLast()->getActualResult());
        $this->assertTrue(ResultList::getInstance()->getLast()->isSuccess(), ResultList::getInstance()->getLast()->getActualResult());
    }

    /**
     * @dataProvider dataProvider
     * @covers       File::delete
     * @param Disk $disk
     * @depends      testUpload
     * @requires extension curl
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testDelete(Disk $disk)
    {
        $file = $disk->file($this->getTestedFile($disk));
        $this->assertTrue($file->delete(), ResultList::getInstance()->getLast()->getActualResult());
    }

    /**
     * @dataProvider dataProvider
     * @covers       File::getPreview
     * @param Disk $disk
     * @depends      testUpload
     * @requires extension curl
     * @requires extension fileinfo
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testGetPreview(Disk $disk)
    {
        $file = $disk->file($this->getTestedJpg($disk));
        $this->assertTrue($file->upload(new FileStream($this->getReadJpg(), FileStream::MODE_READ)), print_r(ResultList::getInstance()->getLast(), true));
        sleep(5); //без этого Яндекс отдаёт иногда 404 ошибку
        $this->assertTrue($file->getPreview('S', new FileStream($this->getWriteJpg($disk), FileStream::MODE_WRITE)), print_r(ResultList::getInstance()->getLast(), true));
        $this->assertTrue($file->delete(), ResultList::getInstance()->getLast()->getActualResult());
        unlink($this->getWriteJpg($disk));
        unlink($this->getWriteFile($disk));
    }
}
