<?php

namespace Claroline\TeamBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\API\Serializer\User\RoleSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\TeamBundle\Entity\Team;

class TeamSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var ResourceNodeSerializer */
    private $resourceNodeSerializer;
    /** @var RoleSerializer */
    private $roleSerializer;
    /** @var WorkspaceSerializer */
    private $workspaceSerializer;

    public function __construct(
        ObjectManager $om,
        ResourceNodeSerializer $resourceNodeSerializer,
        RoleSerializer $roleSerializer,
        WorkspaceSerializer $workspaceSerializer
    ) {
        $this->om = $om;
        $this->resourceNodeSerializer = $resourceNodeSerializer;
        $this->roleSerializer = $roleSerializer;
        $this->workspaceSerializer = $workspaceSerializer;
    }

    public function getName()
    {
        return 'team';
    }

    public function serialize(Team $team, array $options = []): array
    {
        $users = $team->getRole() ? $team->getRole()->getUsers()->toArray() : $team->getUsers()->toArray();

        $serialized = [
            'id' => $team->getUuid(),
            'name' => $team->getName(),
            'description' => $team->getDescription(),
            'maxUsers' => $team->getMaxUsers(),
            'countUsers' => count($users),
            'registration' => [
                'selfRegistration' => $team->isSelfRegistration(),
                'selfUnregistration' => $team->isSelfUnregistration(),
            ],
            'publicDirectory' => $team->isPublic(),
            'deletableDirectory' => $team->isDirDeletable(),
            'role' => $team->getRole() ?
                $this->roleSerializer->serialize($team->getRole(), [Options::SERIALIZE_MINIMAL]) :
                null,
            'teamManagerRole' => $team->getTeamManagerRole() ?
                $this->roleSerializer->serialize($team->getTeamManagerRole(), [Options::SERIALIZE_MINIMAL]) :
                null,
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options) && !in_array(Options::SERIALIZE_LIST, $options)) {
            $serialized = array_merge($serialized, [
                'directory' => $team->getDirectory() ?
                    $this->resourceNodeSerializer->serialize($team->getDirectory()->getResourceNode(), [Options::SERIALIZE_MINIMAL]) :
                    null,
                'workspace' => $this->workspaceSerializer->serialize($team->getWorkspace(), [Options::SERIALIZE_MINIMAL]),
            ]);
        }

        return $serialized;
    }

    public function deserialize(array $data, Team $team, array $options = []): Team
    {
        if (!in_array(Options::REFRESH_UUID, $options)) {
            $team->setUuid($data['id']);
        }

        $this->sipe('name', 'setName', $data, $team);
        $this->sipe('description', 'setDescription', $data, $team);
        $this->sipe('registration.selfRegistration', 'setSelfRegistration', $data, $team);
        $this->sipe('registration.selfUnregistration', 'setSelfUnregistration', $data, $team);
        $this->sipe('publicDirectory', 'setIsPublic', $data, $team);
        $this->sipe('deletableDirectory', 'setDirDeletable', $data, $team);
        $this->sipe('maxUsers', 'setMaxUsers', $data, $team);

        if (isset($data['directory'])) {
            $directory = null;
            /** @var ResourceNode $directory */
            $directoryNode = $this->om->getObject($data['directory'], ResourceNode::class);
            if ($directoryNode) {
                $directory = $this->om->getRepository(Directory::class)->findOneBy(['resourceNode' => $directoryNode]);
            }

            $team->setDirectory($directory);
        }

        if (isset($data['role'])) {
            /** @var Role $role */
            $role = $this->om->getObject($data['role'], Workspace::class);
            $team->setRole($role);
        }

        if (isset($data['teamManagerRole'])) {
            /** @var Role $managerRole */
            $managerRole = $this->om->getObject($data['teamManagerRole'], Workspace::class);
            $team->setTeamManagerRole($managerRole);
        }

        if (isset($data['workspace'])) {
            /** @var Workspace $workspace */
            $workspace = $this->om->getObject($data['workspace'], Workspace::class);
            $team->setWorkspace($workspace);
        }

        return $team;
    }
}
