<?php

namespace Claroline\CoreBundle\API\Serializer;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.location")
 * @DI\Tag("claroline.serializer")
 */
class LocationSerializer
{
    use SerializerTrait;

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Organization\Location';
    }
}
