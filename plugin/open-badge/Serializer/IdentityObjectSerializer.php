<?php

namespace Claroline\OpenBadgeBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.open_badge.identity_object")
 */
class IdentityObjectSerializer
{
    use SerializerTrait;

    public function serialize(User $user)
    {
        return [
            'identity' => $user->getEmail(),
            'type' => 'email',
            'hashed' => false,
        ];
    }
}
