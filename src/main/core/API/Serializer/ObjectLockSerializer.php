<?php

namespace Claroline\CoreBundle\API\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Entity\ObjectLock;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;

class ObjectLockSerializer
{
    private UserSerializer $userSerializer;

    public function __construct(
        UserSerializer $userSerializer
    ) {
        $this->userSerializer = $userSerializer;
    }

    public function getClass(): string
    {
        return ObjectLock::class;
    }

    public function getName(): string
    {
        return 'object_lock';
    }

    public function serialize(ObjectLock $lock): array
    {
        return [
          'user' => $this->userSerializer->serialize($lock->getUser(), [Options::SERIALIZE_MINIMAL]),
          'value' => $lock->isLocked(),
          'updated' => DateNormalizer::normalize($lock->getLastModification()),
          'className' => $lock->getObjectClass(),
          'id' => $lock->getObjectUuid(),
        ];
    }
}
