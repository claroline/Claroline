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

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Repository\OrderedToolRepository;
use Claroline\CoreBundle\Repository\ToolRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\Exception\ToolPositionAlreadyOccupiedException;
use Claroline\CoreBundle\Manager\Exception\UnremovableToolException;
use Claroline\CoreBundle\Manager\ToolMaskDecoderManager;
use Claroline\CoreBundle\Manager\ToolRightsManager;
use Claroline\CoreBundle\Event\StrictDispatcher;
use JMS\DiExtraBundle\Annotation as DI;

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

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "ed"                = @DI\Inject("claroline.event.event_dispatcher"),
     *     "utilities"         = @DI\Inject("claroline.utilities.misc"),
     *     "om"                = @DI\Inject("claroline.persistence.object_manager"),
     *     "roleManager"       = @DI\Inject("claroline.manager.role_manager"),
     *     "toolMaskManager"   = @DI\Inject("claroline.manager.tool_mask_decoder_manager"),
     *     "toolRightsManager" = @DI\Inject("claroline.manager.tool_rights_manager")
     * })
     */
    public function __construct(
        StrictDispatcher $ed,
        ClaroUtilities $utilities,
        ObjectManager $om,
        RoleManager $roleManager,
        ToolMaskDecoderManager $toolMaskManager,
        ToolRightsManager $toolRightsManager
    )
    {
        $this->orderedToolRepo = $om->getRepository('ClarolineCoreBundle:Tool\OrderedTool');
        $this->toolRepo = $om->getRepository('ClarolineCoreBundle:Tool\Tool');
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->adminToolRepo = $om->getRepository('ClarolineCoreBundle:Tool\AdminTool');
        $this->ed = $ed;
        $this->utilities = $utilities;
        $this->om = $om;
        $this->roleManager = $roleManager;
        $this->toolMaskManager = $toolMaskManager;
        $this->toolRightsManager = $toolRightsManager;
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
     * @param \Claroline\CoreBundle\Entity\Tool\Tool                   $tool
     * @param integer                                                  $position
     * @param string                                                   $name
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Tool\OrderedTool
     *
     * @throws ToolPositionAlreadyOccupiedException
     */
    public function addWorkspaceTool(Tool $tool, $position, $name, Workspace $workspace)
    {
        $switchTool = null;

        // At the workspace creation, the workspace id is still null because we only flush once at the very end.
        if ($workspace->getId() !== null) {
            $switchTool = $this->orderedToolRepo->findOneBy(array('workspace' => $workspace, 'order' => $position));
        }

        while (!is_null($switchTool)) {
            $position++;
            $switchTool = $this->orderedToolRepo->findOneBy(array('workspace' => $workspace, 'order' => $position));
        }

        $orderedTool = $this->om->factory('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $orderedTool->setWorkspace($workspace);
        $orderedTool->setName($name);
        $orderedTool->setOrder($position);
        $orderedTool->setTool($tool);
        $this->om->persist($orderedTool);
        $this->om->flush();

        return $orderedTool;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return \Claroline\CoreBundle\Entity\Tool\Tool
     */
    public function getDisplayedDesktopOrderedTools(User $user)
    {
         return $this->toolRepo->findDesktopDisplayedToolsByUser($user);
    }

    /**
     * Returns the sorted list of OrderedTools for a user.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return \Claroline\CoreBundle\Entity\Tool\OrderedTool
     */
    public function getDesktopToolsConfigurationArray(User $user)
    {
        $orderedToolList = array();
        $desktopTools = $this->orderedToolRepo->findByUser($user);

        foreach ($desktopTools as $desktopTool) {
            //this field isn't mapped
            $desktopTool->getTool()->setVisible($desktopTool->isVisibleInDesktop());
            $orderedToolList[$desktopTool->getOrder()] = $desktopTool->getTool();
        }

        $undisplayedTools = $this->toolRepo->findDesktopUndisplayedToolsByUser($user);

        foreach ($undisplayedTools as $tool) {
            //this field isn't mapped
            $tool->setVisible(false);
        }

        $this->addMissingDesktopTools($user, $undisplayedTools, count($desktopTools) + 1);

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
     * Returns an array formatted like this:
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
    public function getWorkspaceExistingTools(Workspace $workspace)
    {
        $ot = $this->orderedToolRepo->findBy(array('workspace' => $workspace), array('order' => 'ASC'));
        $wsRoles = $this->roleManager->getWorkspaceConfigurableRoles($workspace);
        $existingTools = array();
        $maskDecoders = array();
        $otVisibility = array();
        $otDecoders = array();
        $rights = $this->toolRightsManager->getRightsForOrderedTools($ot);
        $decoders = $this->toolMaskManager->getAllMaskDecoders();

        foreach ($decoders as $decoder) {
            $tool = $decoder->getTool();

            if (!isset($maskDecoders[$tool->getId()])) {
                $maskDecoders[$tool->getId()] = array();
            }
            $maskDecoders[$tool->getId()][$decoder->getName()] = $decoder;

            if (!isset($otDecoders[$tool->getId()])) {
                $otDecoders[$tool->getId()] = array();
            }
            $otDecoders[$tool->getId()][] = $decoder;
        }

        foreach ($rights as $right) {
            $rightOt = $right->getOrderedTool();
            $rightRole = $right->getRole();
            $rightTool = $rightOt->getTool();
            $mask = $right->getMask();

            if (!isset($otVisibility[$rightOt->getId()])) {
                $otVisibility[$rightOt->getId()] = array();
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
                    $roleVisibility[$role->getId()] = array();
                }

                if (isset($otVisibility[$orderedTool->getId()])) {

                    foreach ($otVisibility[$orderedTool->getId()] as $key => $value) {
                        $roleVisibility[$key] = $value;
                    }
                }

                $existingTools[] = array(
                    'tool' => $orderedTool->getTool(),
                    'visibility' => $roleVisibility,
                    'position' => $orderedTool->getOrder(),
                    'workspace' => $workspace,
                    'displayedName' => $orderedTool->getName(),
                    'orderTool' => $orderedTool
                );
            }
        }

        return array(
            'existingTools' => $existingTools,
            'maskDecoders' => $maskDecoders
        );
    }

    /**
     * Adds the tools missing in the database for a workspace.
     * Returns an array formatted like this:
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
    public function addMissingWorkspaceTools(Workspace $workspace)
    {
        $undisplayedTools = $this->toolRepo->findUndisplayedToolsByWorkspace($workspace);

        if (count($undisplayedTools) === 0) {
            return;
        }

        $initPos = $this->toolRepo->countDisplayedToolsByWorkspace($workspace);
        $initPos++;
        $missingTools = array();
        $wsRoles = $this->roleManager->getWorkspaceConfigurableRoles($workspace);

        foreach ($undisplayedTools as $undisplayedTool) {
            $wot = $this->orderedToolRepo->findOneBy(array('workspace' => $workspace, 'tool' => $undisplayedTool));

            //create a WorkspaceOrderedTool for each Tool that hasn't already one
            if ($wot === null) {
                $this->addWorkspaceTool(
                    $undisplayedTool,
                    $initPos,
                    $undisplayedTool->getName(),
                    $workspace
                );
            } else {
                continue;
            }

            foreach ($wsRoles as $role) {
                $roleVisibility[$role->getId()] = false;
            }

            $missingTools[] = array(
                'tool' => $undisplayedTool,
                'workspace' => $workspace,
                'position' => $initPos,
                'visibility' => $roleVisibility,
                'displayedName' => $undisplayedTool->getName()
            );

            $initPos++;
        }

        return $missingTools;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Tool\Tool $tool
     * @param \Claroline\CoreBundle\Entity\User      $user
     *
     * @throws UnremovableToolException
     */
    public function removeDesktopTool(Tool $tool, User $user)
    {
        if ($tool->getName() === 'parameters') {
            throw new UnremovableToolException('You cannot remove the parameter tool from the desktop.');
        }

        $orderedTool = $this->orderedToolRepo->findOneBy(array('user' => $user, 'tool' => $tool));
        $orderedTool->setVisibleInDesktop(false);
        $this->om->flush();
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Tool\Tool $tool
     * @param \Claroline\CoreBundle\Entity\User      $user
     * @param integer                                $position
     * @param string                                 $name
     *
     * @throws ToolPositionAlreadyOccupiedException
     */
    public function addDesktopTool(Tool $tool, User $user, $position, $name)
    {
        $switchTool = $this->orderedToolRepo->findOneBy(array('user' => $user, 'order' => $position));

        if (!$switchTool) {
            $desktopTool = $this->om->factory('Claroline\CoreBundle\Entity\Tool\OrderedTool');
            $desktopTool->setUser($user);
            $desktopTool->setTool($tool);
            $desktopTool->setOrder($position);
            $desktopTool->setName($name);
            $desktopTool->setVisibleInDesktop(true);
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
     * @param \Claroline\CoreBundle\Entity\Tool\Tool                   $tool
     * @param integer                                                  $position
     * @param \Claroline\CoreBundle\Entity\User                        $user
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     */
    public function switchToolPosition(Tool $tool, $position, User $user = null, Workspace $workspace = null)
    {
         $movingTool = $this->orderedToolRepo
             ->findOneBy(array('user' => $user, 'tool' => $tool, 'workspace' => $workspace));
         $switchTool = $this->orderedToolRepo
             ->findOneBy(array('user' => $user, 'order' => $position, 'workspace' => $workspace));

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
    public function setToolPosition(Tool $tool, $position, User $user = null, Workspace $workspace = null)
    {
        $movingTool = $this->orderedToolRepo
            ->findOneBy(array('user' => $user, 'tool' => $tool, 'workspace' => $workspace));
        $movingTool->setOrder($position);
        $this->om->persist($movingTool);
        $this->om->flush();
    }

    /**
     * Resets the tool visibility
     *
     * @param User      $user
     * @param Workspace $workspace
     */
    public function resetToolsVisiblity(User $user = null, Workspace $workspace = null)
    {
        $orderedTools = $this->orderedToolRepo->findBy(array('user' => $user, 'workspace' => $workspace));

        foreach ($orderedTools as $orderedTool) {

            if ($user && $orderedTool->getTool()->getName() !== 'parameters') {
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
     * @param \Claroline\CoreBundle\Entity\Tool\Tool                   $tool
     *
     * @return \Claroline\CoreBundle\Entity\Tool\OrderedTool
     */
    public function getOneByWorkspaceAndTool(Workspace $ws, Tool $tool)
    {
        return $this->orderedToolRepo->findOneBy(array('workspace' => $ws, 'tool' => $tool));
    }

    private function configChecker($conf)
    {
        //no implementation yet
        return true;
    }

    /**
     * Sets a tool visible for a user in the desktop.
     *
     * @param Tool $tool
     * @param User $user
     */
    public function setDesktopToolVisible(Tool $tool, User $user)
    {
        $orderedTool = $this->orderedToolRepo->findOneBy(array('user' => $user, 'tool' => $tool));
        $orderedTool->setVisibleInDesktop(true);
        $this->om->persist($orderedTool);
        $this->om->flush();
    }

    /**
     *Adds the mandatory tools at the user creation.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     */
    public function addRequiredToolsToUser(User $user)
    {
        $requiredTools[] = $this->toolRepo->findOneBy(array('name' => 'home'));
        $requiredTools[] = $this->toolRepo->findOneBy(array('name' => 'resource_manager'));
        $requiredTools[] = $this->toolRepo->findOneBy(array('name' => 'parameters'));

        $position = 1;
        $this->om->startFlushSuite();

        foreach ($requiredTools as $requiredTool) {
            $this->addDesktopTool($requiredTool, $user, $position, $requiredTool->getName());
            $position++;
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
     * Extract the files from a the template configuration array
     *
     * @param string $archpath
     * @param array  $confTools
     *
     * @return array
     */
    public function extractFiles($archpath, $confTools)
    {
        $extractPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('claro_ws_tmp_', true);
        $archive = $this->om->factory('ZipArchive');
        $archive->open($archpath);
        $archive->extractTo($extractPath);
        $realPaths = array();

        if (isset($confTools['files'])) {
            foreach ($confTools['files'] as $path) {
                $realPaths[] = $extractPath . DIRECTORY_SEPARATOR . $path;
            }
        }

        return $realPaths;
    }

    /**
     * @param string[]                                                 $roles
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Tool\OrderedTool[]
     */
    public function getOrderedToolsByWorkspaceAndRoles(Workspace $workspace, array $roles)
    {
        return $this->orderedToolRepo->findByWorkspaceAndRoles($workspace, $roles);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Tool\OrderedTool[]
     */
    public function getOrderedToolsByWorkspace(Workspace $workspace)
    {
        // pre-load associated tools to save some requests
        $this->toolRepo->findDisplayedToolsByWorkspace($workspace);

        return $this->orderedToolRepo->findBy(array('workspace' => $workspace), array('order' => 'ASC'));
    }

    /**
     * @param string[]                                                 $roles
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Tool\Tool
     */
    public function getDisplayedByRolesAndWorkspace(array $roles, Workspace $workspace)
    {
        return $this->toolRepo->findDisplayedByRolesAndWorkspace($roles, $workspace);
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
    public function getOrderedToolsByUser(User $user)
    {
        return $this->orderedToolRepo->findBy(
            array('user' => $user),
            array('order' => 'ASC')
        );
    }

    private function addMissingDesktopTools(User $user, array $missingTools, $startPosition)
    {
        foreach ($missingTools as $tool) {
            $wot = $this->orderedToolRepo->findOneBy(array('user' => $user, 'tool' => $tool));

            if (!$wot) {
                $orderedTool = new OrderedTool();
                $orderedTool->setUser($user);
                $orderedTool->setName($tool->getName());
                $orderedTool->setOrder($startPosition);
                $orderedTool->setTool($tool);
                $orderedTool->setVisibleInDesktop(false);
                $this->om->persist($orderedTool);
            }

            $startPosition++;
        }

        $this->om->flush();
    }

    public function updateWorkspaceOrderedToolOrder(
        OrderedTool $orderedTool,
        $newOrder
    )
    {
        $order = $orderedTool->getOrder();

        if ($newOrder < $order) {
            $this->orderedToolRepo->incWorkspaceOrderedToolOrderForRange(
                $orderedTool->getWorkspace(),
                $newOrder,
                $order
            );
        } else {
            $this->orderedToolRepo->decWorkspaceOrderedToolOrderForRange(
                $orderedTool->getWorkspace(),
                $order,
                $newOrder
            );
        }
        $orderedTool->setOrder($newOrder);
        $this->om->persist($orderedTool);
        $this->om->flush();
    }

    public function updateDesktopOrderedToolOrder(
        OrderedTool $orderedTool,
        $newOrder
    )
    {
        $order = $orderedTool->getOrder();

        if ($newOrder < $order) {
            $this->orderedToolRepo->incDesktopOrderedToolOrderForRange(
                $orderedTool->getUser(),
                $newOrder,
                $order
            );
        } else {
            $this->orderedToolRepo->decDesktopOrderedToolOrderForRange(
                $orderedTool->getUser(),
                $order,
                $newOrder
            );
        }
        $orderedTool->setOrder($newOrder);
        $this->om->persist($orderedTool);
        $this->om->flush();
    }
}
