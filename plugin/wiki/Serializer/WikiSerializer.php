<?php

namespace Icap\WikiBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Icap\WikiBundle\Entity\Wiki;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.wiki")
 * @DI\Tag("claroline.serializer")
 */
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
     * @param Wiki $wiki
     *
     * @return array - The serialized representation of a contribution
     */
    public function serialize(Wiki $wiki)
    {
        return [
            'id' => $wiki->getUuid(),
            'mode' => null === $wiki->getMode() ? '0' : ''.$wiki->getMode(),
            'display' => [
                'sectionNumbers' => null === $wiki->getDisplaySectionNumbers() ?
                    false :
                    $wiki->getDisplaySectionNumbers(),
                'contents' => null === $wiki->getDisplayContents() ? true : $wiki->getDisplayContents(),
            ],
        ];
    }

    public function deserialize($data, Wiki $wiki = null)
    {
        if (empty($wiki)) {
            $wiki = new Wiki();
        }
        $this->sipe('id', 'setUuid', $data, $wiki);
        $this->sipe('mode', 'setMode', $data, $wiki);
        if (isset($data['display']['sectionNumbers'])) {
            $wiki->setDisplaySectionNumbers($data['display']['sectionNumbers']);
        }

        if (isset($data['display']['contents'])) {
            $wiki->setDisplayContents($data['display']['contents']);
        }

        return $wiki;
    }
}
