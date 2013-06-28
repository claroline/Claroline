<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Database\Writer;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Repository\OrderedToolRepository;
use Claroline\CoreBundle\Repository\ToolRepository;
use Claroline\CoreBundle\Writer\ToolWriter;
use Claroline\CoreBundle\Library\Event\ImportToolEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.tool_manager")
 */
class ToolManager
{
    /** @var ToolWriter */
    private $writer;
    /** @var OrderedToolRepository */
    private $orderedToolRepo;
    /** @var ToolRepository */
    private $toolRepo;
    /** @var EventDispatcher */
    private $ed;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "writer" = @DI\Inject("claroline.database.writer"),
     *     "orderedToolRepo" = @DI\Inject("ordered_tool_repository"),
     *     "toolRepo" = @DI\Inject("tool_repository"),
     *     "ed" = @DI\Inject("event_dispatcher")
     * })
     */
    public function __construct(
        Writer $writer,
        OrderedToolRepository $orderedToolRepo,
        ToolRepository $toolRepo,
        EventDispatcher $ed
    )
    {
        $this->writer = $writer;
        $this->orderedToolRepo = $orderedToolRepo;
        $this->toolRepo = $toolRepo;
        $this->ed = $ed;
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
        $this->writer->create(
            $name,
            $plugin,
            $isDisplayableInDesktop,
            $isDisplayableInWorkspace,
            $isExportable,
            $isDesktopRequired,
            $isWorkspaceRequired,
            $hasOption,
            $isVisible,
            $displayName
        );
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
        $this->createOrderedTool($tool, $position, $perms['name'], $manager, $workspace);

        foreach ($perms['perms'] as $role) {
            $tool = $this->toolRepo->findOneBy(array('name' => $name));

            $this->addRole($tool, $roles[$role], $workspace);
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
        $this->writer->createOrderedTool($tool, $position, $name, $workspace, $user);
    }

    public function addRole(Tool $tool, Role $role, AbstractWorkspace $workspace)
    {
        $otr = $this->orderedToolRepo->findOneBy(array('tool' => $tool, 'workspace' => $workspace));
        $this->writer->addRole($otr, $role);
    }

    public function removeRole(Tool $tool, Role $role, AbstractWorkspace $workspace)
    {
        $otr = $this->orderedToolRepo->findOneBy(array('tool' => $tool, 'workspace' => $workspace));
        $this->writer->removeRole($otr, $role);
    }

    public function removeDesktopTool(Tool $tool, User $user)
    {
        if ($tool->getName() === 'parameters') {
            throw new \Exception('You cannot remove the parameter tool from the desktop.');
        }

        $orderedTool = $this->orderedToolRepo->findOneBy(array('user' => $user, 'tool' => $tool));
        $this->writer->remove($orderedTool);
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
}