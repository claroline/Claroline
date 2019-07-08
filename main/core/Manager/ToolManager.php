<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\PwsToolConfig;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\ToolRole;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\Exception\ToolPositionAlreadyOccupiedException;
use Claroline\CoreBundle\Repository\AdministrationToolRepository;
use Claroline\CoreBundle\Repository\OrderedToolRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Repository\ToolRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("claroline.manager.tool_manager")
 */
class ToolManager
{
    use LoggableTrait;

    // todo adds a config in tools to avoid this
    const WORKSPACE_MODEL_TOOLS = ['home', 'resource_manager', 'users'];

    /** @var OrderedToolRepository */
    private $orderedToolRepo;
    /** @var RoleRepository */
    private $roleRepo;
    /** @var ToolRepository */
    private $toolRepo;
    /** @var UserRepository */
    private $userRepo;
    private $toolRoleRepo;
    /** @var AdministrationToolRepository */
    private $adminToolRepo;
    private $pwsToolConfigRepo;
    /** @var ClaroUtilities */
    private $utilities;
    /** @var ObjectManager */
    private $om;
    /** @var RoleManager */
    private $roleManager;
    /** @var ToolMaskDecoderManager */
    private $toolMaskManager;
    /** @var ToolRightsManager */
    private $toolRightsManager;

    private $container;

    /**
     * ToolManager constructor.
     *
     * @DI\InjectParams({
     *     "utilities"         = @DI\Inject("claroline.utilities.misc"),
     *     "om"                = @DI\Inject("claroline.persistence.object_manager"),
     *     "roleManager"       = @DI\Inject("claroline.manager.role_manager"),
     *     "toolMaskManager"   = @DI\Inject("claroline.manager.tool_mask_decoder_manager"),
     *     "toolRightsManager" = @DI\Inject("claroline.manager.tool_rights_manager"),
     *     "container"         = @DI\Inject("service_container")
     * })
     *
     * @param ClaroUtilities         $utilities
     * @param ObjectManager          $om
     * @param RoleManager            $roleManager
     * @param ToolMaskDecoderManager $toolMaskManager
     * @param ToolRightsManager      $toolRightsManager
     * @param ContainerInterface     $container
     */
    public function __construct(
        ClaroUtilities $utilities,
        ObjectManager $om,
        RoleManager $roleManager,
        ToolMaskDecoderManager $toolMaskManager,
        ToolRightsManager $toolRightsManager,
        ContainerInterface $container
    ) {
        $this->orderedToolRepo = $om->getRepository(OrderedTool::class);
        $this->toolRepo = $om->getRepository(Tool::class);
        $this->roleRepo = $om->getRepository(Role::class);
        $this->toolRoleRepo = $om->getRepository(ToolRole::class);
        $this->adminToolRepo = $om->getRepository(AdminTool::class);
        $this->pwsToolConfigRepo = $om->getRepository(PwsToolConfig::class);
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->utilities = $utilities;
        $this->om = $om;
        $this->roleManager = $roleManager;
        $this->toolMaskManager = $toolMaskManager;
        $this->toolRightsManager = $toolRightsManager;
        $this->container = $container;
    }

    public function create(Tool $tool)
    {
        $this->om->startFlushSuite();
        $this->om->persist($tool);
        $this->om->forceFlush();
        $this->toolMaskManager->createDefaultToolMaskDecoders($tool);
        $this->om->endFlushSuite();

        //check if there are already workspace Tools, if not we add them
        $ot = $this->om->getRepository(OrderedTool::class)->findBy(['tool' => $tool], [], 1, 0);

        if (count($ot) > 0 || !$tool->isDisplayableInWorkspace()) {
            return;
        }

        $total = $this->om->count(Workspace::class);
        $this->log('Adding tool '.$tool->getName().' to workspaces ('.$total.')');

        $offset = 0;
        $totalTools = $this->om->count(Tool::class);
        $this->om->startFlushSuite();

        while ($offset < $total) {
            /** @var Workspace $workspaces */
            $workspaces = $this->om->getRepository(Workspace::class)->findBy([], [], 500, $offset);
            $ot = [];

            foreach ($workspaces as $workspace) {
                $ot[] = $this->setWorkspaceTool($tool, $totalTools, $tool->getName(), $workspace);
                ++$offset;
                $this->log('Adding tool '.$offset.'/'.$total);
            }
            $this->log('Flush');
            $this->om->forceFlush();

            foreach ($ot as $toDetach) {
                $this->om->detach($toDetach);
            }
        }

        $this->om->endFlushSuite();
    }

    /**
     * @param Tool      $tool
     * @param int       $position
     * @param string    $name
     * @param Workspace $workspace
     * @param int       $orderedToolType
     *
     * @return OrderedTool
     *
     * @throws ToolPositionAlreadyOccupiedException
     */
    public function setWorkspaceTool(
        Tool $tool,
        $position,
        $name,
        Workspace $workspace,
        $orderedToolType = 0
    ) {
        $switchTool = null;

        $orderedTool = $this->orderedToolRepo->findOneBy(
            ['workspace' => $workspace, 'tool' => $tool]
        );

        if (!$orderedTool) {
            $orderedTool = new OrderedTool();
        }

        // At the workspace creation, the workspace id is still null because we only flush once at the very end.
        if (null !== $workspace->getId()) {
            $switchTool = $this->orderedToolRepo->findOneBy(
                ['workspace' => $workspace, 'order' => $position, 'type' => $orderedToolType]
            );
        }

        while (!is_null($switchTool)) {
            ++$position;
            $switchTool = $this->orderedToolRepo->findOneBy(
                ['workspace' => $workspace, 'order' => $position, 'type' => $orderedToolType]
            );
        }

        $orderedTool->setWorkspace($workspace);
        $orderedTool->setName($name);
        $orderedTool->setOrder($position);
        $orderedTool->setTool($tool);
        $orderedTool->setType($orderedToolType);
        $this->om->persist($orderedTool);
        $this->om->flush();

        return $orderedTool;
    }

    /**
     * @param User  $user
     * @param int   $type
     * @param array $excludedTools
     *
     * @return Tool[]
     */
    public function getDisplayedDesktopOrderedTools(
        User $user,
        $type = 0,
        array $excludedTools = []
    ) {
        if (0 === count($excludedTools)) {
            return $this->toolRepo->findDesktopDisplayedToolsByUser($user, $type);
        }

        return $this->toolRepo->findDesktopDisplayedToolsWithExclusionByUser(
            $user,
            $excludedTools,
            $type
        );
    }

    /**
     * Returns the sorted list of OrderedTools for a user.
     *
     * @param User $user
     * @param int  $type
     *
     * @return OrderedTool[]
     */
    public function getDesktopToolsConfigurationArray(User $user, $type = 0)
    {
        $orderedToolList = [];
        $desktopTools = $this->orderedToolRepo->findDisplayableDesktopOrderedToolsByUser(
            $user,
            $type
        );

        foreach ($desktopTools as $desktopTool) {
            //this field isn't mapped
            $desktopTool->getTool()->setVisible($desktopTool->isVisibleInDesktop());
            $orderedToolList[$desktopTool->getOrder()] = $desktopTool->getTool();
        }

        $undisplayedTools = $this->toolRepo->findDesktopUndisplayedToolsByUser($user, $type);

        foreach ($undisplayedTools as $tool) {
            //this field isn't mapped
            $tool->setVisible(false);
        }

        $this->addMissingDesktopTools(
            $user,
            $undisplayedTools,
            count($desktopTools) + 1,
            $type
        );

        return $this->utilities->arrayFill($orderedToolList, $undisplayedTools);
    }

    /**
     * Adds the tools missing in the database for a workspace.
     * Returns an array formatted like this:.
     *
     * array(
     *     'tool'          => $tool,
     *     'workspace'     => $workspace,
     *     'visibility'    => array($roleId => $bool),
     *     'position'      => ...
     *     'displayedName' => ...
     * )
     *
     * @param Workspace $workspace
     * @param int       $type
     *
     * @return array
     */
    public function addMissingWorkspaceTools(Workspace $workspace, $type = 0)
    {
        $undisplayedTools = $this->toolRepo->findUndisplayedToolsByWorkspace($workspace, $type);

        if (0 === count($undisplayedTools)) {
            return [];
        }

        $initPos = $this->toolRepo->countDisplayedToolsByWorkspace($workspace, $type);
        ++$initPos;
        $missingTools = [];
        $wsRoles = $this->roleManager->getWorkspaceConfigurableRoles($workspace);
        $this->om->startFlushSuite();

        foreach ($undisplayedTools as $undisplayedTool) {
            $wot = $this->orderedToolRepo->findOneBy(
                ['workspace' => $workspace, 'tool' => $undisplayedTool, 'type' => $type]
            );

            //create a WorkspaceOrderedTool for each Tool that hasn't already one
            if (null === $wot) {
                $this->setWorkspaceTool(
                    $undisplayedTool,
                    $initPos,
                    $undisplayedTool->getName(),
                    $workspace,
                    $type
                );
            } else {
                continue;
            }

            $roleVisibility = [];
            foreach ($wsRoles as $role) {
                $roleVisibility[$role->getId()] = false;
            }

            $missingTools[] = [
                'tool' => $undisplayedTool,
                'workspace' => $workspace,
                'position' => $initPos,
                'visibility' => $roleVisibility,
                'displayedName' => $undisplayedTool->getName(),
            ];

            ++$initPos;
        }

        $this->om->endFlushSuite();

        return $missingTools;
    }

    /**
     * @param Tool   $tool
     * @param User   $user
     * @param int    $position
     * @param string $name
     * @param int    $type
     *
     * @throws ToolPositionAlreadyOccupiedException
     */
    public function addDesktopTool(Tool $tool, User $user, $position, $name, $type = 0)
    {
        $switchTool = $this->orderedToolRepo->findOneBy(
            ['user' => $user, 'order' => $position, 'type' => $type]
        );

        if (!$switchTool) {
            $desktopTool = new OrderedTool();
            $desktopTool->setUser($user);
            $desktopTool->setTool($tool);
            $desktopTool->setOrder($position);
            $desktopTool->setName($name);
            $desktopTool->setVisibleInDesktop(true);
            $desktopTool->setType($type);
            $this->om->persist($desktopTool);
            $this->om->flush();
        } elseif ($switchTool->getTool() === $tool) {
            $switchTool->setVisibleInDesktop(true);
            $this->om->flush();
        } else {
            throw new ToolPositionAlreadyOccupiedException('A tool already exists at this position');
        }
    }

    /**
     * Sets a tool position.
     *
     * @param Tool      $tool
     * @param           $position
     * @param User      $user
     * @param Workspace $workspace
     */
    public function setToolPosition(
        Tool $tool,
        $position,
        User $user = null,
        Workspace $workspace = null,
        $type = 0
    ) {
        $movingTool = $this->orderedToolRepo->findOneBy(
            ['user' => $user, 'tool' => $tool, 'workspace' => $workspace, 'type' => $type]
        );
        $movingTool->setOrder($position);
        $this->om->persist($movingTool);
        $this->om->flush();
    }

    /**
     * Resets the tool visibility.
     *
     * @param User      $user
     * @param Workspace $workspace
     * @param int       $type
     */
    public function resetToolsVisibility(
        User $user = null,
        Workspace $workspace = null,
        $type = 0
    ) {
        $orderedTools = $this->orderedToolRepo->findBy(
            ['user' => $user, 'workspace' => $workspace, 'type' => $type]
        );

        foreach ($orderedTools as $orderedTool) {
            if ($user) {
                $orderedTool->setVisibleInDesktop(false);
            }

            $this->om->persist($orderedTool);
        }

        $this->om->flush();
    }

    /**
     * Sets a tool visible for a user in the desktop.
     *
     * @param Tool $tool
     * @param User $user
     */
    public function setDesktopToolVisible(Tool $tool, User $user, $type = 0)
    {
        $orderedTool = $this->orderedToolRepo->findOneBy(
            ['user' => $user, 'tool' => $tool, 'type' => $type]
        );
        $orderedTool->setVisibleInDesktop(true);
        $this->om->persist($orderedTool);
        $this->om->flush();
    }

    /**
     * Adds the mandatory tools at the user creation.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     */
    public function addRequiredToolsToUser(User $user, $type = 0)
    {
        $requiredTools = [];
        $adminOrderedTools = $this->getConfigurableDesktopOrderedToolsByTypeForAdmin($type);

        foreach ($adminOrderedTools as $orderedTool) {
            if ($orderedTool->isVisibleInDesktop()) {
                $requiredTools[] = $orderedTool->getTool();
            }
        }

        $position = 1;
        $this->om->startFlushSuite();

        foreach ($requiredTools as $requiredTool) {
            $this->addDesktopTool(
                $requiredTool,
                $user,
                $position,
                $requiredTool->getName(),
                $type
            );
            ++$position;
        }

        $this->om->persist($user);
        $this->om->endFlushSuite();
    }

    /**
     * @param string[]  $roles
     * @param Workspace $workspace
     * @param int       $type
     *
     * @return OrderedTool[]
     */
    public function getOrderedToolsByWorkspaceAndRoles(Workspace $workspace, array $roles, $type = 0)
    {
        if ($workspace->isPersonal()) {
            $tools = $this->orderedToolRepo->findPersonalDisplayableByWorkspaceAndRoles(
                $workspace,
                $roles,
                $type
            );
        } else {
            $tools = $this->orderedToolRepo->findByWorkspaceAndRoles($workspace, $roles, $type);
        }

        if ($workspace->isModel()) {
            $tools = array_filter($tools, function (OrderedTool $orderedTool) {
                return in_array($orderedTool->getTool()->getName(), static::WORKSPACE_MODEL_TOOLS);
            });
        }

        return $tools;
    }

    /**
     * @param Workspace $workspace
     * @param int       $type
     *
     * @return OrderedTool[]
     */
    public function getOrderedToolsByWorkspace(Workspace $workspace, $type = 0)
    {
        // pre-load associated tools to save some requests
        $this->toolRepo->findDisplayedToolsByWorkspace($workspace, $type);

        // load workspace tools
        if ($workspace->isPersonal()) {
            $tools = $this->orderedToolRepo->findPersonalDisplayable($workspace, $type);
        } else {
            $tools = $this->orderedToolRepo->findBy(
                ['workspace' => $workspace, 'type' => $type],
                ['order' => 'ASC']
            );
        }

        if ($workspace->isModel()) {
            $tools = array_filter($tools, function (OrderedTool $orderedTool) {
                return in_array($orderedTool->getTool()->getName(), static::WORKSPACE_MODEL_TOOLS);
            });
        }

        return $tools;
    }

    /**
     * @param string[]                                         $roles
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Tool\Tool
     */
    public function getDisplayedByRolesAndWorkspace(
        array $roles,
        Workspace $workspace,
        $type = 0
    ) {
        return $this->toolRepo->findDisplayedByRolesAndWorkspace(
            $roles,
            $workspace,
            $type
        );
    }

    public function getAdminTools()
    {
        return $this->adminToolRepo->findAll();
    }

    public function getAdminToolByName($name)
    {
        return $this->om->getRepository('Claroline\CoreBundle\Entity\Tool\AdminTool')
            ->findOneByName($name);
    }

    /**
     * @param array $roles
     *
     * @return AdminTool[]
     */
    public function getAdminToolsByRoles(array $roles)
    {
        $disabled = $this->container->get('claroline.config.platform_config_handler')->getParameter('security.disabled_admin_tools');
        $tools = $this->om->getRepository('Claroline\CoreBundle\Entity\Tool\AdminTool')->findByRoles($roles);
        $allowed = [];

        foreach ($tools as $tool) {
            if (!in_array($tool->getName(), $disabled)) {
                $allowed[] = $tool;
            }
        }

        return $allowed;
    }

    public function getToolById($id)
    {
        return $this->om->getRepository('ClarolineCoreBundle:Tool\Tool')->find($id);
    }

    public function getToolByName($name)
    {
        return $this->om->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneBy(['name' => $name]);
    }

    /**
     * @param User $user
     * @param int  $type
     *
     * @return OrderedTool[]
     */
    public function getOrderedToolsByUser(User $user, $type = 0)
    {
        return $this->orderedToolRepo->findBy(
            ['user' => $user, 'type' => $type],
            ['order' => 'ASC']
        );
    }

    private function addMissingDesktopTools(
        User $user,
        array $missingTools,
        $startPosition,
        $type = 0
    ) {
        foreach ($missingTools as $tool) {
            $wot = $this->orderedToolRepo->findOneBy(
                ['user' => $user, 'tool' => $tool, 'type' => $type]
            );

            if (!$wot) {
                $orderedTool = new OrderedTool();
                $orderedTool->setUser($user);
                $orderedTool->setName($tool->getName());
                $orderedTool->setOrder($startPosition);
                $orderedTool->setTool($tool);
                $orderedTool->setVisibleInDesktop(false);
                $orderedTool->setType($type);
                $this->om->persist($orderedTool);
            }

            ++$startPosition;
        }

        $this->om->flush();
    }

    public function reorderDesktopOrderedTool(
        User $user,
        OrderedTool $orderedTool,
        $nextOrderedToolId,
        $type = 0
    ) {
        $orderedTools = $this->getConfigurableDesktopOrderedToolsByUser(
            $user,
            [],
            $type
        );
        $nextId = intval($nextOrderedToolId);
        $order = 1;
        $updated = false;

        foreach ($orderedTools as $ot) {
            if ($ot === $orderedTool) {
                continue;
            } elseif ($ot->getId() === $nextId) {
                $orderedTool->setOrder($order);
                $updated = true;
                $this->om->persist($orderedTool);
                ++$order;
                $ot->setOrder($order);
                $this->om->persist($ot);
                ++$order;
            } else {
                $ot->setOrder($order);
                $this->om->persist($ot);
                ++$order;
            }
        }

        if (!$updated) {
            $orderedTool->setOrder($order);
            $this->om->persist($orderedTool);
        }
        $this->om->flush();
    }

    public function getPersonalWorkspaceToolConfigs()
    {
        return $this->pwsToolConfigRepo->findAll();
    }

    public function getAvailableWorkspaceTools()
    {
        return $this->toolRepo->findBy(['isDisplayableInWorkspace' => true]);
    }

    public function getPersonalWorkspaceToolConfig(Tool $tool, Role $role)
    {
        $toolConfig = $this->pwsToolConfigRepo->findBy(['tool' => $tool, 'role' => $role]);

        if ($toolConfig) {
            return $toolConfig[0];
        }

        $toolConfig = new PwsToolConfig();
        $toolConfig->setTool($tool);
        $toolConfig->setRole($role);
        $toolConfig->setMask(0);

        $this->om->persist($toolConfig);
        $this->om->flush();

        return $toolConfig;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return \Claroline\CoreBundle\Entity\Tool\OrderedTool[]
     */
    public function getConfigurableDesktopOrderedToolsByUser(
        User $user,
        array $excludedToolNames,
        $type = 0,
        $executeQuery = true
    ) {
        $excludedToolNames[] = 'home'; // maybe not

        return $this->orderedToolRepo->findConfigurableDesktopOrderedToolsByUser(
            $user,
            $excludedToolNames,
            $type,
            $executeQuery
        );
    }

    public function getConfigurableDesktopOrderedToolsByTypeForAdmin(
        $type = 0,
        array $excludedToolNames = [],
        $executeQuery = true
    ) {
        $excludedToolNames[] = 'home'; // maybe not

        return $this->orderedToolRepo->findConfigurableDesktopOrderedToolsByTypeForAdmin(
            $excludedToolNames,
            $type,
            $executeQuery
        );
    }

    public function getLockedConfigurableDesktopOrderedToolsByTypeForAdmin(
        $type = 0,
        array $excludedToolNames = [],
        $executeQuery = true
    ) {
        $excludedToolNames[] = 'home'; // maybe not

        return $this->orderedToolRepo->findLockedConfigurableDesktopOrderedToolsByTypeForAdmin(
            $excludedToolNames,
            $type,
            $executeQuery
        );
    }

    public function getUserDesktopToolsConfiguration(User $user)
    {
        $roles = array_filter($user->getEntityRoles(), function (Role $role) {
            return Role::PLATFORM_ROLE === $role->getType();
        });

        return $this->getDesktopToolsConfiguration($roles);
    }

    public function getDesktopToolsConfiguration(array $roles)
    {
        $config = [];

        foreach ($roles as $role) {
            $toolsConfigs = $this->toolRoleRepo->findBy(['role' => $role]);

            foreach ($toolsConfigs as $toolConfig) {
                $toolName = $toolConfig->getTool()->getName();
                $display = $toolConfig->getDisplay();

                if (!isset($config[$toolName]) || is_null($config[$toolName]) || ToolRole::FORCED === $display) {
                    $config[$toolName] = $display;
                }
            }
        }

        return $config;
    }

    public function computeUserOrderedTools(User $user, array $config)
    {
        $orderedTools = [];
        $desktopTools = $this->toolRepo->findBy(['isDisplayableInDesktop' => true]);

        foreach ($desktopTools as $tool) {
            $toolName = $tool->getName();
            $orderedTool = $this->orderedToolRepo->findOneBy(['user' => $user, 'tool' => $tool, 'type' => 0]);

            if (is_null($orderedTool)) {
                $orderedTool = new OrderedTool();
                $orderedTool->setTool($tool);
                $orderedTool->setUser($user);
                $orderedTool->setType(0);
                $orderedTool->setOrder(1);
                $orderedTool->setName($toolName);
            }
            if (isset($config[$toolName])) {
                switch ($config[$toolName]) {
                    case ToolRole::FORCED:
                        $orderedTool->setVisibleInDesktop(true);
                        $orderedTool->setLocked(true);
                        break;
                    case ToolRole::HIDDEN:
                        $orderedTool->setVisibleInDesktop(false);
                        $orderedTool->setLocked(true);
                        break;
                    default:
                        $orderedTool->setLocked(false);
                }
            } else {
                $orderedTool->setLocked(false);
            }
            $this->om->persist($orderedTool);
            $orderedTools[] = $orderedTool;
        }
        $this->om->flush();

        return $orderedTools;
    }

    public function saveUserOrderedTools(User $user, array $config)
    {
        foreach ($config as $toolName => $data) {
            $tool = $this->toolRepo->findOneBy(['name' => $toolName]);

            if ($tool) {
                $orderedTool = $this->orderedToolRepo->findOneBy(['user' => $user, 'tool' => $tool, 'type' => 0]);

                if ($orderedTool) {
                    $orderedTool->setVisibleInDesktop($data['visible']);
                    $orderedTool->setLocked($data['locked']);
                    $this->om->persist($orderedTool);
                }
            }
        }
        $this->om->flush();
    }
}
