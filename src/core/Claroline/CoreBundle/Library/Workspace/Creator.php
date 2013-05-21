<?php

namespace Claroline\CoreBundle\Library\Workspace;

use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Event\ImportToolEvent;
use Claroline\CoreBundle\Library\Event\LogWorkspaceCreateEvent;
use Claroline\CoreBundle\Library\Resource\Manager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Tool\WorkspaceToolRole;
use Claroline\CoreBundle\Entity\Tool\WorkspaceOrderedTool;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.workspace.creator")
 */
class Creator
{
    private $entityManager;
    private $manager;
    private $roleRepo;
    private $ed;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "rm" = @DI\Inject("claroline.resource.manager"),
     *     "ed" = @DI\Inject("event_dispatcher"),
     *     "translator" = @DI\Inject("translator")
     * })
     */
    public function __construct(EntityManager $em, Manager $rm, $ed, $translator)
    {
        $this->entityManager = $em;
        $this->manager = $rm;
        $this->roleRepo = $this->entityManager->getRepository('ClarolineCoreBundle:Role');
        $this->ed = $ed;
        $this->translator = $translator;
    }

    /**
     * Creates a workspace.
     *
     * @param \Claroline\CoreBundle\Library\Workspace\Configuration $config
     * @param \Claroline\CoreBundle\Entity\User $manager
     *
     * @return AbstractWorkspace
     */
    public function createWorkspace(Configuration $config, User $manager, $autoflush = true)
    {
        $config->check();
        $workspaceType = $config->getWorkspaceType();
        $workspace = new $workspaceType;
        $workspace->setName($config->getWorkspaceName());
        $workspace->setPublic($config->isPublic());
        $workspace->setCode($config->getWorkspaceCode());
        $this->entityManager->persist($workspace);
        $this->entityManager->flush();
        $this->initBaseRoles($workspace, $config);
        $rootDir = $this->manager->createRootDir($workspace, $manager, $config->getPermsRootConfiguration());
        $this->entityManager->flush();
        $extractPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('claro_ws_tmp_', true);
        $archive = new \ZipArchive();
        $archive->open($config->getArchive());
        $archive->extractTo($extractPath);
        $toolsConfig = $config->getToolsConfiguration();

        foreach ($toolsConfig as $name => $conf) {
            $realPaths = array();

            foreach ($conf['files'] as $path) {
                $realPaths[] = $extractPath . DIRECTORY_SEPARATOR . $path;
            }

            $event = new ImportToolEvent($workspace, $conf, $rootDir, $manager);
            $event->setFiles($realPaths);
            $this->ed->dispatch('tool_'.$name.'_from_template', $event);
        }

        $manager->addRole($this->roleRepo->findManagerRole($workspace));
        $this->addMandatoryTools($workspace, $config);
        $this->entityManager->persist($manager);
        if ($autoflush) {
            $this->entityManager->flush();
        }
        $archive->close();

        $log = new LogWorkspaceCreateEvent($workspace);
        $this->ed->dispatch('log', $log);

        return $workspace;
    }

    /**
     * Creates the base roles of a workspace.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param \Claroline\CoreBundle\Library\Workspace\Configuration $config
     */
    private function initBaseRoles(AbstractWorkspace $workspace, Configuration $config)
    {
        $roles = $config->getRoles();

        foreach ($roles as $name => $translation) {
            $this->createRole($name, $workspace, $translation);
        }

        $this->entityManager->flush();
    }

    /**
     * Creates a new role.
     *
     * @param string $baseName
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param string $translationKey
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    private function createRole($baseName, AbstractWorkspace $workspace, $translationKey)
    {
        $baseRole = new Role();
        $baseRole->setName($baseName . '_' . $workspace->getId());
        $baseRole->setParent(null);
        $baseRole->setType(Role::WS_ROLE);
        $baseRole->setTranslationKey($translationKey);
        $baseRole->setWorkspace($workspace);

        $this->entityManager->persist($baseRole);

        return $baseRole;
    }

    /**
     * Adds the tools for a workspace.
     *
     * @todo Optimize this for doctrine (loops with findby aren't exactly really effective).
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param \Claroline\CoreBundle\Library\Workspace\Configuration $config
     */
    private function addMandatoryTools(AbstractWorkspace $workspace, Configuration $config)
    {
        $toolsPermissions = $config->getToolsPermissions();
        $order = 1;

        foreach ($toolsPermissions as $name => $data) {

            $tool = $this->entityManager
                ->getRepository('ClarolineCoreBundle:Tool\Tool')
                ->findOneBy(array('name' => $name));
            if (!$tool->isDisplayableInWorkspace()) {
                throw new \Exception('The tool ' .$name. 'is not displayable in a workspace');
            }
            $wot = new WorkspaceOrderedTool();
            $wot->setWorkspace($workspace);
            $wot->setName(
                $this->translator->trans(
                    $data['name'],
                    array(),
                    'tools'
                )
            );
            $wot->setTool($tool);
            $wot->setOrder($order);
            $this->entityManager->persist($wot);
            //$this->entityManager->flush();
            $order++;

            foreach ($data['perms'] as $role) {
                if ($role === 'ROLE_ANONYMOUS') {
                     $role = $this->entityManager
                        ->getRepository('ClarolineCoreBundle:Role')
                        ->findOneBy(array('name' => $role));
                } else {
                     $role = $this->entityManager
                        ->getRepository('ClarolineCoreBundle:Role')
                        ->findOneBy(array('name' => $role.'_'.$workspace->getId()));
                }

                $tool = $this->entityManager
                    ->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneBy(array('name' => $name));
                    //$wot = $this->entityManager->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')
                    //->findOneBy(array('tool' => $tool, 'workspace' => $workspace));

                $this->setWorkspaceToolRole($wot, $role);
            }
        }

        $this->entityManager->persist($workspace);
    }

    private function setWorkspaceToolRole(WorkspaceOrderedTool $wot, Role $role)
    {
        $wtr = new WorkspaceToolRole();
        $wtr->setRole($role);
        $wtr->setWorkspaceOrderedTool($wot);
        $this->entityManager->persist($wtr);
    }
}