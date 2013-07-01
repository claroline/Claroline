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
use Claroline\CoreBundle\Library\Event\ImportToolEvent;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\Exception\ToolPositionAlreadyOccupiedException;
use Claroline\CoreBundle\Manager\Exception\UnremovableToolException;
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
    /** @var ToolRepository */
    private $toolRepo;
    /** @var EventDispatcher */
    private $ed;
    /** @var ClaroUtilities */
    private $utilities;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "writer" = @DI\Inject("claroline.database.writer"),
     *     "orderedToolRepo" = @DI\Inject("ordered_tool_repository"),
     *     "toolRepo" = @DI\Inject("tool_repository"),
     *     "ed" = @DI\Inject("event_dispatcher"),
     *     "utilities" = @DI\Inject("claroline.utilities.misc")
     * })
     */
    public function __construct(
        Writer $writer,
        OrderedToolRepository $orderedToolRepo,
        ToolRepository $toolRepo,
        EventDispatcher $ed,
        ClaroUtilities $utilities
    )
    {
        $this->writer = $writer;
        $this->orderedToolRepo = $orderedToolRepo;
        $this->toolRepo = $toolRepo;
        $this->ed = $ed;
        $this->utilities = $utilities;
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
     * @param User              $manager
     * @param string            $extractPath The path were the template archive was extracted
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
        //if a tool is already at this position, he must go "far away"
        if ($switchTool !== null) {
            //go far away ! Integrety constraints.
            $switchTool->setOrder('99');
            $this->writer->update($switchTool);
        }

        //the tool must exists
        if ($movingTool !== null) {
            $newPosition = $movingTool->getOrder();
            $movingTool->setOrder(intval($position));
            $this->writer->update($movingTool);
        }

         //put the original tool back.
        if ($switchTool !== null) {
            $switchTool->setOrder($newPosition);
            $this->writer->update($switchTool);
        }

        $this->writer->forceFlush();
    }

    public function findOneByName($name)
    {
        return $this->toolRepo->findOneByName($name);
    }

    private function configChecker($conf)
    {
        //no implementation yet
        return true;
    }
}