<?php

namespace Claroline\CoreBundle\API\Serializer\User;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Tool\ToolMaskDecoderManager;
use Claroline\CoreBundle\Manager\Tool\ToolRightsManager;
use Claroline\CoreBundle\Repository\Tool\OrderedToolRepository;
use Claroline\CoreBundle\Repository\Tool\ToolRightsRepository;
use Claroline\CoreBundle\Repository\User\UserRepository;

class RoleSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;

    /** @var ToolMaskDecoderManager */
    private $maskManager;

    /** @var ToolRightsManager */
    private $rightsManager;

    /** @var WorkspaceSerializer */
    private $workspaceSerializer;

    /** @var UserSerializer */
    private $userSerializer;

    /** @var OrderedToolRepository */
    private $orderedToolRepo;

    /** @var ToolRightsRepository */
    private $toolRightsRepo;

    /** @var UserRepository */
    private $userRepo;

    public function __construct(
        ObjectManager $om,
        ToolMaskDecoderManager $maskManager,
        ToolRightsManager $rightsManager,
        WorkspaceSerializer $workspaceSerializer,
        UserSerializer $userSerializer
    ) {
        $this->om = $om;
        $this->maskManager = $maskManager;
        $this->rightsManager = $rightsManager;
        $this->workspaceSerializer = $workspaceSerializer;
        $this->userSerializer = $userSerializer;

        $this->orderedToolRepo = $this->om->getRepository('ClarolineCoreBundle:Tool\OrderedTool');
        $this->toolRightsRepo = $this->om->getRepository('ClarolineCoreBundle:Tool\ToolRights');
        $this->userRepo = $this->om->getRepository('ClarolineCoreBundle:User');
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
            'name' => $role->getName(),
            'type' => $role->getType(), // TODO : should be a string for better data readability
            'translationKey' => $role->getTranslationKey(),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized['meta'] = $this->serializeMeta($role);
            $serialized['restrictions'] = $this->serializeRestrictions($role);

            if ($role->getWorkspace()) {
                $serialized['workspace'] = $this->workspaceSerializer->serialize($role->getWorkspace(), [Options::SERIALIZE_MINIMAL]);
            }

            if (Role::USER_ROLE === $role->getType()) {
                if (count($role->getUsers()->toArray()) > 0) {
                    $serialized['user'] = $this->userSerializer->serialize($role->getUsers()->toArray()[0], [Options::SERIALIZE_MINIMAL]);
                } else {
                    //if we removed some user roles... for some reason.
                    $serialized['user'] = null;
                }
            }

            // TODO: Fix option for workspace uuid. For the moment the uuid of the workspace is prefixed with `workspace_id_`.
            if (in_array(Options::SERIALIZE_ROLE_TOOLS_RIGHTS, $options)) {
                $workspaceId = null;

                foreach ($options as $option) {
                    if ('workspace_id_' === substr($option, 0, 13)) {
                        $workspaceId = substr($option, 13);
                        break;
                    }
                }
                if ($workspaceId) {
                    $serialized['tools'] = $this->serializeTools($role, $workspaceId);
                }
            }
            if (Role::PLATFORM_ROLE === $role->getType()) {
                // easier request than count users which will go into mysql cache so I'm not too worried about looping here.
                $adminTools = [];

                /** @var AdminTool $adminTool */
                foreach ($this->om->getRepository('ClarolineCoreBundle:Tool\AdminTool')->findAll() as $adminTool) {
                    $adminTools[$adminTool->getName()] = $role->getAdminTools()->contains($adminTool);
                }

                $serialized['adminTools'] = $adminTools;
                $serialized['desktopTools'] = $this->serializeTools($role);
            }
        }

        return $serialized;
    }

    public function serializeMeta(Role $role): array
    {
        $meta = [
            'readOnly' => $role->isReadOnly(),
            'personalWorkspaceCreationEnabled' => $role->getPersonalWorkspaceCreationEnabled(),
            'users' => 1,
       ];

        if (Role::USER_ROLE !== $role->getType()) {
            $meta['users'] = $this->userRepo->countUsersByRoleIncludingGroup($role);
        }

        return $meta;
    }

    public function serializeRestrictions(Role $role): array
    {
        return [
            'maxUsers' => $role->getMaxUsers(),
        ];
    }

    private function serializeTools(Role $role, string $workspaceId = null): array
    {
        $tools = [];

        if (!empty($workspaceId)) {
            // get workspace tools
            $workspace = $this->om->getRepository(Workspace::class)->findBy(['uuid' => $workspaceId]);
            $orderedTools = $this->orderedToolRepo->findBy(['workspace' => $workspace]);
        } else {
            // get desktop tools
            $orderedTools = $this->orderedToolRepo->findBy(['workspace' => null, 'user' => null]);
        }

        foreach ($orderedTools as $orderedTool) {
            $toolRights = $this->toolRightsRepo->findBy(['role' => $role, 'orderedTool' => $orderedTool], ['id' => 'ASC']);
            $mask = 0 < count($toolRights) ? $toolRights[0]->getMask() : 0;

            $tools[$orderedTool->getTool()->getName()] = $this->maskManager->decodeMask($mask, $orderedTool->getTool());
        }

        return $tools;
    }

    public function deserialize(array $data, Role $role, array $options = []): Role
    {
        if (!$role->isReadOnly()) {
            $this->sipe('name', 'setName', $data, $role);

            $this->sipe('type', 'setType', $data, $role);

            if (isset($data['translationKey'])) {
                $role->setTranslationKey($data['translationKey']);
                //this is if it's not a workspace and we send the translationKey role
                if (null === $role->getName() && !isset($data['workspace'])) {
                    $role->setName('ROLE_'.str_replace(' ', '_', strtoupper($data['translationKey'])));
                }
            }
        }

        $this->sipe('meta.personalWorkspaceCreationEnabled', 'setPersonalWorkspaceCreationEnabled', $data, $role);
        $this->sipe('restrictions.maxUsers', 'setMaxUsers', $data, $role);

        // we should test role type before trying to set the workspace
        if (!empty($data['workspace']) && !empty($data['workspace']['id'])) {
            $workspace = $this->om->getRepository('ClarolineCoreBundle:Workspace\Workspace')
                ->findOneBy(['uuid' => $data['workspace']['id']]);

            if (!$role->getName()) {
                $role->setName('ROLE_WS_'.str_replace(' ', '_', strtoupper($data['translationKey'])).'_'.$data['workspace']['id']);
            }

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

        if (isset($data['adminTools'])) {
            $adminTools = $this->om->getRepository('ClarolineCoreBundle:Tool\AdminTool')->findAll();

            /** @var AdminTool $adminTool */
            foreach ($adminTools as $adminTool) {
                if ($data['adminTools'][$adminTool->getName()]) {
                    $adminTool->addRole($role);
                } else {
                    $adminTool->removeRole($role);
                }
            }
        }

        // sets rights for workspace tools
        if (isset($data['tools']) && in_array(Options::SERIALIZE_ROLE_TOOLS_RIGHTS, $options)) {
            foreach ($data['tools'] as $toolName => $toolData) {
                $tool = $this->om->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneBy(['name' => $toolName]);

                if ($tool) {
                    // TODO: Fix option for workspace uuid. For the moment the uuid of the workspace is prefixed with `workspace_id_`.
                    $workspaceId = null;

                    foreach ($options as $option) {
                        if ('workspace_id_' === substr($option, 0, 13)) {
                            $workspaceId = substr($option, 13);
                            break;
                        }
                    }

                    if ($workspaceId) {
                        $workspace = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $workspaceId]);
                        $tool = $this->om->getRepository(Tool::class)->findOneBy(['name' => $toolName]);

                        /** @var OrderedTool $orderedTool */
                        $orderedTool = $this->orderedToolRepo->findOneBy([
                            'tool' => $tool,
                            'workspace' => $workspace,
                        ]);

                        if ($orderedTool) {
                            $this->rightsManager->setToolRights($orderedTool, $role, $this->maskManager->encodeMask($toolData, $orderedTool->getTool()));
                        }
                    }
                }
            }
        }

        // Sets desktop tools configuration for platform roles
        if (Role::PLATFORM_ROLE === $role->getType() && isset($data['desktopTools'])) {
            foreach ($data['desktopTools'] as $toolName => $toolData) {
                /** @var Tool $tool */
                $tool = $this->om->getRepository(Tool::class)->findOneBy(['name' => $toolName]);

                /** @var OrderedTool $orderedTool */
                $orderedTool = $this->orderedToolRepo->findOneBy([
                    'tool' => $tool,
                    'workspace' => null,
                    'user' => null,
                ]);

                if ($orderedTool) {
                    $this->rightsManager->setToolRights($orderedTool, $role, $this->maskManager->encodeMask($toolData, $orderedTool->getTool()));
                }
            }
        }

        return $role;
    }
}
