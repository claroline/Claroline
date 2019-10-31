<?php

namespace Claroline\OpenBadgeBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;

class RevocationListSerializer
{
    use SerializerTrait;

    public function __construct()
    {
    }

    public function getName()
    {
        return 'open_badge_revocation_list';
    }

    public function serialize($assertion)
    {
        return [
            'type' => 'RevocationList',
            //'id' => $this->router->generate('apiv2_open_badge__assertion', ['assertion' => $assertion->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
    }
}
