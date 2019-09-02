<?php

namespace Claroline\OpenBadgeBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\User;

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
