<?php

namespace Claroline\OpenBadgeBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;

class AlignementObjectSerializer
{
    use SerializerTrait;

    public function serialize()
    {
        return [
            'type' => 'AlignementObject',
            //'id' => $this->router->generate('apiv2_open_badge__assertion', ['assertion' => $assertion->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
    }

    public function deserialize()
    {
    }
}
