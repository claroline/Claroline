<?php

namespace Icap\WebsiteBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Icap\WebsiteBundle\Entity\Website;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.website")
 * @DI\Tag("claroline.serializer")
 */
class WebsiteSerializer
{
    use SerializerTrait;

    /**
     * @param Website $website
     *
     * @return array - The serialized representation of a website
     *               //TODO For the moment only serialize uuid to get Website working with new resource manager
     */
    public function serialize(Website $website)
    {
        return [
            'id' => $website->getUuid(),
        ];
    }
}
