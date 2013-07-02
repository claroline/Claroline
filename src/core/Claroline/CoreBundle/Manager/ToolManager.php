<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Database\Writer;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Repository\OrderedToolRepository;
use Claroline\CoreBundle\Repository\ToolRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Library\Event\ImportToolEvent;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\Exception\ToolPositionAlreadyOccupiedException;
use Claroline\CoreBundle\Manager\Exception\UnremovableToolException;
use Symfony\Component\Translation\Translator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.tool_manager")
 */
class ToolManager
{
    /** @var Writer */
    private $writer;
    /** @var OrderedToolRepository */
    private $orderedToolRepo;
    /** @var RoleRepository */
    private $roleRepo;
    /** @var ToolRepository */
    private $toolRepo;
    /** @var EventDispatcher */
    private $ed;
    /** @var ClaroUtilities */
    private $utilities;
    /** @var Translator */
    private $translator;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "writer"          = @DI\Inject("claroline.database.writer"),
     *     "orderedToolRepo" = @DI\Inject("ordered_tool_repository"),
     *     "toolRepo"        = @DI\Inject("tool_repository"),
     *     "ed"              = @DI\Inject("event_dispatcher"),
     *     "utilities"       = @DI\Inject("claroline.utilities.misc"),
     *     "roleRepo"        = @DI\Inject("role_repository"),
     *     "translator"      = @DI\Inject("translator")
     * })
     */
    public function __construct(
        Writer $writer,
        OrderedToolRepository $orderedToolRepo,
        ToolRepository $toolRepo,
        EventDispatcher $ed,
        ClaroUtilities $utilities,
        RoleRepository $roleRepo,
        Translator $translator
    )
    {
        $this->writer = $writer;
        $this->orderedToolRepo = $orderedToolRepo;
        $this->toolRepo = $toolRepo;
        $this->ed = $ed;
        $this->utilities = $utilities;
        $this->roleRepo = $roleRepo;
        $this->translator = $translator;
    }

    public function create(Tool $tool)
    {
        $this->writer->create($tool);
    }

    /**
     * Import a tool in a workspace from the template archive.
     *
     * @param array             $perms
     * @param array             $config
     * @param array             $roles
     * @param string            $name
     * @param AbstractWorkspace $workspace
     * @param AbstractResource  $rootDir
     * @param Tool              $tool
     * @param User              $manager
     * @param integer           $position
     */
    public function import(
        array $config,
        array $roles,
        array $filePaths,
        $name,
        AbstractWorkspace $workspace,
        AbstractResource $rootDir,
        Tool $tool,
        User $manager,
        $position
    )
    {
        $this->configChecker($config);
        $otr = $this->addWorkspaceTool($tool, $position, $name, $workspace);

        foreach ($roles as $role) {
            $this->addRoleToOrderedTool($otr, $role);
        }

        $event = new ImportToolEvent($workspace, $config, $rootDir, $manager);
        $event->setFiles($filePaths);
        $this->ed->dispatch('tool_' . $tool->getName() . '_from_template', $event);
    }

    public function addWorkspaceTool(Tool $tool, $position, $name, AbstractWorkspace $workspace)
    {
        $switchTool = $this->orderedToolRepo->findOneBy(array('workspace' => $workspace, 'order' => $position));

        if ($switchTool != null) {
            throw new ToolPositionAlreadyOccupiedException('A tool already exists at this position');
        }

        $orderedTool = new OrderedTool();
        $orderedTool->setWorkspace($workspace);
        $orderedTool->setName($name);
        $orderedTool->setOrder($position);
        $orderedTool->setTool($tool);
        $this->writer->create($orderedTool);

        return $orderedTool;
    }

    public function addRole(Tool $tool, Role $role, AbstractWorkspace $workspace)
    {
        $otr = $this->orderedToolRepo->findOneBy(array('tool' => $tool, 'workspace' => $workspace));
        $otr->addRole($role);
        $this->writer->update($otr);
    }

    public function addRoleToOrderedTool(OrderedTool $otr, Role $role)
    {
        $otr->addRole($role);
        $this->writer->update($otr);
    }

    public function removeRole(Tool $tool, Role $role, AbstractWorkspace $workspace)
    {
        $otr = $this->orderedToolRepo->findOneBy(array('tool' => $tool, 'workspace' => $workspace));
        $otr->removeRole($role);
        $this->writer->update($otr);
    }

    public function removeRoleFromOrderedTool(OrderedTool $otr, Role $role)
    {
        $otr->removeRole($role);
        $this->writer->update($otr);
    }

    public function getDisplayedDesktopOrderedTools(User $user)
    {
         return $this->toolRepo->findDesktopDisplayedToolsByUser($user);
    }

    public function getDesktopToolsConfigurationArray(User $user)
    {
        $orderedToolList = array();
        $desktopTools = $this->orderedToolRepo->findByUser($user);

        foreach ($desktopTools as $desktopTool) {
            //this field isn't mapped
            $desktopTool->getTool()->setVisible(true);
            $orderedToolList[$desktopTool->getOrder()] = $desktopTool->getTool();
        }

         $undisplayedTools = $this->toolRepo->findDesktopUndisplayedToolsByUser($user);

        foreach ($undisplayedTools as $tool) {
            //this field isn't mapped
            $tool->setVisible(false);
        }

        return $this->utilities->arrayFill($orderedToolList, $undisplayedTools);
    }

    public function getWorkspaceToolsConfigurationArray(AbstractWorkspace $workspace)
    {
        $missingTools = $this->addMissingWorkspaceTools($workspace);
        $existingTools = $this->getWorkspaceExistingTools($workspace);
        $tools = array_merge($existingTools, $missingTools);

        return $tools;
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
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return array
     */
    public function getWorkspaceExistingTools(AbstractWorkspace $workspace)
    {
        $ot = $this->orderedToolRepo->findBy(array('workspace' => $workspace), array('order' => 'ASC'));
        $wsRoles = $this->getWorkspaceRoles($workspace);
        $existingTools = array();

        foreach ($ot as $orderedTool) {
            if ($orderedTool->getTool()->isDisplayableInWorkspace()) {
                //creates the visibility array
                foreach ($wsRoles as $role) {
                    $isVisible = false;
                    //is the tool visible for a role in a workspace ?
                    foreach ($orderedTool->getRoles() as $toolRole) {
                        if ($toolRole == $role) {
                            $isVisible = true;
                        }
                    }

                    $roleVisibility[$role->getId()] = $isVisible;
                }

                $existingTools[] = array(
                    'tool' => $orderedTool->getTool(),
                    'visibility' => $roleVisibility,
                    'position' => $orderedTool->getOrder(),
                    'workspace' => $workspace,
                    'displayedName' => $orderedTool->getName()
                );
            }
        }

        return $existingTools;
    }

    /**
     * Adds the tools missing in the database for a workspace.
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
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return array
     */
    public function addMissingWorkspaceTools(AbstractWorkspace $workspace)
    {
        $undisplayedTools = $this->toolRepo->findUndisplayedToolsByWorkspace($workspace);
        $initPos = $this->toolRepo->countDisplayedToolsByWorkspace($workspace);
        $initPos++;
        $missingTools = array();
        $wsRoles = $this->getWorkspaceRoles($workspace);

        foreach ($undisplayedTools as $undisplayedTool) {
            if ($undisplayedTool->isDisplayableInWorkspace()) {
                $wot = $this->orderedToolRepo->findOneBy(array('workspace' => $workspace, 'tool' => $undisplayedTool));

                //create a WorkspaceOrderedTool for each Tool that hasn't already one
                if ($wot === null) {
                    $this->addWorkspaceTool(
                        $undisplayedTool,
                        $initPos,
                        $this->translator->trans($undisplayedTool->getName(), array(), 'tools'),
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
        }

        return $missingTools;
    }

    public function removeDesktopTool(Tool $tool, User $user)
    {
        if ($tool->getName() === 'parameters') {
            throw new UnremovableToolException('You cannot remove the parameter tool from the desktop.');
        }

        $orderedTool = $this->orderedToolRepo->findOneBy(array('user' => $user, 'tool' => $tool));
        $this->writer->delete($orderedTool);
    }

    public function addDesktopTool(Tool $tool, User $user, $position, $name)
    {
        $switchTool = $this->orderedToolRepo->findOneBy(array('user' => $user, 'order' => $position));

        if ($switchTool != null) {
            throw new ToolPositionAlreadyOccupiedException('A tool already exists at this position');
        }

        $desktopTool = new OrderedTool();
        $desktopTool->setUser($user);
        $desktopTool->setTool($tool);
        $desktopTool->setOrder($position);
        $desktopTool->setName($name);
        $this->writer->create($desktopTool);
    }

    public function move(Tool $tool, $position, User $user = null, AbstractWorkspace $workspace = null)
    {
         $movingTool = $this->orderedToolRepo
             ->findOneBy(array('user' => $user, 'tool' => $tool, 'workspace' => $workspace));
         $switchTool = $this->orderedToolRepo
             ->findOneBy(array('user' => $user, 'order' => $position, 'workspace' => $workspace));

         $this->writer->suspendFlush();
         $newPosition = $movingTool->getOrder();
         //if a tool is already at this position, he must go "far away"
         $switchTool->setOrder($newPosition);
         $movingTool->setOrder(intval($position));
         $this->writer->update($switchTool);
         $this->writer->update($movingTool);

         $this->writer->forceFlush();
    }

    public function editOrderedTool(OrderedTool $ot)
    {
        $this->writer->update($ot);
    }

    public function editTool(Tool $tool)
    {
        $this->writer->update($tool);
    }

    public function findOneByName($name)
    {
        return $this->toolRepo->findOneByName($name);
    }

    public function findAll()
    {
        return $this->toolRepo->findAll();
    }

    public function findOneByWorkspaceAndTool(AbstractWorkspace $ws, Tool $tool)
    {
        return $this->orderedToolRepo->findOneBy(array('workspace' => $ws, 'tool' => $tool));
    }

    public function getWorkspaceRoles(AbstractWorkspace $workspace)
    {
        return array_merge(
            $this->roleRepo->findByWorkspace($workspace),
            $this->roleRepo->findBy(array('name' => 'ROLE_ANONYMOUS'))
        );
    }

    private function configChecker($conf)
    {
        //no implementation yet
        return true;
    }
}