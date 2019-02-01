<?php

namespace Claroline\OpenBadgeBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 * @DI\Tag("claroline.serializer")
 */
class AlignementObjectSerializer
{
    use SerializerTrait;

    public function __construct()
    {
    }

    public function serialize()
    {
        return [
            'type' => 'AlignementObject',
            //'id' => $this->router->generate('apiv2_open_badge__assertion', ['assertion' => $assertion->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
    }

    public function getClass()
    {
        return self::class;
    }
}
