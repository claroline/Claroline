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

    public function create(
        $name,
        Plugin $plugin,
        $isDisplayableInDesktop,
        $isDisplayableInWorkspace,
        $isExportable,
        $isDesktopRequired,
        $isWorkspaceRequired,
        $hasOption,
        $isVisible,
        $displayName
    )
    {
        $tool = new Tool();
        $tool->setName($name);
        $tool->setPlugin($plugin);
        $tool->setDisplayableInDesktop($isDisplayableInDesktop);
        $tool->setDisplayableInWorkspace($isDisplayableInWorkspace);
        $tool->setExportable($isExportable);
        $tool->setIsDesktopRequired($isDesktopRequired);
        $tool->setIsWorkspaceRequired($isWorkspaceRequired);
        $tool->setHasOptions($hasOption);
        $tool->setVisible($isVisible);
        $tool->setDisplayName($displayName);
        $tool->setClass('test');
        $this->writer->create($tool);
    }

    public function import(
        array $perms,
        array $config,
        array $roles,
        $name,
        AbstractWorkspace $workspace,
        AbstractResource $rootDir,
        User $manager,
        $extractPath,
        $position
    )
    {
        $this->permsChecker($perms);
        $this->configChecker($perms);

        $tool = $this->toolRepo->findOneBy(array('name' => $name));
        $otr = $this->createOrderedTool($tool, $position, $perms['name'], $manager, $workspace);

        foreach ($perms['perms'] as $role) {
            $tool = $this->toolRepo->findOneBy(array('name' => $name));

            $this->addRoleToOrderedTool($otr, $roles[$role]);
        }

        $realPaths = array();

        if (isset($config['files'])) {
            foreach ($config['files'] as $path) {
                $realPaths[] = $extractPath . DIRECTORY_SEPARATOR . $path;
            }
        }

        $event = new ImportToolEvent($workspace, $config, $rootDir, $manager);
        $event->setFiles($realPaths);
        $this->ed->dispatch('tool_' . $name . '_from_template', $event);
    }

    public function createOrderedTool(Tool $tool, $position, $name, User $user = null, AbstractWorkspace $workspace = null)
    {
        $orderedTool = new OrderedTool();
        $orderedTool->setWorkspace($workspace);
        $orderedTool->setName($name);
        $orderedTool->setOrder($position);
        $orderedTool->setUser($user);
        $orderedTool->setTool($tool);
        $this->writer->create($orderedTool);

        return $orderedTool;
    }

    public function addRole($tool, Role $role, AbstractWorkspace $workspace)
    {
        $otr = $this->orderedToolRepo->findOneBy(array('tool' => $tool, 'workspace' => $workspace));
        $otr->addRole($role);
        $this->writer->update($otr);
    }

    private function addRoleToOrderedTool(OrderedTool $otr, $role)
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

    private function removeRoleFromOrderedTool(OrderedTool $otr, $role)
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
            $desktopTool->getTool()->setVisible(true);
            $orderedToolList[$desktopTool->getOrder()] = $desktopTool->getTool();
        }

         $undisplayedTools = $this->toolRepo->findDesktopUndisplayedToolsByUser($user);

        foreach ($undisplayedTools as $tool) {
            $tool->setVisible(false);
        }

        return $this->utilities->arrayFill($orderedToolList, $undisplayedTools);
    }

    public function removeDesktopTool(Tool $tool, User $user)
    {
        if ($tool->getName() === 'parameters') {
            throw new \Exception('You cannot remove the parameter tool from the desktop.');
        }

        $orderedTool = $this->orderedToolRepo->findOneBy(array('user' => $user, 'tool' => $tool));
        $this->writer->delete($orderedTool);
    }

    public function addDesktopTool(Tool $tool, User $user, $position)
    {
        $switchTool = $this->orderedToolRepo->findOneBy(array('user' => $user, 'order' => $position));

        if ($switchTool != null) {
            throw new \RuntimeException('A tool already exists at this position');
        }

        $desktopTool = new OrderedTool();
        $desktopTool->setUser($user);
        $desktopTool->setTool($tool);
        $desktopTool->setOrder($position);
        $desktopTool->setName($tool->getName());
        $this->writer->create($desktopTool);
    }

    public function order(array $tools)
    {

    }

    public function permsChecker($conf)
    {
        //no implementation yet
        return true;
    }

    public function configChecker($conf)
    {
        //no implementation yet
        return true;
    }

    public function addRequiredToolsToUser(User $user)
    {
        $requiredTools[] = $this->toolRepo->findOneBy(array('name' => 'home'));
        $requiredTools[] = $this->toolRepo->findOneBy(array('name' => 'resource_manager'));
        $requiredTools[] = $this->toolRepo->findOneBy(array('name' => 'parameters'));

        $i = 1;

        foreach ($requiredTools as $requiredTool) {
            $this->createOrderedTool($requiredTool, $i, $requiredTool->getName(), $user);
            $i++;
        }
        $this->writer->update($user);
    }
}