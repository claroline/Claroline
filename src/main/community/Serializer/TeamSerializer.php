<?php

namespace Claroline\CommunityBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Entity\Team;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class TeamSerializer
{
    use SerializerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var ResourceNodeSerializer */
    private $resourceNodeSerializer;
    /** @var RoleSerializer */
    private $roleSerializer;
    /** @var WorkspaceSerializer */
    private $workspaceSerializer;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        ResourceNodeSerializer $resourceNodeSerializer,
        RoleSerializer $roleSerializer,
        WorkspaceSerializer $workspaceSerializer
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->resourceNodeSerializer = $resourceNodeSerializer;
        $this->roleSerializer = $roleSerializer;
        $this->workspaceSerializer = $workspaceSerializer;
    }

    public function getClass(): string
    {
        return Team::class;
    }

    public function getName(): string
    {
        return 'team';
    }

    public function serialize(Team $team, array $options = []): array
    {
        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return [
                'id' => $team->getUuid(),
                'autoId' => $team->getId(),
                'name' => $team->getName(),
                'thumbnail' => $team->getThumbnail(),
            ];
        }

        $serialized = [
            'id' => $team->getUuid(),
            'autoId' => $team->getId(),
            'name' => $team->getName(),
            'thumbnail' => $team->getThumbnail(),
            'poster' => $team->getPoster(),
            'users' => $this->om->getRepository(Team::class)->countUsers($team),
            'meta' => [
                'description' => $team->getDescription(),
            ],
            'registration' => [
                'selfRegistration' => $team->isSelfRegistration(),
                'selfUnregistration' => $team->isSelfUnregistration(),
            ],
            'restrictions' => [
                'users' => $team->getMaxUsers(),
            ],
            'publicDirectory' => $team->isPublic(),
            'deletableDirectory' => $team->isDirDeletable(),
            'directory' => $team->getDirectory() ?
                $this->resourceNodeSerializer->serialize($team->getDirectory(), [SerializerInterface::SERIALIZE_MINIMAL]) :
                null,
        ];

        if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
            $serialized['permissions'] = [
                'open' => $this->authorization->isGranted('OPEN', $team),
                'edit' => $this->authorization->isGranted('EDIT', $team),
                'delete' => $this->authorization->isGranted('DELETE', $team),
            ];
        }

        if (!in_array(SerializerInterface::SERIALIZE_LIST, $options)) {
            $serialized = array_merge($serialized, [
                'role' => $team->getRole() ?
                    $this->roleSerializer->serialize($team->getRole(), [SerializerInterface::SERIALIZE_MINIMAL]) :
                    null,
                'managerRole' => $team->getManagerRole() ?
                    $this->roleSerializer->serialize($team->getManagerRole(), [SerializerInterface::SERIALIZE_MINIMAL]) :
                    null,
                'workspace' => $this->workspaceSerializer->serialize($team->getWorkspace(), [SerializerInterface::SERIALIZE_MINIMAL]),
            ]);
        }

        return $serialized;
    }

    public function deserialize(array $data, Team $team, array $options = []): Team
    {
        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $team);
        } else {
            $team->refreshUuid();
        }

        $this->sipe('name', 'setName', $data, $team);
        $this->sipe('meta.description', 'setDescription', $data, $team);
        $this->sipe('poster', 'setPoster', $data, $team);
        $this->sipe('thumbnail', 'setThumbnail', $data, $team);
        $this->sipe('registration.selfRegistration', 'setSelfRegistration', $data, $team);
        $this->sipe('registration.selfUnregistration', 'setSelfUnregistration', $data, $team);
        $this->sipe('publicDirectory', 'setPublic', $data, $team);
        $this->sipe('deletableDirectory', 'setDirDeletable', $data, $team);
        $this->sipe('restrictions.users', 'setMaxUsers', $data, $team);

        if (isset($data['directory'])) {
            /** @var ResourceNode $directoryNode */
            $directoryNode = $this->om->getObject($data['directory'], ResourceNode::class);
            $team->setDirectory($directoryNode);
        }

        if (isset($data['role'])) {
            /** @var Role $role */
            $role = $this->om->getObject($data['role'], Role::class);
            $team->setRole($role);
        }

        if (isset($data['managerRole'])) {
            /** @var Role $managerRole */
            $managerRole = $this->om->getObject($data['managerRole'], Role::class);
            $team->setManagerRole($managerRole);
        }

        if (isset($data['workspace'])) {
            /** @var Workspace $workspace */
            $workspace = $this->om->getObject($data['workspace'], Workspace::class);
            $team->setWorkspace($workspace);
        }

        return $team;
    }
}
