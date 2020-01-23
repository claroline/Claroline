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
use Claroline\CoreBundle\Manager\Exception\ToolPositionAlreadyOccupiedException;
use Claroline\CoreBundle\Repository\AdministrationToolRepository;
use Claroline\CoreBundle\Repository\OrderedToolRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Repository\ToolRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ToolManager
{
    use LoggableTrait;

    // todo adds a config in tools to avoid this
    const WORKSPACE_MODEL_TOOLS = ['home', 'resources', 'users'];

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
     * @param ObjectManager          $om
     * @param RoleManager            $roleManager
     * @param ToolMaskDecoderManager $toolMaskManager
     * @param ToolRightsManager      $toolRightsManager
     * @param ContainerInterface     $container
     */
    public function __construct(
        ObjectManager $om,
        RoleManager $roleManager,
        ToolMaskDecoderManager $toolMaskManager,
        ToolRightsManager $toolRightsManager,
        ContainerInterface $container
    ) {
        $this->om = $om;
        $this->roleManager = $roleManager;
        $this->toolMaskManager = $toolMaskManager;
        $this->toolRightsManager = $toolRightsManager;

        $this->orderedToolRepo = $om->getRepository(OrderedTool::class);
        $this->toolRepo = $om->getRepository(Tool::class);
        $this->roleRepo = $om->getRepository(Role::class);
        $this->toolRoleRepo = $om->getRepository(ToolRole::class);
        $this->adminToolRepo = $om->getRepository(AdminTool::class);
        $this->pwsToolConfigRepo = $om->getRepository(PwsToolConfig::class);
        $this->userRepo = $om->getRepository(User::class);

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
                $ot[] = $this->setWorkspaceTool($tool, $totalTools, $workspace);
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
        $orderedTool->setOrder($position);
        $orderedTool->setTool($tool);
        $orderedTool->setType($orderedToolType);
        $this->om->persist($orderedTool);
        $this->om->flush();

        return $orderedTool;
    }

    /**
     * @param User $user
     * @param int  $type
     *
     * @return Tool[]
     */
    public function getDisplayedDesktopOrderedTools(User $user, $type = 0)
    {
        return $this->toolRepo->findDesktopDisplayedToolsByUser($user, $type);
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
     * @param Tool $tool
     * @param User $user
     * @param int  $position
     * @param int  $type
     *
     * @throws ToolPositionAlreadyOccupiedException
     */
    public function addDesktopTool(Tool $tool, User $user, $position, $type = 0)
    {
        $switchTool = $this->orderedToolRepo->findOneBy(
            ['user' => $user, 'order' => $position, 'type' => $type]
        );

        if (!$switchTool) {
            $desktopTool = new OrderedTool();
            $desktopTool->setUser($user);
            $desktopTool->setTool($tool);
            $desktopTool->setOrder($position);
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

    public function getUserDisplayedTools(User $user)
    {
        $tools = [];

        /** @var Tool[] $ots */
        $ots = $this->getDisplayedDesktopOrderedTools($user);
        // TODO : restore user tools config
        //$configs = $this->getUserDesktopToolsConfiguration($user);

        /** @var Role[] $roles */
        $roles = $user->getEntityRoles();

        foreach ($ots as $tool) {
            foreach ($roles as $role) {
                if (Role::PLATFORM_ROLE === $role->getType()) {
                    if ('ROLE_ADMIN' === $role->getName()) {
                        $tools[] = $tool;
                        break;
                    }

                    $toolRole = $this->om->getRepository(ToolRole::class)->findOneBy(['role' => $role, 'tool' => $tool]);
                    if (!$toolRole || ToolRole::HIDDEN !== $toolRole->getDisplay()) {
                        $tools[] = $tool;
                        break;
                    }
                }
            }
        }

        return $tools;
    }

    /**
     * Adds the mandatory tools at the user creation.
     *
     * @param User $user
     * @param int  $type
     *
     * @return Tool[]
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
                $type
            );
            ++$position;
        }

        $this->om->persist($user);
        $this->om->endFlushSuite();

        return $requiredTools;
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
     * @param string[]  $roles
     * @param Workspace $workspace
     * @param int       $type
     *
     * @return Tool
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

    public function getAdminToolByName($name)
    {
        return $this->adminToolRepo->findOneBy(['name' => $name]);
    }

    /**
     * @param array $roles
     *
     * @return AdminTool[]
     */
    public function getAdminToolsByRoles(array $roles)
    {
        return $this->om->getRepository(AdminTool::class)->findByRoles($roles);
    }

    public function getToolByName($name)
    {
        return $this->toolRepo->findOneBy(['name' => $name]);
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

    public function addMissingDesktopTools(
        User $user,
        $startPosition = 0,
        $type = 0
    ) {
        $missingTools = $this->toolRepo->findDesktopUndisplayedToolsByUser($user, $type);

        foreach ($missingTools as $tool) {
            //this field isn't mapped
            $tool->setVisible(false);

            $wot = $this->orderedToolRepo->findOneBy(
                ['user' => $user, 'tool' => $tool, 'type' => $type]
            );

            if (!$wot) {
                $orderedTool = new OrderedTool();
                $orderedTool->setUser($user);
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

    public function getPersonalWorkspaceToolConfigs()
    {
        return $this->pwsToolConfigRepo->findAll();
    }

    /**
     * @param User $user
     * @param int  $type
     * @param bool $executeQuery
     *
     * @return OrderedTool[]
     */
    public function getConfigurableDesktopOrderedToolsByUser(User $user, $type = 0, $executeQuery = true)
    {
        return $this->orderedToolRepo->findConfigurableDesktopOrderedToolsByUser(
            $user,
            $type,
            $executeQuery
        );
    }

    /**
     * @param int  $type
     * @param bool $executeQuery
     *
     * @return \Doctrine\ORM\Query|OrderedTool[]
     */
    public function getConfigurableDesktopOrderedToolsByTypeForAdmin($type = 0, $executeQuery = true)
    {
        return $this->orderedToolRepo->findConfigurableDesktopOrderedToolsByTypeForAdmin(
            $type,
            $executeQuery
        );
    }

    public function getLockedConfigurableDesktopOrderedToolsByTypeForAdmin($type = 0, $executeQuery = true)
    {
        return $this->orderedToolRepo->findLockedConfigurableDesktopOrderedToolsByTypeForAdmin(
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

    private function getDesktopToolsConfiguration(array $roles)
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

    public function computeUserOrderedTools(User $user, array $config, array $excludedTools = [])
    {
        $orderedTools = [];
        $desktopTools = $this->toolRepo->findBy(['isDisplayableInDesktop' => true]);
        $excludedToolNames = array_map(function (Tool $tool) {
            return $tool->getName();
        }, $excludedTools);

        foreach ($desktopTools as $tool) {
            $toolName = $tool->getName();

            if (!in_array($toolName, $excludedToolNames)) {
                $orderedTool = $this->orderedToolRepo->findOneBy(['user' => $user, 'tool' => $tool, 'type' => 0]);

                if (empty($orderedTool)) {
                    $orderedTool = new OrderedTool();
                    $orderedTool->setTool($tool);
                    $orderedTool->setUser($user);
                    $orderedTool->setType(0);
                    $orderedTool->setOrder(1);
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
                    if (isset($data['visible'])) {
                        $orderedTool->setVisibleInDesktop($data['visible']);
                    }

                    if (isset($data['locked'])) {
                        $orderedTool->setLocked($data['locked']);
                    }
                    $this->om->persist($orderedTool);
                }
            }
        }
        $this->om->flush();
    }

    public function cleanOldTools()
    {
        $tools = [
           'formalibre_reservation_agenda',
           'formalibre_presence_tool',
           'my-learning-objectives',
           'my_portfolios',
           'message',
           'formalibre_bulletin_tool',
           'innova_video_recorder_tool',
           'innova_audio_recorder_tool',
           'formalibre_pia_tool',
           'badges',
           'my_badges',
           'all_my_badges',
       ];

        foreach ($tools as $tool) {
            $tool = $this->om->getRepository(Tool::class)->findOneBy(['name' => $tool]);

            if ($tool) {
                $this->log('Removing tool '.$tool->getName());
                $this->om->remove($tool);
            }
        }

        $this->om->flush();
    }
}
