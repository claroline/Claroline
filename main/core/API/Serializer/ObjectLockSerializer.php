<?php

namespace Claroline\CoreBundle\API\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\ObjectLock;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.object_lock")
 * @DI\Tag("claroline.serializer")
 */
class ObjectLockSerializer
{
    /**
     * @DI\InjectParams({
     *     "userSerializer" = @DI\Inject("claroline.serializer.user")
     * })
     */
    public function __construct(
        UserSerializer $userSerializer
    ) {
        $this->userSerializer = $userSerializer;
    }

    public function getClass()
    {
        return ObjectLock::class;
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
