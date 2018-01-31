<?php

namespace Claroline\TeamBundle\API\Serializer;

use Claroline\CoreBundle\API\Serializer\User\RoleSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\TeamBundle\Entity\Team;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.team")
 * @DI\Tag("claroline.serializer")
 */
class TeamSerializer
{
    private $roleSerializer;
    private $userSerializer;

    /**
     * TeamSerializer constructor.
     *
     * @DI\InjectParams({
     *     "roleSerializer" = @DI\Inject("claroline.serializer.role"),
     *     "userSerializer" = @DI\Inject("claroline.serializer.user")
     * })
     *
     * @param RoleSerializer $roleSerializer
     * @param UserSerializer $userSerializer
     */
    public function __construct(RoleSerializer $roleSerializer, UserSerializer $userSerializer)
    {
        $this->roleSerializer = $roleSerializer;
        $this->userSerializer = $userSerializer;
    }

    /**
     * @param Team $team
     *
     * @return array
     */
    public function serialize(Team $team)
    {
        return [
            'id' => $team->getId(),
            'name' => $team->getName(),
            'description' => $team->getDescription(),
            'workspace' => $team->getWorkspace()->getId(),
            'role' => $team->getRole() ? $this->roleSerializer->serialize($team->getRole()) : null,
            'teamManager' => $team->getTeamManager() ? $this->userSerializer->serialize($team->getTeamManager()) : null,
            'teamManagerRole' => $team->getTeamManagerRole() ? $this->roleSerializer->serialize($team->getTeamManagerRole()) : null,
            'maxUsers' => $team->getMaxUsers(),
            'selfRegistration' => $team->getSelfRegistration(),
            'selfUnregistration' => $team->getSelfUnregistration(),
            'directory' => $team->getDirectory() ? $team->getDirectory()->getId() : null,
            'isPublic' => $team->getIsPublic(),
        ];
    }
}
