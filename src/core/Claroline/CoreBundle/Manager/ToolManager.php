<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Repository\OrderedToolRepository;
use Claroline\CoreBundle\Repository\ToolRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\Exception\ToolPositionAlreadyOccupiedException;
use Claroline\CoreBundle\Manager\Exception\UnremovableToolException;
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Component\Translation\Translator;
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
    /** @var EventDispatcher */
    private $ed;
    /** @var ClaroUtilities */
    private $utilities;
    /** @var Translator */
    private $translator;
    /** @var ObjectManager */
    private $om;
    /** @var RoleManager */
    private $roleManager;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "ed"          = @DI\Inject("claroline.event.event_dispatcher"),
     *     "utilities"   = @DI\Inject("claroline.utilities.misc"),
     *     "translator"  = @DI\Inject("translator"),
     *     "om"          = @DI\Inject("claroline.persistence.object_manager"),
     *     "roleManager" = @DI\Inject("claroline.manager.role_manager")
     * })
     */
    public function __construct(
        StrictDispatcher $ed,
        ClaroUtilities $utilities,
        Translator $translator,
        ObjectManager $om,
        RoleManager $roleManager
    )
    {
        $this->orderedToolRepo = $om->getRepository('ClarolineCoreBundle:Tool\OrderedTool');
        $this->toolRepo = $om->getRepository('ClarolineCoreBundle:Tool\Tool');
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->ed = $ed;
        $this->utilities = $utilities;
        $this->translator = $translator;
        $this->om = $om;
        $this->roleManager = $roleManager;
    }

    public function create(Tool $tool)
    {
        $this->om->persist($tool);
        $this->om->flush();
    }

    /**
     * Import a tool in a workspace from the template archive.
     *
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
        array $generatedRoles,
        $name,
        AbstractWorkspace $workspace,
        AbstractResource $rootDir,
        Tool $tool,
        User $manager,
        $position,
        $archive
    )
    {
        $this->configChecker($config);
        $otr = $this->addWorkspaceTool($tool, $position, $name, $workspace);

        foreach ($roles as $role) {
            $this->addRoleToOrderedTool($otr, $role);
        }

        $filePaths = $this->extractFiles($archive, $config);

        $this->ed->dispatch(
            'tool_' . $tool->getName() . '_from_template', 'ImportTool',
            array($workspace, $config, $rootDir->getResourceNode(), $manager, $filePaths, $generatedRoles)
        );
    }

    public function addWorkspaceTool(Tool $tool, $position, $name, AbstractWorkspace $workspace)
    {
        $switchTool = null;

        /**
         * At the workspace creation, the workspace id is still null because we only flush once at the very end.
         */
        if ($workspace->getId() !== null) {
            $switchTool = $this->orderedToolRepo->findOneBy(array('workspace' => $workspace, 'order' => $position));
        }

        if ($switchTool !== null) {
            throw new ToolPositionAlreadyOccupiedException('A tool already exists at this position');
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

    public function addRole(Tool $tool, Role $role, AbstractWorkspace $workspace)
    {
        $otr = $this->orderedToolRepo->findOneBy(array('tool' => $tool, 'workspace' => $workspace));
        $otr->addRole($role);
        $this->om->persist($otr);
        $this->om->flush();
    }

    public function addRoleToOrderedTool(OrderedTool $otr, Role $role)
    {
        $otr->addRole($role);
        $this->om->persist($otr);
        $this->om->flush();
    }

    public function removeRole(Tool $tool, Role $role, AbstractWorkspace $workspace)
    {
        $otr = $this->orderedToolRepo->findOneBy(array('tool' => $tool, 'workspace' => $workspace));
        $otr->removeRole($role);
        $this->om->persist($otr);
        $this->om->flush();
    }

    public function removeRoleFromOrderedTool(OrderedTool $otr, Role $role)
    {
        $otr->removeRole($role);
        $this->om->persist($otr);
        $this->om->flush();
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
        $wsRoles = $this->roleManager->getWorkspaceRoles($workspace);
        $existingTools = array();

        foreach ($ot as $orderedTool) {
            if ($orderedTool->getTool()->isDisplayableInWorkspace()) {
                //creates the visibility array
                foreach ($wsRoles as $role) {
                    $isVisible = false;
                    //is the tool visible for a role in a workspace ?
                    foreach ($orderedTool->getRoles() as $toolRole) {
                        if ($toolRole === $role) {
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
        $wsRoles = $this->roleManager->getWorkspaceRoles($workspace);

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
        $this->om->remove($orderedTool);
        $this->om->flush();
    }

    public function addDesktopTool(Tool $tool, User $user, $position, $name)
    {
        $switchTool = $this->orderedToolRepo->findOneBy(array('user' => $user, 'order' => $position));

        if ($switchTool != null) {
            throw new ToolPositionAlreadyOccupiedException('A tool already exists at this position');
        }

        $desktopTool = $this->om->factory('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $desktopTool->setUser($user);
        $desktopTool->setTool($tool);
        $desktopTool->setOrder($position);
        $desktopTool->setName($name);
        $this->om->persist($desktopTool);
        $this->om->flush();
    }

    public function move(Tool $tool, $position, User $user = null, AbstractWorkspace $workspace = null)
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

    public function editOrderedTool(OrderedTool $ot)
    {
        $this->om->persist($ot);
        $this->om->flush();
    }

    public function editTool(Tool $tool)
    {
        $this->om->persist($tool);
        $this->om->flush();
    }

    public function getAllTools()
    {
        return $this->toolRepo->findAll();
    }

    public function getOneByWorkspaceAndTool(AbstractWorkspace $ws, Tool $tool)
    {
        return $this->orderedToolRepo->findOneBy(array('workspace' => $ws, 'tool' => $tool));
    }

    private function configChecker($conf)
    {
        //no implementation yet
        return true;
    }

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

    public function getOneToolByName($name)
    {
        return $this->toolRepo->findOneByName($name);
    }

    public function getToolByCriterias(array $criterias)
    {
        return $this->toolRepo->findBy($criterias);
    }

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

    public function getOrderedToolsByWorkspaceAndRoles(AbstractWorkspace $workspace, array $roles)
    {
        return $this->orderedToolRepo->findByWorkspaceAndRoles($workspace, $roles);
    }

    public function getDisplayedByRolesAndWorkspace(array $roles, AbstractWorkspace $workspace)
    {
        return $this->toolRepo->findDisplayedByRolesAndWorkspace($roles, $workspace);
    }
}
