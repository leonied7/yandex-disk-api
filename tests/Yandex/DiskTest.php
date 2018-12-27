<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 17.01.2018
 * Time: 16:18
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex;

use PHPUnit\Framework\TestCase;
use Leonied7\Yandex\Disk\Collection\PropertyCollection;
use Leonied7\Yandex\Disk\Collection\ResultList;
use Leonied7\Yandex\Disk\Model\Property;

class DiskTest extends TestCase
{
    /**
     * @return array
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function dataProvider()
    {
        $result = [];
        foreach (json_decode(file_get_contents(realpath(__DIR__ . '/../data.json')), true) as $key => $data) {
            $result[$key] = [
                new Disk($data)
            ];
        }
        return $result;
    }

    /**
     * @dataProvider dataProvider
     * @param Disk $disk
     * @covers       Disk::getInfo
     * @requires extension curl
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testGetInfo(Disk $disk)
    {
        $info = $disk->getInfo();
        $result = ResultList::getInstance()->getLast();
        $this->assertNotEmpty($info, $result->getActualResult());
        $this->assertTrue($result->isSuccess(), $result->getActualResult());
    }

    /**
     * @dataProvider dataProvider
     * @param Disk $disk
     * @covers       Disk::spaceInfo
     * @requires extension curl
     * @throws Disk\Exception\InvalidArgumentException
     */
    public function testSpaceInfo(Disk $disk)
    {
        $info = $disk->spaceInfo();
        /** @var PropertyCollection $info */
        $result = ResultList::getInstance()->getLast();
        $this->assertInstanceOf(PropertyCollection::class, $info);
        $this->assertCount(2, $info);

        $available = $info->find('quota-available-bytes');
        $this->assertInstanceOf(Property::class, $available);
        $this->assertEquals(['quota-available-bytes', 'DAV:'], [$available->getName(), $available->getNamespace()]);
        $this->assertGreaterThanOrEqual(0, $available->getValue());

        $used = $info->find('quota-used-bytes');
        $this->assertInstanceOf(Property::class, $used);
        $this->assertEquals(['quota-used-bytes', 'DAV:'], [$used->getName(), $used->getNamespace()]);
        $this->assertGreaterThanOrEqual(0, $used->getValue());

        $this->assertTrue($result->isSuccess(), $result->getActualResult());
    }
}
