<?php

namespace Icap\WikiBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Icap\WikiBundle\Entity\Wiki;

class WikiSerializer
{
    use SerializerTrait;

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/wiki/wiki.json';
    }

    /**
     * @return array - The serialized representation of a wiki
     */
    public function serialize(Wiki $wiki)
    {
        return [
            'id' => $wiki->getUuid(),
            'mode' => $this->getModeStringValue($wiki->getMode()),
            'display' => [
                'sectionNumbers' => null === $wiki->getDisplaySectionNumbers() ?
                    false :
                    $wiki->getDisplaySectionNumbers(),
                'contents' => null === $wiki->getDisplayContents() ? true : $wiki->getDisplayContents(),
            ],
        ];
    }

    public function getName()
    {
        return 'wiki';
    }

    private function getModeStringValue($value)
    {
        switch ($value) {
            case 0:
                $strVal = 'normal';
                break;
            case 1:
                $strVal = 'moderate';
                break;
            case 2:
                $strVal = 'read_only';
                break;
            default:
                $strVal = 'normal';
        }

        return $strVal;
    }

    private function getModeIntValue($value)
    {
        switch ($value) {
            case 'normal':
                $intVal = 0;
                break;
            case 'moderate':
                $intVal = 1;
                break;
            case 'read_only':
                $intVal = 2;
                break;
            default:
                $intVal = 0;
        }

        return $intVal;
    }

    /**
     * @param $data
     *
     * @return Wiki - The deserialized wiki object
     */
    public function deserialize($data, Wiki $wiki = null, array $options = [])
    {
        if (empty($wiki)) {
            $wiki = new Wiki();
        }

        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $wiki);
        }

        if (isset($data['mode'])) {
            $wiki->setMode($this->getModeIntValue($data['mode']));
        }
        if (isset($data['display']['sectionNumbers'])) {
            $wiki->setDisplaySectionNumbers($data['display']['sectionNumbers']);
        }
        if (isset($data['display']['contents'])) {
            $wiki->setDisplayContents($data['display']['contents']);
        }

        return $wiki;
    }
}
