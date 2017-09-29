<?php

namespace Claroline\CoreBundle\API\Serializer;

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.workspace")
 * @DI\Tag("claroline.serializer")
 */
class WorkspaceSerializer
{
    const OPTION_MINIMAL = 'minimal';

    /** @var UserSerializer */
    private $userSerializer;

    /** @var WorkspaceManager */
    private $workspaceManager;

    /**
     * WorkspaceSerializer constructor.
     *
     * @DI\InjectParams({
     *     "userSerializer"   = @DI\Inject("claroline.serializer.user"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     *
     * @param UserSerializer   $userSerializer
     * @param WorkspaceManager $workspaceManager
     */
    public function __construct(
        UserSerializer $userSerializer,
        WorkspaceManager $workspaceManager
    ) {
        $this->userSerializer = $userSerializer;
        $this->workspaceManager = $workspaceManager;
    }

    /**
     * Serializes a Workspace entity for the JSON api.
     *
     * @param Workspace $workspace - the workspace to serialize
     * @param array     $options   - a list of serialization options
     *
     * @return array - the serialized representation of the workspace
     */
    public function serialize(Workspace $workspace, array $options = [])
    {
        $serialized = [
            'id' => $workspace->getId(),
            'uuid' => $workspace->getGuid(), // todo: should be merged with `id`
            'name' => $workspace->getName(),
            'code' => $workspace->getCode(),
            'thumbnail' => null, // todo : add as Workspace prop
        ];

        if (!in_array(static::OPTION_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'poster' => null, // todo : add as Workspace prop
                'meta' => $this->getMeta($workspace),
                'display' => $this->getDisplay($workspace),
                'restrictions' => $this->getRestrictions($workspace),
                'registration' => $this->getRegistration($workspace),
                'roles' => array_map(function (Role $role) {
                    return ['id' => $role->getId(), 'name' => $role->getName()];
                }, $workspace->getRoles()->toArray()),
                'managers' => array_map(function (User $manager) {
                    return $this->userSerializer->serialize($manager);
                }, $this->workspaceManager->getManagers($workspace)),
            ]);
        }

        return $serialized;
    }

    private function getMeta(Workspace $workspace)
    {
        return [
            'model' => $workspace->isModel(),
            'personal' => $workspace->isPersonal(),
            'description' => $workspace->getDescription(),
            'created' => $workspace->getCreated()->format('Y-m-d\TH:i:s'),
            'creator' => $workspace->getCreator() ? $this->userSerializer->serialize($workspace->getCreator()) : null,
        ];
    }

    private function getDisplay(Workspace $workspace)
    {
        return [
            'displayable' => $workspace->isDisplayable(),
        ];
    }

    private function getRestrictions(Workspace $workspace)
    {
        return [
            'accessibleFrom' => $workspace->getStartDate() ? $workspace->getStartDate()->format('Y-m-d\TH:i:s') : null,
            'accessibleUntil' => $workspace->getEndDate() ? $workspace->getEndDate()->format('Y-m-d\TH:i:s') : null,
            'maxUsers' => $workspace->getMaxUsers(),
            'maxStorage' => $workspace->getMaxStorageSize(),
            'maxResources' => $workspace->getMaxUploadResources(),
        ];
    }

    private function getRegistration(Workspace $workspace)
    {
        return [
            'validation' => $workspace->getRegistrationValidation(),
            'selfRegistration' => $workspace->getSelfRegistration(),
            'selfUnregistration' => $workspace->getSelfUnregistration(),
        ];
    }
}
