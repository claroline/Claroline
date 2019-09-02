<?php

namespace Claroline\OpenBadgeBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\OpenBadgeBundle\Entity\SignedBadge;

class SignedBadgeSerializer
{
    use SerializerTrait;

    public function __construct()
    {
    }

    public function serialize(Assertion $assertion)
    {
        return [
            'type' => 'SignedBadge',
            //'id' => $this->router->generate('apiv2_open_badge__assertion', ['assertion' => $assertion->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
    }

    public function getClass()
    {
        return SignedBadge::class;
    }
}
