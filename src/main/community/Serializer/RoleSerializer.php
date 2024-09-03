<?php

namespace Claroline\CommunityBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RoleSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly WorkspaceSerializer $workspaceSerializer,
        private readonly UserSerializer $userSerializer
    ) {
    }

    public function getName(): string
    {
        return 'role';
    }

    public function getClass(): string
    {
        return Role::class;
    }

    public function getSchema(): string
    {
        return '#/main/core/role.json';
    }

    public function serialize(Role $role, array $options = []): array
    {
        $serialized = [
            'id' => $role->getUuid(),
            'autoId' => $role->getId(),
            'name' => $role->getName(),
            'type' => $role->getType(),
            'translationKey' => $role->getTranslationKey(),
            'meta' => [
                'description' => $role->getDescription(),
            ],
        ];

        if (!in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            $serialized['meta'] = [
                'description' => $role->getDescription(),
                'readOnly' => $role->isLocked(),
                'personalWorkspaceCreationEnabled' => $role->isPersonalWorkspaceCreationEnabled(),
            ];

            if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
                $serialized['permissions'] = [
                    'open' => $this->authorization->isGranted('OPEN', $role),
                    'edit' => $this->authorization->isGranted('EDIT', $role),
                    'administrate' => $this->authorization->isGranted('ADMINISTRATE', $role),
                    'delete' => $this->authorization->isGranted('DELETE', $role),
                ];

                if (Role::WS_ROLE === $role->getType() && $role->getWorkspace()) {
                    $serialized['workspace'] = $this->workspaceSerializer->serialize($role->getWorkspace(), [SerializerInterface::SERIALIZE_MINIMAL]);
                } elseif (Role::USER_ROLE === $role->getType()) {
                    if (count($role->getUsers()->toArray()) > 0) {
                        $serialized['user'] = $this->userSerializer->serialize($role->getUsers()->toArray()[0], [SerializerInterface::SERIALIZE_MINIMAL]);
                    } else {
                        //if we removed some user roles... for some reason.
                        $serialized['user'] = null;
                    }
                }
            }
        }

        return $serialized;
    }

    public function deserialize(array $data, Role $role, ?array $options = []): Role
    {
        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $role);
        } else {
            $role->refreshUuid();
        }

        if (!$role->isLocked()) { // shouldn't be checked in the deserialization
            $this->sipe('name', 'setName', $data, $role);
            $this->sipe('type', 'setType', $data, $role);
            $this->sipe('translationKey', 'setTranslationKey', $data, $role);
        }

        $this->sipe('meta.description', 'setDescription', $data, $role);
        $this->sipe('meta.personalWorkspaceCreationEnabled', 'setPersonalWorkspaceCreationEnabled', $data, $role);

        // we should test role type before trying to set the workspace
        if (!empty($data['workspace']) && !empty($data['workspace']['id'])) {
            $workspace = $this->om->getRepository(Workspace::class)
                ->findOneBy(['uuid' => $data['workspace']['id']]);
            if ($workspace) {
                $role->setWorkspace($workspace);
            }
        }

        if (!empty($data['user']) && !empty($data['user']['id'])) {
            /** @var User $user */
            $user = $this->om->getRepository(User::class)
                ->findOneBy(['uuid' => $data['user']['id']]);
            if ($user) {
                $role->addUser($user);
            }
        }

        return $role;
    }
}
