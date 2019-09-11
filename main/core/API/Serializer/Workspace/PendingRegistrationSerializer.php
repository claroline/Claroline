<?php

namespace Claroline\CoreBundle\API\Serializer\Workspace;

use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue;

class PendingRegistrationSerializer
{
    /** @var UserSerializer */
    private $serializer;

    /**
     * PendingRegistrationSerializer constructor.
     *
     * @param UserSerializer $serializer
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

    /**
     * Serialize a user waiting for WS registration.
     *
     * @param WorkspaceRegistrationQueue $pending
     * @param array                      $options
     *
     * @return array
     */
    public function serialize(WorkspaceRegistrationQueue $pending, array $options = [])
    {
        return $this->serializer->serialize($pending->getUser());
    }
}
