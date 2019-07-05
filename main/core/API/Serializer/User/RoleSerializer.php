<?php

namespace Claroline\CoreBundle\API\Serializer\User;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\ToolsOptions;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Finder\Workspace\OrderedToolFinder;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Claroline\CoreBundle\Entity\Tool\ToolRole;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Repository\OrderedToolRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.role")
 * @DI\Tag("claroline.serializer")
 */
class RoleSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;

    /** @var OrderedToolRepository */
    private $orderedToolRepo;

    /** @var ToolRightsRepository */
    private $toolRightsRepo;

    /** @var UserRepository */
    private $userRepo;

    /**
     * RoleSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"                  = @DI\Inject("claroline.persistence.object_manager"),
     *     "orderedToolFinder"   = @DI\Inject("claroline.api.finder.ordered_tool"),
     *     "workspaceSerializer" = @DI\Inject("claroline.serializer.workspace"),
     *     "userSerializer"      = @DI\Inject("claroline.serializer.user")
     * })
     *
     * @param SerializerProvider $serializer
     * @param ObjectManager      $om
     */
    public function __construct(
        ObjectManager $om,
        OrderedToolFinder $orderedToolFinder,
        WorkspaceSerializer $workspaceSerializer,
        UserSerializer $userSerializer
    ) {
        $this->om = $om;
        $this->orderedToolFinder = $orderedToolFinder;
        $this->workspaceSerializer = $workspaceSerializer;
        $this->userSerializer = $userSerializer;

        $this->orderedToolRepo = $this->om->getRepository('ClarolineCoreBundle:Tool\OrderedTool');
        $this->toolRightsRepo = $this->om->getRepository('ClarolineCoreBundle:Tool\ToolRights');
        $this->userRepo = $this->om->getRepository('ClarolineCoreBundle:User');
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return Role::class;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/main/core/role.json';
    }

    /**
     * Serializes a Role entity.
     *
     * @param Role  $role
     * @param array $options
     *
     * @return array
     */
    public function serialize(Role $role, array $options = [])
    {
        $serialized = [
            'id' => $role->getUuid(),
            'name' => $role->getName(),
            'type' => $role->getType(), // TODO : should be a string for better data readability
            'translationKey' => $role->getTranslationKey(),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized['meta'] = $this->serializeMeta($role, $options);
            $serialized['restrictions'] = $this->serializeRestrictions($role);

            if ($workspace = $role->getWorkspace()) {
                $serialized['workspace'] = $this->workspaceSerializer->serialize($workspace, [Options::SERIALIZE_MINIMAL]);
            }

            if (Role::USER_ROLE === $role->getType()) {
                if (count($role->getUsers()->toArray()) > 0) {
                    $serialized['user'] = $this->userSerializer->serialize($role->getUsers()->toArray()[0], [Options::SERIALIZE_MINIMAL]);
                } else {
                    //if we removed some user roles... for some reason.
                    $serialized['user'] = null;
                }
            }

            // easier request than count users which will go into mysql cache so I'm not too worried about looping here.
            $adminTools = [];

            /** @var AdminTool $adminTool */
            foreach ($this->om->getRepository('ClarolineCoreBundle:Tool\AdminTool')->findAll() as $adminTool) {
                $adminTools[$adminTool->getName()] = $role->getAdminTools()->contains($adminTool);
            }

            $serialized['adminTools'] = $adminTools;

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
            if (Role::PLATFORM_ROLE === $role->getType() && in_array(Options::SERIALIZE_ROLE_DESKTOP_TOOLS, $options)) {
                $serialized['desktopTools'] = $this->serializeDesktopToolsConfig($role);
            }
        }

        return $serialized;
    }

    /**
     * Serialize role metadata.
     *
     * @param Role  $role
     * @param array $options
     *
     * @return array
     */
    public function serializeMeta(Role $role, array $options)
    {
        $meta = [
           'readOnly' => $role->isReadOnly(),
           'personalWorkspaceCreationEnabled' => $role->getPersonalWorkspaceCreationEnabled(),
       ];

        if (in_array(Options::SERIALIZE_COUNT_USER, $options)) {
            if (Role::USER_ROLE !== $role->getType()) {
                $meta['users'] = $this->userRepo->countUsersByRoleIncludingGroup($role);
            } else {
                $meta['users'] = 1;
            }
        }

        return $meta;
    }

    /**
     * Serialize role restrictions.
     *
     * @param Role $role
     *
     * @return array
     */
    public function serializeRestrictions(Role $role)
    {
        return [
            'maxUsers' => $role->getMaxUsers(),
        ];
    }

    /**
     * Serialize role tools rights.
     *
     * @param Role   $role
     * @param string $workspaceId
     *
     * @return array
     */
    private function serializeTools(Role $role, $workspaceId)
    {
        $tools = [];
        $workspace = $this->om->getRepository(Workspace::class)->findBy(['uuid' => $workspaceId]);
        $orderedTools = $this->orderedToolRepo->findBy(['workspace' => $workspace]);

        foreach ($orderedTools as $orderedTool) {
            $toolRights = $this->toolRightsRepo->findBy(['role' => $role, 'orderedTool' => $orderedTool], ['id' => 'ASC']);
            $mask = 0 < count($toolRights) ? $toolRights[0]->getMask() : 0;
            $toolName = $orderedTool->getTool()->getName();
            $tools[$toolName] = [];

            foreach (ToolMaskDecoder::$defaultActions as $action) {
                $actionValue = ToolMaskDecoder::$defaultValues[$action];
                $tools[$toolName][$action] = $mask & $actionValue ? true : false;
            }
        }

        return count($tools) > 0 ? $tools : new \stdClass();
    }

    /**
     * Serialize role configuration for desktop tools.
     *
     * @param Role $role
     *
     * @return array
     */
    private function serializeDesktopToolsConfig(Role $role)
    {
        $configs = [];
        $desktopTools = $this->om->getRepository(Tool::class)->findBy(['isDisplayableInDesktop' => true]);
        $toolsRole = $this->om->getRepository(ToolRole::class)->findBy(['role' => $role]);

        foreach ($toolsRole as $toolRole) {
            $toolName = $toolRole->getTool()->getName();

            if (!in_array($toolName, ToolsOptions::EXCLUDED_TOOLS)) {
                $configs[$toolName] = $toolRole->getDisplay();
            }
        }
        foreach ($desktopTools as $desktopTool) {
            $toolName = $desktopTool->getName();

            if (!in_array($toolName, ToolsOptions::EXCLUDED_TOOLS) && !isset($configs[$toolName])) {
                $configs[$toolName] = null;
            }
        }

        return 0 < count($configs) ? $configs : new \stdClass();
    }

    /**
     * Deserializes data into a Role entity.
     *
     * @param \stdClass $data
     * @param Role      $role
     * @param array     $options
     *
     * @return Role
     */
    public function deserialize($data, Role $role, array $options = [])
    {
        // TODO : set readOnly based on role type
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
        if (!empty($data['workspace']) && !empty($data['workspace']['uuid'])) {
            if (isset($data['workspace']['uuid'])) {
                $workspace = $this->om->getRepository('ClarolineCoreBundle:Workspace\Workspace')
                    ->findOneBy(['uuid' => $data['workspace']['uuid']]);

                if (!$role->getName()) {
                    $role->setName('ROLE_WS_'.str_replace(' ', '_', strtoupper($data['translationKey'])).'_'.$data['workspace']['uuid']);
                }

                if ($workspace) {
                    $role->setWorkspace($workspace);
                }
            }
        }

        // TODO : set the user for ROLE_USER

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
                        $orderedTool = $this->orderedToolFinder
                          ->findOneBy(['tool' => $toolName, 'workspace' => $workspaceId]);

                        if ($orderedTool) {
                            $toolRights = $this->om
                                ->getRepository('ClarolineCoreBundle:Tool\ToolRights')
                                ->findBy(['orderedTool' => $orderedTool, 'role' => $role], ['id' => 'ASC']);

                            if (0 < count($toolRights)) {
                                $rights = $toolRights[0];
                            } else {
                                $rights = new ToolRights();
                                $rights->setRole($role);
                                $rights->setOrderedTool($orderedTool);
                            }
                            $mask = 0;

                            foreach (ToolMaskDecoder::$defaultActions as $action) {
                                if (isset($toolData[$action]) && $toolData[$action]) {
                                    $mask += ToolMaskDecoder::$defaultValues[$action];
                                }
                            }
                            $rights->setMask($mask);
                            $this->om->persist($rights);
                        }
                    }
                }
            }
        }

        // Sets desktop tools configuration for platform roles
        if (Role::PLATFORM_ROLE === $role->getType() && isset($data['desktopTools'])) {
            foreach ($data['desktopTools'] as $toolName => $toolData) {
                $tool = $this->om->getRepository(Tool::class)->findOneBy(['name' => $toolName]);

                if ($tool) {
                    $toolRole = $this->om->getRepository(ToolRole::class)->findOneBy(['tool' => $tool, 'role' => $role]);

                    if (!$toolRole) {
                        $toolRole = new ToolRole();
                        $toolRole->setTool($tool);
                        $toolRole->setRole($role);
                    }
                    $toolRole->setDisplay($toolData);
                    $this->om->persist($toolRole);
                }
            }
        }

        return $role;
    }
}
