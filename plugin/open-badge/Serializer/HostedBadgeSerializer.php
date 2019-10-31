<?php

namespace Claroline\OpenBadgeBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;

class HostedBadgeSerializer
{
    use SerializerTrait;

    public function __construct()
    {
    }

    public function getName()
    {
        return 'open_badge_hosted_badge';
    }

    public function serialize()
    {
        return [
            'type' => 'hosted',
            //'id' => $this->router->generate('apiv2_open_badge__assertion', ['assertion' => $assertion->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
    }
}
