<?php

namespace Claroline\CoreBundle\API\Serializer\Workspace;

use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue;

class PendingRegistrationSerializer
{
    /** @var UserSerializer */
    private $serializer;

    /**
     * PendingRegistrationSerializer constructor.
     */
    public function __construct(UserSerializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue';
    }

    public function getName()
    {
        return 'workspace_registration_queue';
    }

    /**
     * Serialize a user waiting for WS registration.
     *
     * @return array
     */
    public function serialize(WorkspaceRegistrationQueue $pending, array $options = [])
    {
        return $this->serializer->serialize($pending->getUser());
    }
}
