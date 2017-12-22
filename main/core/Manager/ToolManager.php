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

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\PwsToolConfig;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\Exception\ToolPositionAlreadyOccupiedException;
use Claroline\CoreBundle\Manager\Exception\UnremovableToolException;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Repository\OrderedToolRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Repository\ToolRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("claroline.manager.tool_manager")
 */
class ToolManager
{
    /** @var OrderedToolRepository */
    private $orderedToolRepo;
    /** @var RoleRepository */
    private $roleRepo;
    /** @var ToolRepository */
    private $toolRepo;
    /** @var UserRepository */
    private $userRepo;

    private $adminToolRepo;
    /** @var EventDispatcher */
    private $ed;
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
     * Constructor.
     *
     * @DI\InjectParams({
     *     "ed"                = @DI\Inject("claroline.event.event_dispatcher"),
     *     "utilities"         = @DI\Inject("claroline.utilities.misc"),
     *     "om"                = @DI\Inject("claroline.persistence.object_manager"),
     *     "roleManager"       = @DI\Inject("claroline.manager.role_manager"),
     *     "toolMaskManager"   = @DI\Inject("claroline.manager.tool_mask_decoder_manager"),
     *     "toolRightsManager" = @DI\Inject("claroline.manager.tool_rights_manager"),
     *     "container"         = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        StrictDispatcher $ed,
        ClaroUtilities $utilities,
        ObjectManager $om,
        RoleManager $roleManager,
        ToolMaskDecoderManager $toolMaskManager,
        ToolRightsManager $toolRightsManager,
        ContainerInterface $container
    ) {
        $this->orderedToolRepo = $om->getRepository('ClarolineCoreBundle:Tool\OrderedTool');
        $this->toolRepo = $om->getRepository('ClarolineCoreBundle:Tool\Tool');
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->adminToolRepo = $om->getRepository('ClarolineCoreBundle:Tool\AdminTool');
        $this->pwsToolConfigRepo = $om->getRepository('ClarolineCoreBundle:Tool\PwsToolConfig');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->ed = $ed;
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
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Tool\Tool           $tool
     * @param int                                              $position
     * @param string                                           $name
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Tool\OrderedTool
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
        if ($workspace->getId() !== null) {
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
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param int                               $type
     * @param array                             $excludedTools
     *
     * @return \Claroline\CoreBundle\Entity\Tool\Tool[]
     */
    public function getDisplayedDesktopOrderedTools(
        User $user,
        $type = 0,
        array $excludedTools = []
    ) {
        return count($excludedTools) === 0 ?
            $this->toolRepo->findDesktopDisplayedToolsByUser($user, $type) :
            $this->toolRepo->findDesktopDisplayedToolsWithExclusionByUser(
                $user,
                $excludedTools,
                $type
            );
    }

    /**
     * @param int $type
     *
     * @return \Claroline\CoreBundle\Entity\Tool\OrderedTool[]
     */
    public function getOrderedToolsLockedByAdmin($type = 0)
    {
        return $this->orderedToolRepo->findOrderedToolsLockedByAdmin($type);
    }

    /**
     * Returns the sorted list of OrderedTools for a user.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return \Claroline\CoreBundle\Entity\Tool\OrderedTool
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
     * Returns the sorted list of OrderedTools for configuration in admin.
     *
     * @return \Claroline\CoreBundle\Entity\Tool\OrderedTool
     */
    public function getDesktopToolsConfigurationArrayForAdmin($type = 0)
    {
        $orderedToolList = [];
        $desktopTools = $this->orderedToolRepo
            ->findDisplayableDesktopOrderedToolsByTypeForAdmin($type);

        foreach ($desktopTools as $desktopTool) {
            //this field isn't mapped
            $desktopTool->getTool()->setVisible($desktopTool->isVisibleInDesktop());
            $orderedToolList[$desktopTool->getOrder()] = $desktopTool->getTool();
        }

        $undisplayedTools = $this->toolRepo->findDesktopUndisplayedToolsByTypeForAdmin($type);

        foreach ($undisplayedTools as $tool) {
            //this field isn't mapped
            $tool->setVisible(false);
        }

        $this->addMissingDesktopToolsForAdmin(
            $undisplayedTools,
            count($desktopTools) + 1,
            $type
        );

        return $this->utilities->arrayFill($orderedToolList, $undisplayedTools);
    }

    /**
     * Returns the sorted list of OrderedTools for a user.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return type
     */
    public function getWorkspaceToolsConfigurationArray(Workspace $workspace)
    {
        return $this->getWorkspaceExistingTools($workspace);
    }

    /**
     * Returns an array formatted like this:.
     *
     * array(
     *     'tool' => $tool,
     *     'workspace' => $workspace,
     *     'visibility' => array($roleId => $bool),
     *     'position' => ...
     *     'displayedName' => ...
     * )
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return array
     */
    public function getWorkspaceExistingTools(Workspace $workspace, $type = 0)
    {
        $ot = $this->orderedToolRepo->findBy(
            ['workspace' => $workspace, 'type' => $type],
            ['order' => 'ASC']
        );
        $wsRoles = $this->roleManager->getWorkspaceConfigurableRoles($workspace);
        $existingTools = [];
        $maskDecoders = [];
        $otVisibility = [];
        $otDecoders = [];
        $rights = $this->toolRightsManager->getRightsForOrderedTools($ot);
        $decoders = $this->toolMaskManager->getAllMaskDecoders();

        foreach ($decoders as $decoder) {
            $tool = $decoder->getTool();

            if (!isset($maskDecoders[$tool->getId()])) {
                $maskDecoders[$tool->getId()] = [];
            }
            $maskDecoders[$tool->getId()][$decoder->getName()] = $decoder;

            if (!isset($otDecoders[$tool->getId()])) {
                $otDecoders[$tool->getId()] = [];
            }
            $otDecoders[$tool->getId()][] = $decoder;
        }

        foreach ($rights as $right) {
            $rightOt = $right->getOrderedTool();
            $rightRole = $right->getRole();
            $rightTool = $rightOt->getTool();
            $mask = $right->getMask();

            if (!isset($otVisibility[$rightOt->getId()])) {
                $otVisibility[$rightOt->getId()] = [];
            }
            $otVisibility[$rightOt->getId()][$rightRole->getId()] =
                $this->toolMaskManager->decodeMaskWithDecoders(
                    $mask,
                    $otDecoders[$rightTool->getId()]
                );
        }

        foreach ($ot as $orderedTool) {
            if ($orderedTool->getTool()->isDisplayableInWorkspace()) {
                //creates the visibility array
                foreach ($wsRoles as $role) {
                    $roleVisibility[$role->getId()] = [];
                }

                if (isset($otVisibility[$orderedTool->getId()])) {
                    foreach ($otVisibility[$orderedTool->getId()] as $key => $value) {
                        $roleVisibility[$key] = $value;
                    }
                }

                $existingTools[] = [
                    'tool' => $orderedTool->getTool(),
                    'visibility' => $roleVisibility,
                    'position' => $orderedTool->getOrder(),
                    'workspace' => $workspace,
                    'displayedName' => $orderedTool->getName(),
                    'orderTool' => $orderedTool,
                ];
            }
        }

        return [
            'existingTools' => $existingTools,
            'maskDecoders' => $maskDecoders,
        ];
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
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return array
     */
    public function addMissingWorkspaceTools(Workspace $workspace, $type = 0)
    {
        $undisplayedTools = $this->toolRepo->findUndisplayedToolsByWorkspace($workspace, $type);

        if (count($undisplayedTools) === 0) {
            return;
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
            if ($wot === null) {
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
     * @param \Claroline\CoreBundle\Entity\Tool\Tool $tool
     * @param \Claroline\CoreBundle\Entity\User      $user
     *
     * @throws UnremovableToolException
     */
    public function removeDesktopTool(Tool $tool, User $user, $type = 0)
    {
        if ($tool->getName() === 'parameters') {
            throw new UnremovableToolException('You cannot remove the parameter tool from the desktop.');
        }

        $orderedTool = $this->orderedToolRepo->findOneBy(
            ['user' => $user, 'tool' => $tool, 'type' => $type]
        );
        $orderedTool->setVisibleInDesktop(false);
        $this->om->flush();
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Tool\Tool $tool
     * @param \Claroline\CoreBundle\Entity\User      $user
     * @param int                                    $position
     * @param string                                 $name
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
     * @param \Claroline\CoreBundle\Entity\Tool\Tool           $tool
     * @param int                                              $position
     * @param \Claroline\CoreBundle\Entity\User                $user
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     */
    public function switchToolPosition(
        Tool $tool,
        $position,
        User $user = null,
        Workspace $workspace = null,
        $type = 0
    ) {
        $movingTool = $this->orderedToolRepo->findOneBy(
             ['user' => $user, 'tool' => $tool, 'workspace' => $workspace, 'type' => $type]
         );
        $switchTool = $this->orderedToolRepo->findOneBy(
             ['user' => $user, 'order' => $position, 'workspace' => $workspace, 'type' => $type]
         );

        $newPosition = $movingTool->getOrder();
        //if a tool is already at this position, he must go "far away"
        $switchTool->setOrder($newPosition);
        $movingTool->setOrder(intval($position));
        $this->om->persist($switchTool);
        $this->om->persist($movingTool);
        $this->om->flush();
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
     */
    public function resetToolsVisiblity(
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
     * @param \Claroline\CoreBundle\Entity\Tool\OrderedTool $ot
     */
    public function editOrderedTool(OrderedTool $ot)
    {
        $this->om->persist($ot);
        $this->om->flush();
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Tool\Tool $tool
     */
    public function editTool(Tool $tool)
    {
        $this->om->persist($tool);
        $this->om->flush();
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Tool\Tool[] $tool
     */
    public function getAllTools()
    {
        return $this->toolRepo->findAll();
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $ws
     * @param \Claroline\CoreBundle\Entity\Tool\Tool           $tool
     *
     * @return \Claroline\CoreBundle\Entity\Tool\OrderedTool
     */
    public function getOneByWorkspaceAndTool(Workspace $ws, Tool $tool, $type = 0)
    {
        return $this->orderedToolRepo->findOneBy(
            ['workspace' => $ws, 'tool' => $tool, 'type' => $type]
        );
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
     *Adds the mandatory tools at the user creation.
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
        $this->om->endFlushSuite($user);
    }

    /**
     * @param string $name
     *
     * @return \Claroline\CoreBundle\Entity\Tool\Tool
     */
    public function getOneToolByName($name)
    {
        return $this->toolRepo->findOneByName($name);
    }

    /**
     * Delete to the findBy method.
     *
     * @param array $criterias
     *
     * @return \Claroline\CoreBundle\Entity\Tool\Tool[]
     */
    public function getToolByCriterias(array $criterias)
    {
        return $this->toolRepo->findBy($criterias);
    }

    /**
     * @param string[]                                         $roles
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Tool\OrderedTool[]
     */
    public function getOrderedToolsByWorkspaceAndRoles(
        Workspace $workspace,
        array $roles,
        $type = 0
    ) {
        if ($workspace->isPersonal()) {
            return $this->orderedToolRepo->findPersonalDisplayableByWorkspaceAndRoles(
                $workspace,
                $roles,
                $type
            );
        }

        return $this->orderedToolRepo->findByWorkspaceAndRoles($workspace, $roles, $type);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Tool\OrderedTool[]
     */
    public function getOrderedToolsByWorkspace(Workspace $workspace, $type = 0)
    {
        // pre-load associated tools to save some requests
        $this->toolRepo->findDisplayedToolsByWorkspace($workspace, $type);

        if ($workspace->isPersonal()) {
            return $this->orderedToolRepo->findPersonalDisplayable($workspace, $type);
        }

        return $this->orderedToolRepo->findBy(
            ['workspace' => $workspace, 'type' => $type],
            ['order' => 'ASC']
        );
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

    public function addRoleToAdminTool(AdminTool $tool, Role $role)
    {
        $tool->addRole($role);
        $this->om->persist($tool);
        $this->om->flush();
    }

    public function removeRoleFromAdminTool(AdminTool $tool, Role $role)
    {
        $tool->removeRole($role);
        $this->om->persist($tool);
        $this->om->flush();
    }

    public function getAdminToolByName($name)
    {
        return $this->om->getRepository('Claroline\CoreBundle\Entity\Tool\AdminTool')
            ->findOneByName($name);
    }

    public function getAdminToolsByRoles(array $roles)
    {
        return $this->om->getRepository('Claroline\CoreBundle\Entity\Tool\AdminTool')->findByRoles($roles);
    }

    public function getToolById($id)
    {
        return $this->om->getRepository('ClarolineCoreBundle:Tool\Tool')->find($id);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $user
     *
     * @return \Claroline\CoreBundle\Entity\Tool\OrderedTool[]
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

    private function addMissingDesktopToolsForAdmin(
        array $missingTools,
        $startPosition,
        $type = 0
    ) {
        foreach ($missingTools as $tool) {
            $wot = $this->orderedToolRepo->findOneBy(
                ['user' => null, 'workspace' => null, 'tool' => $tool, 'type' => $type]
            );

            if (!$wot) {
                $orderedTool = new OrderedTool();
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

    public function updateWorkspaceOrderedToolOrder(
        OrderedTool $orderedTool,
        $newOrder,
        $type = 0,
        $executeQuery = true
    ) {
        $order = $orderedTool->getOrder();

        if ($newOrder < $order) {
            $this->orderedToolRepo->incWorkspaceOrderedToolOrderForRange(
                $orderedTool->getWorkspace(),
                $newOrder,
                $order,
                $type,
                $executeQuery
            );
        } else {
            $this->orderedToolRepo->decWorkspaceOrderedToolOrderForRange(
                $orderedTool->getWorkspace(),
                $order,
                $newOrder,
                $type,
                $executeQuery
            );
        }
        $orderedTool->setOrder($newOrder);
        $this->om->persist($orderedTool);
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

    public function reorderAdminOrderedTool(
        OrderedTool $orderedTool,
        $nextOrderedToolId,
        $type = 0
    ) {
        $orderedTools = $this->getConfigurableDesktopOrderedToolsByTypeForAdmin($type);
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

    public function getPersonalWorkspaceToolConfigAsArray()
    {
        $roles = $this->roleManager->getAllPlatformRoles();
        $availableTools = $this->getAvailableWorkspaceTools();
        $data = [];

        foreach ($roles as $role) {
            $data[$role->getId()] = [];
            $perms = $this->pwsToolConfigRepo->findByRole($role);

            if ($perms === [] || $perms === null) {
                foreach ($availableTools as $availableTool) {
                    $data[$role->getId()][$availableTool->getId()] = [
                        'toolId' => $availableTool->getId(),
                        'name' => $availableTool->getName(),
                        'mask' => 0,
                        'id' => 0,
                    ];
                }
            } else {
                $tools = [];
                foreach ($perms as $perm) {
                    $tools[$perm->getTool()->getId()] = [
                        'toolId' => $perm->getTool()->getId(),
                        'name' => $perm->getTool()->getName(),
                        'mask' => $perm->getMask(),
                        'id' => $perm->getId(),
                    ];
                }

                //then we'll have to add the missing roles
                //[ ADD MISSING ROLES HERE]
                foreach ($availableTools as $availableTool) {
                    $found = false;

                    foreach ($tools as $tool) {
                        if ($tool['name'] === $availableTool->getName()) {
                            $found = true;
                        }
                    }

                    if (!$found) {
                        $tools[$availableTool->getId()] = [
                            'toolId' => $availableTool->getId(),
                            'name' => $availableTool->getName(),
                            'mask' => 0,
                            'id' => null,
                        ];
                    }
                }

                $data[$role->getId()] = $tools;
            }
        }

        //order the array so we can use it easily.
        return $data;
    }

    public function getPersonalWorkspaceToolConfigForCurrentUser()
    {
        $token = $this->container->get('security.token_storage')->getToken();

        //maybe from the utils thing
        $roles = $token->getRoles();
        $roleNames = [];

        foreach ($roles as $role) {
            $roleNames[] = $role->getRole();
        }

        return $this->pwsToolConfigRepo->findByRoles($roleNames);
    }

    public function getAllWorkspaceMaskDecodersAsArray()
    {
        $availableTools = $this->getAvailableWorkspaceTools();
        $data = [];

        foreach ($availableTools as $availableTool) {
            $decoders = $this->toolMaskManager
                ->getMaskDecodersByTool($availableTool);
            $decByName = [];

            foreach ($decoders as $decoder) {
                $decByName[$decoder->getName()] = $decoder;
            }

            $data[$availableTool->getId()] = $decByName;
        }

        return $data;
    }

    public function activatePersonalWorkspaceToolPerm($value, Tool $tool, Role $role)
    {
        $value = (int) $value;
        $config = $this->getPersonalWorkspaceToolConfig($tool, $role);
        $config->setMask($config->getMask() | $value);
        $this->om->persist($config);
        $this->om->flush();
    }

    public function removePersonaLWorkspaceToolPerm($value, Tool $tool, Role $role)
    {
        $value = (int) $value;
        $config = $this->getPersonalWorkspaceToolConfig($tool, $role);
        $config->setMask($config->getMask() & ~$value);
        $this->om->persist($config);
        $this->om->flush();
    }

    public function getPersonalWorkspaceToolConfig(Tool $tool, Role $role)
    {
        $pwstc = $this->pwsToolConfigRepo->findBy(['tool' => $tool, 'role' => $role]);

        if ($pwstc) {
            return $pwstc[0];
        }

        $pwstc = new PwsToolConfig();
        $pwstc->setTool($tool);
        $pwstc->setRole($role);
        $pwstc->setMask(0);
        $this->om->persist($pwstc);
        $this->om->flush();

        return $pwstc;
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
        $excludedToolNames[] = 'home';

        if ($type === 1) {
            $excludedToolNames[] = 'parameters';
        }

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
        $excludedToolNames[] = 'home';

        if ($type === 1) {
            $excludedToolNames[] = 'parameters';
        }

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
        $excludedToolNames[] = 'home';

        if ($type === 1) {
            $excludedToolNames[] = 'parameters';
        }

        return $this->orderedToolRepo->findLockedConfigurableDesktopOrderedToolsByTypeForAdmin(
            $excludedToolNames,
            $type,
            $executeQuery
        );
    }

    public function getOneAdminOrderedToolByToolAndType(Tool $tool, $type = 0)
    {
        return $this->orderedToolRepo->findOneBy(
            ['user' => null, 'workspace' => null, 'tool' => $tool, 'type' => $type]
        );
    }

    public function createOrderedToolByToolForAllUsers(
        LoggerInterface $logger,
        Tool $tool,
        $type = 0,
        $isVisible = true
    ) {
        $toolName = $tool->getName();
        $usersQuery = $this->userRepo->findAllEnabledUsers(false);
        $users = $usersQuery->iterate();
        $this->om->startFlushSuite();
        $index = 0;

        $countUser = $this->userRepo->countAllEnabledUsers();

        $logger->info(sprintf('%d users to check tools on.', $countUser));

        foreach ($users as $row) {
            $user = $row[0];
            /** @var \Claroline\CoreBundle\Entity\Tool\OrderedTool[] $orderedTools */
            $orderedTools = $this->orderedToolRepo->findOrderedToolsByToolAndUser(
                $tool,
                $user,
                $type
            );

            if (count($orderedTools) === 0) {
                $orderedTool = new OrderedTool();
                $orderedTool->setName($toolName);
                $orderedTool->setTool($tool);
                $orderedTool->setUser($user);
                $orderedTool->setVisibleInDesktop($isVisible);
                $orderedTool->setOrder(1);
                $orderedTool->setType($type);
                $this->om->persist($orderedTool);
                ++$index;

                if ($index % 100 === 0) {
                    $this->om->forceFlush();
                    $this->om->clear($orderedTool);
                    $logger->info(sprintf('    %d users checked.', 100));
                }
            } else {
                $orderedTool = $orderedTools[0];

                if ($orderedTool->isVisibleInDesktop() !== $isVisible) {
                    $orderedTool->setVisibleInDesktop($isVisible);
                    $this->om->persist($orderedTool);
                    ++$index;

                    if ($index % 100 === 0) {
                        $this->om->forceFlush();
                        $this->om->clear($orderedTool);
                        $logger->info(sprintf('    %d users checked.', 100));
                    }
                }
            }
        }
        if ($index % 100 !== 0) {
            $logger->info(sprintf('    %d users checked.', (100 - $index)));
        }
        $this->om->endFlushSuite();
    }

    public function persistAdminTool(AdminTool $adminTool)
    {
        $this->om->persist($adminTool);
        $this->om->flush();
    }

    public function deleteDuplicatedOldOrderedTools()
    {
        $usersOts = $this->orderedToolRepo->findDuplicatedOldOrderedToolsByUsers();
        $wsOts = $this->orderedToolRepo->findDuplicatedOldOrderedToolsByWorkspaces();
        $exitingUsers = [];

        foreach ($usersOts as $ot) {
            $toolId = $ot->getTool()->getId();
            $userId = $ot->getUser()->getId();

            if (isset($exitingUsers[$toolId])) {
                if (isset($exitingUsers[$toolId][$userId])) {
                    $this->om->remove($ot);
                } else {
                    $exitingUsers[$toolId][$userId] = true;
                }
            } else {
                $exitingUsers[$toolId] = [];
                $exitingUsers[$toolId][$userId] = true;
            }
        }
        $this->om->flush();
        $exitingWorkspaces = [];

        foreach ($wsOts as $ot) {
            $toolId = $ot->getTool()->getId();
            $workspaceId = $ot->getWorkspace()->getId();

            if (isset($exitingWorkspaces[$toolId])) {
                if (isset($exitingWorkspaces[$toolId][$workspaceId])) {
                    $this->om->remove($ot);
                } else {
                    $exitingWorkspaces[$toolId][$workspaceId] = true;
                }
            } else {
                $exitingWorkspaces[$toolId] = [];
                $exitingWorkspaces[$toolId][$workspaceId] = true;
            }
        }
        $this->om->flush();
    }
}
