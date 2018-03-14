<?php

namespace Claroline\CoreBundle\API\Serializer\User;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Organization\Location;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.pending")
 * @DI\Tag("claroline.serializer")
 */
class PendingSerializer
{
    use SerializerTrait;

    /**
     * UserManager constructor.
     *
     * @DI\InjectParams({
     *     "serializer" = @DI\Inject("claroline.serializer.user")
     * })
     *
     * @param UserSerializer $serializer
     */
    public function __construct(UserSerializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Serialize a Location Entity.
     *
     * @param Location $location
     * @param array    $options
     *
     * @return array
     */
    public function serialize(WorkspaceRegistrationQueue $pending, array $options = [])
    {
        return $this->serializer->serialize($pending->getUser());
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue';
    }
}
