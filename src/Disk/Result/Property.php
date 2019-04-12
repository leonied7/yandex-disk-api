<?php
/**
 * Created by PhpStorm.
 * User: Денис
 * Date: 24.12.2018
 * Time: 11:00
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Result;


use FluidXml\FluidContext;
use FluidXml\FluidXml;
use Leonied7\Yandex\Disk\Collection\PropertyCollection;
use Leonied7\Yandex\Disk\Collection\PropertyFail;
use Leonied7\Yandex\Disk\Entity\Result;
use Leonied7\Yandex\Disk\Http\Response;
use Leonied7\Yandex\Disk\Util\XmlReader;

abstract class Property extends Result
{
    /**
     * {@inheritdoc}
     */
    protected function getGoodCode()
    {
        return [
            207
        ];
    }

    protected function prepareDom(FluidXml $xml)
    {
        $self = $this;
        $result = $xml->query('d:response')->map(function ($key, \DOMElement $response) use ($self) {
            /** @var FluidContext $this */
            return $self->onResponse($this, $response);
        });
        $result = array_column($result, 'result', 'href');
        return $result;
    }

    protected function onResponse(FluidContext $xml, \DOMElement $response)
    {
        $href = urldecode(XmlReader::getValueByTag($response, 'href'));
        $applyCollection = new PropertyCollection();

        $fails = $this->propStat($xml, $applyCollection, $response);

        $result = [];
        $result['apply'] = $applyCollection;
        if (!empty($fails)) {
            $result['fail'] = array_values($fails);
        }
        return [
            'href' => $href,
            'result' => $result
        ];
    }

    protected function onPropStat(FluidContext $xml, \DOMElement $propertyStat, PropertyCollection $applyCollection)
    {
        $status = XmlReader::getValueByTag($propertyStat, 'status');
        $fail = null;
        $properties = $this->getProperties($xml);

        if (strpos($status, Response::OK_STATUS) === false) {
            $fail = $this->onFailStatus($status, $properties);
        } else {
            $this->onSuccessStatus($properties, $applyCollection);
        }

        return $fail;
    }

    protected function onFailStatus($status, array $properties)
    {
        $fail = new PropertyFail($status);

        foreach ($properties as $property) {
            $this->addToCollection($fail, $property);
        }

        return $fail;
    }

    protected function onSuccessStatus(array $properties, PropertyCollection $collection)
    {
        foreach ($properties as $property) {
            $this->addToCollection($collection, $property);
        }
    }

    protected function addToCollection(\Leonied7\Yandex\Disk\Entity\PropertyCollection $collection, array $property)
    {
        $collection->add($property['name'], $property['namespace'], $property['value']);
    }

    private function getProperties(FluidContext $xml)
    {
        return $xml->query('d:prop')->map(function ($propertyKey, \DOMElement $property) {
            return XmlReader::getArray($property)['children'];
        })[0];
    }

    private function propStat(FluidContext $xml, PropertyCollection $applyCollection, \DOMElement $response)
    {
        $self = $this;
        $fails = $xml->query('d:propstat')->map(function ($keyStat, \DOMElement $propertyStat) use ($applyCollection, $self) {
            /** @var FluidContext $this */
            return $self->onPropStat($this, $propertyStat, $applyCollection);
        });
        $fails = array_filter($fails);
        return $fails;
    }
}