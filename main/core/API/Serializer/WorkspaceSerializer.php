<?php

namespace Claroline\CoreBundle\API\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
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
    use SerializerTrait;

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

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'poster' => '', // todo : add as Workspace prop
                'meta' => $this->getMeta($workspace),
                'display' => $this->getDisplay($workspace),
                'restrictions' => $this->getRestrictions($workspace),
                'registration' => $this->getRegistration($workspace),
                'roles' => array_map(function (Role $role) {
                    return ['id' => $role->getId(), 'name' => $role->getName()];
                }, $workspace->getRoles()->toArray()),
                'managers' => array_map(function (User $manager) {
                    return $this->userSerializer->serialize($manager, [Options::SERIALIZE_MINIMAL]);
                }, $this->workspaceManager->getManagers($workspace)),
            ]);
        }

        return $serialized;
    }

    /**
     * @param Workspace $workspace
     *
     * @return array
     */
    private function getMeta(Workspace $workspace)
    {
        return [
            'model' => $workspace->isModel(),
            'personal' => $workspace->isPersonal(),
            'description' => $workspace->getDescription(),
            'created' => $workspace->getCreated()->format('Y-m-d\TH:i:s'),
            'creator' => $workspace->getCreator() ? $this->userSerializer->serialize($workspace->getCreator(), [Options::SERIALIZE_MINIMAL]) : null,
        ];
    }

    /**
     * @param Workspace $workspace
     *
     * @return array
     */
    private function getDisplay(Workspace $workspace)
    {
        return [
            'displayable' => $workspace->isDisplayable(),
        ];
    }

    /**
     * @param Workspace $workspace
     *
     * @return array
     */
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

    /**
     * @param Workspace $workspace
     *
     * @return array
     */
    private function getRegistration(Workspace $workspace)
    {
        return [
            'validation' => $workspace->getRegistrationValidation(),
            'selfRegistration' => $workspace->getSelfRegistration(),
            'selfUnregistration' => $workspace->getSelfUnregistration(),
        ];
    }

    /**
     * @param array     $data
     * @param Workspace $workspace
     * @param array     $options
     */
    public function deserialize(array $data, Workspace $workspace, array $options = [])
    {
        $this->sipe('uuid', 'setUuid', $data, $workspace);
        $this->sipe('code', 'setCode', $data, $workspace);
        $this->sipe('name', 'setName', $data, $workspace);

        $this->sipe('meta.model', 'setIsModel', $data, $workspace);
        $this->sipe('meta.description', 'setDescription', $data, $workspace);

        $this->sipe('display.displayable', 'setDisplayable', $data, $workspace);

        $this->sipe('restrictions.accessibleFrom', 'setStartDate', $data, $workspace);
        $this->sipe('restrictions.accessibleUntil', 'setEndDate', $data, $workspace);
        $this->sipe('restrictions.maxUsers', 'setMaxUsers', $data, $workspace);
        $this->sipe('restrictions.maxStorage', 'setMaxStorageSize', $data, $workspace);
        $this->sipe('restrictions.maxResources', 'setMaxUploadResources', $data, $workspace);

        $this->sipe('registration.validation', 'setRegistrationValidation', $data, $workspace);
        $this->sipe('registration.selfRegistration', 'setSelfRegistration', $data, $workspace);
        $this->sipe('registration.selfUnregistration', 'setSelfUnregistration', $data, $workspace);

        return $workspace;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/main/core/workspace.json';
    }

    /**
     * @return string
     */
    public function getSamples()
    {
        return '#/main/core/workspace';
    }
}
