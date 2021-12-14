<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager\Workspace;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Shortcuts;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceOptions;
use Claroline\CoreBundle\Repository\User\UserRepository;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class WorkspaceManager implements LoggerAwareInterface
{
    use LoggableTrait;

    /** @var string */
    private $filesDir;
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;
    private $container;

    private $shortcutsRepo;
    /** @var UserRepository */
    private $userRepo;
    /** @var WorkspaceRepository */
    private $workspaceRepo;

    public function __construct(
        string $fileDir,
        Crud $crud,
        ObjectManager $om,
        ContainerInterface $container
    ) {
        $this->filesDir = $fileDir;
        $this->om = $om;
        $this->container = $container;
        $this->crud = $crud;

        $this->userRepo = $om->getRepository(User::class);
        $this->workspaceRepo = $om->getRepository(Workspace::class);
        $this->shortcutsRepo = $om->getRepository(Shortcuts::class);
    }

    public function createPersonalWorkspace(User $user, ?Workspace $model = null): Workspace
    {
        if (empty($model)) {
            $model = $this->getDefaultModel(true);
        }

        /** @var Workspace $workspace */
        $workspace = $this->crud->create(Workspace::class, [
            'name' => $user->getUsername(),
            'code' => $user->getUsername(),
            'model' => [
                'id' => $model->getUuid(),
            ],
            'meta' => [
                'personal' => true,
                // Set the target user as creator (this no longer work has the creator is overridden in WorkspaceCrud):
                // - user will automatically gets the MANAGER role on workspace creation
                // - managers don't get registered to all the personal workspace they create
                'creator' => ['id' => $user->getUuid()],
            ],
        ], [Crud::NO_PERMISSIONS]);

        $user->setPersonalWorkspace($workspace);

        // register target user as manager
        if ($workspace->getManagerRole()) {
            $this->crud->patch($user, 'role', 'add', [$workspace->getManagerRole()], [Crud::NO_PERMISSIONS]);
        }

        $this->om->persist($user);
        $this->om->flush();

        return $workspace;
    }

    public function export(Workspace $workspace)
    {
        return $this->container->get(TransferManager::class)->export($workspace);
    }

    public function import(string $archivePath)
    {
        return $this->container->get(TransferManager::class)->import($archivePath);
    }

    public function hasAccess(Workspace $workspace, TokenInterface $token, string $toolName = null, string $permission = 'open'): bool
    {
        $roles = $token->getRoleNames();
        if (!empty($roles)) {
            return $this->workspaceRepo->checkAccess($workspace, $roles, $toolName, $permission);
        }

        return false;
    }

    public function addUser(Workspace $workspace, User $user): User
    {
        if ($workspace->getDefaultRole() && !$user->hasRole($workspace->getDefaultRole()->getName())) {
            $this->crud->patch($user, 'role', Crud::COLLECTION_ADD, [$workspace->getDefaultRole()]);
        }

        return $user;
    }

    /**
     * Get the workspace storage directory.
     */
    public function getStorageDirectory(Workspace $workspace): string
    {
        return $this->filesDir.DIRECTORY_SEPARATOR.'WORKSPACE_'.$workspace->getId();
    }

    /**
     * Get the current used storage in a workspace.
     */
    public function getUsedStorage(Workspace $workspace): int
    {
        $dir = $this->getStorageDirectory($workspace);
        $size = 0;

        if (!is_dir($dir)) {
            return $size;
        }

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $file) {
            $size += $file->getSize();
        }

        return $size;
    }

    public function getWorkspaceOptions(Workspace $workspace): WorkspaceOptions
    {
        $workspaceOptions = $this->om->getRepository(WorkspaceOptions::class)->findOneBy(['workspace' => $workspace]);

        //might not be required
        if (!$workspaceOptions) {
            $scheduledForInsert = $this->om->getUnitOfWork()->getScheduledEntityInsertions();

            foreach ($scheduledForInsert as $entity) {
                if (WorkspaceOptions::class === get_class($entity)) {
                    if ($entity->getWorkspace() && $entity->getWorkspace()->getCode() === $workspace->getCode()) {
                        $workspaceOptions = $entity;
                    }
                }
            }
        }

        if (!$workspaceOptions) {
            $workspaceOptions = new WorkspaceOptions();
            $workspaceOptions->setWorkspace($workspace);
            $details = [
                'hide_tools_menu' => null,
                'background_color' => null,
                'hide_breadcrumb' => false,
                'use_workspace_opening_resource' => false,
                'workspace_opening_resource' => null,
            ];
            $workspaceOptions->setDetails($details);
            $workspace->setOptions($workspaceOptions);
            $this->om->persist($workspaceOptions);
            $this->om->persist($workspace);
            $this->om->flush();
        }

        return $workspaceOptions;
    }

    public function isRegistered(Workspace $workspace, User $user): bool
    {
        return $this->workspaceRepo->checkAccess($workspace, $user->getRoles());
    }

    public function isManager(Workspace $workspace, TokenInterface $token): bool
    {
        if (!$token->getUser() instanceof User) {
            return false;
        }

        if (in_array('ROLE_ADMIN', $token->getRoleNames())) {
            // this should be checked at a higher level
            return true;
        }

        //or we have the role_manager
        $managerRole = $workspace->getManagerRole();
        if ($managerRole && in_array($managerRole->getName(), $token->getRoleNames())) {
            return true;
        }

        if (!$this->isImpersonated($token)) {
            /** @var User $user */
            $user = $token->getUser();

            // we are the creator of the workspace
            // this is useless because we give the manager role to the creator (checked earlier)
            if ($workspace->getCreator() === $user) {
                return true;
            }

            //if we're amongst the administrators of the organizations
            $adminOrganizations = $user->getAdministratedOrganizations();
            $workspaceOrganizations = $workspace->getOrganizations();

            foreach ($adminOrganizations as $adminOrganization) {
                foreach ($workspaceOrganizations as $workspaceOrganization) {
                    if ($workspaceOrganization === $adminOrganization) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function isImpersonated(TokenInterface $token): bool
    {
        return in_array('ROLE_USURPATE_WORKSPACE_ROLE', $token->getRoleNames());
    }

    public function getTokenRoles(TokenInterface $token, Workspace $workspace): array
    {
        return array_values(array_filter($workspace->getRoles()->toArray(), function (Role $role) use ($token) {
            return in_array($role->getName(), $token->getRoleNames());
        }));
    }

    public function archive(Workspace $workspace): Workspace
    {
        //rename with [archive] and ids
        $workspace->setName('[archive]'.$workspace->getName());
        $workspace->setCode('[archive]'.$workspace->getCode().uniqid());
        $workspace->setArchived(true);

        $this->om->persist($workspace);
        $this->om->flush();

        return $workspace;
    }

    public function unarchive(Workspace $workspace): Workspace
    {
        $workspace->setArchived(false);

        $this->om->persist($workspace);
        $this->om->flush();

        return $workspace;
    }

    public function getDefaultModel($isPersonal = false, $restore = false): Workspace
    {
        $name = $isPersonal ? 'default_personal' : 'default_workspace';
        $this->log('Search old default workspace '.$name);
        $workspace = $this->workspaceRepo->findOneBy(['code' => $name]);

        if (!$workspace || $restore) {
            $this->log('Rebuilding...');
            if ($workspace && $restore) {
                $this->log('Removing workspace...');
                $this->om->remove($workspace);
                $this->om->flush();
            }

            $this->log(sprintf('Import from archive "%s"...', $this->container->getParameter('claroline.param.workspace.default')));

            $workspace = new Workspace();
            $workspace->setName($name);
            $workspace->setCode($name);

            /** @var Workspace $workspace */
            $workspace = $this->container->get(TransferManager::class)->import(
                $this->container->getParameter('claroline.param.workspace.default'),
                $workspace
            );

            //just in case
            $workspace->setPersonal($isPersonal);
            $workspace->setModel(true);

            if (0 === count($this->shortcutsRepo->findBy(['workspace' => $workspace]))) {
                $this->log('Generating default shortcuts...');
                $managerRole = $workspace->getManagerRole();
                $collaboratorRole = $workspace->getCollaboratorRole();

                if ($managerRole) {
                    $shortcuts = new Shortcuts();
                    $shortcuts->setWorkspace($workspace);
                    $shortcuts->setRole($managerRole);
                    $shortcuts->setData([
                        ['type' => 'tool', 'name' => 'home'],
                        ['type' => 'tool', 'name' => 'resources'],
                        ['type' => 'tool', 'name' => 'agenda'],
                        ['type' => 'tool', 'name' => 'community'],
                        ['type' => 'tool', 'name' => 'dashboard'],
                        ['type' => 'action', 'name' => 'favourite'],
                        ['type' => 'action', 'name' => 'configure'],
                        ['type' => 'action', 'name' => 'impersonation'],
                    ]);
                    $this->om->persist($shortcuts);
                }
                if ($collaboratorRole) {
                    $shortcuts = new Shortcuts();
                    $shortcuts->setWorkspace($workspace);
                    $shortcuts->setRole($collaboratorRole);
                    $shortcuts->setData([
                        ['type' => 'tool', 'name' => 'home'],
                        ['type' => 'tool', 'name' => 'resources'],
                        ['type' => 'tool', 'name' => 'agenda'],
                        ['type' => 'action', 'name' => 'favourite'],
                    ]);
                    $this->om->persist($shortcuts);
                }
            }

            $this->om->persist($workspace);
            $this->om->flush();
        }

        return $workspace;
    }

    public function unregister(AbstractRoleSubject $subject, Workspace $workspace, array $options = [])
    {
        $rolesToRemove = array_filter($workspace->getRoles()->toArray(), function (Role $role) use ($workspace) {
            return $role->getWorkspace()->getId() === $workspace->getId();
        });

        foreach ($rolesToRemove as $role) {
            if ($subject->hasRole($role->getName())) {
                $this->crud->patch($subject, 'role', Crud::COLLECTION_REMOVE, [$role], $options);
            }
        }
    }

    public function countUsersForRoles(Workspace $workspace)
    {
        $roles = $workspace->getRoles();

        $usersInRoles = [];
        foreach ($roles as $role) {
            $usersInRoles[] = [
                'name' => $role->getTranslationKey(),
                'total' => floatval($this->userRepo->countUsersByRole($role)),
            ];
        }

        return $usersInRoles;
    }

    public function getShortcuts(Workspace $workspace, array $roleNames = []): array
    {
        $shortcuts = [];
        foreach ($workspace->getShortcuts() as $shortcut) {
            if (in_array($shortcut->getRole()->getName(), $roleNames)) {
                $shortcuts = array_merge($shortcuts, $shortcut->getData());
            }
        }

        return $shortcuts;
    }

    public function addShortcuts(Workspace $workspace, Role $role, array $toAdd)
    {
        $workspaceShortcuts = $this->shortcutsRepo->findOneBy(['workspace' => $workspace, 'role' => $role]);

        if (!$workspaceShortcuts) {
            $workspaceShortcuts = new Shortcuts();
            $workspaceShortcuts->setWorkspace($workspace);
            $workspaceShortcuts->setRole($role);
        }
        $data = $workspaceShortcuts->getData();

        foreach ($toAdd as $shortcut) {
            if (Shortcuts::SHORTCUTS_LIMIT > count($data)) {
                $filteredArray = array_filter($data, function ($element) use ($shortcut) {
                    return $element['type'] === $shortcut['type'] && $element['name'] === $shortcut['name'];
                });

                if (0 === count($filteredArray)) {
                    $data[] = $shortcut;
                }
            }
        }
        $workspaceShortcuts->setData($data);
        $this->om->persist($workspaceShortcuts);
        $this->om->flush();
    }

    public function removeShortcut(Workspace $workspace, Role $role, $type, $name)
    {
        $workspaceShortcuts = $this->shortcutsRepo->findOneBy(['workspace' => $workspace, 'role' => $role]);

        if ($workspaceShortcuts) {
            $data = $workspaceShortcuts->getData();
            $newData = [];

            foreach ($data as $shortcut) {
                if ($shortcut['type'] !== $type || $shortcut['name'] !== $name) {
                    $newData[] = $shortcut;
                }
            }
            $workspaceShortcuts->setData($newData);
            $this->om->persist($workspaceShortcuts);
            $this->om->flush();
        }
    }

    /**
     * Generates an unique workspace code from given one by iterating it.
     */
    public function getUniqueCode(string $code): string
    {
        $existingCodes = $this->workspaceRepo->findWorkspaceCodesWithPrefix($code);
        if (empty($existingCodes)) {
            return $code;
        }

        do {
            $index = count($existingCodes) + 1;
            $currentCode = $code.'_'.$index;
            $upperCurrentCode = strtoupper($currentCode);
        } while (in_array($upperCurrentCode, $existingCodes));

        return $currentCode;
    }
}
