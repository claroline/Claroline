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
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Repository\UserRepository;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceOptions;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class WorkspaceManager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private UserRepository $userRepo;
    private WorkspaceRepository $workspaceRepo;

    public function __construct(
        private readonly string $filesDir,
        private readonly string $defaultWorkspacePath,
        private readonly Crud $crud,
        private readonly ObjectManager $om,
        private readonly TransferManager $transferManager
    ) {
        $this->userRepo = $om->getRepository(User::class);
        $this->workspaceRepo = $om->getRepository(Workspace::class);
    }

    public function export(Workspace $workspace): string
    {
        return $this->transferManager->export($workspace);
    }

    public function import(string $archivePath, ?Workspace $workspace = null): Workspace
    {
        return $this->transferManager->import($archivePath, $workspace ?? new Workspace());
    }

    public function hasAccess(Workspace $workspace, ?TokenInterface $token, ?string $toolName = null, string $permission = 'open'): bool
    {
        return $this->workspaceRepo->checkAccess($workspace, $token?->getRoleNames() ?? [], $toolName, $permission);
    }

    public function registerUsers(array $users, Workspace $workspace, ?Role $role = null, ?array $options = []): void
    {
        if (empty($role)) {
            $role = $workspace->getDefaultRole();
        }

        $this->crud->patch($role, 'user', Crud::COLLECTION_ADD, $users, $options);
    }

    public function unregisterUsers(array $users, Workspace $workspace, ?array $options = []): void
    {
        $roles = $workspace->getRoles()->toArray();
        foreach ($roles as $role) {
            $this->crud->patch($role, 'user', Crud::COLLECTION_REMOVE, $users, $options);
        }
    }

    public function registerGroups(array $groups, Workspace $workspace, ?Role $role = null, ?array $options = []): void
    {
        if (empty($role)) {
            $role = $workspace->getDefaultRole();
        }

        $this->crud->patch($role, 'group', Crud::COLLECTION_ADD, $groups, $options);
    }

    public function unregisterGroups(array $groups, Workspace $workspace, array $options = []): void
    {
        $roles = $workspace->getRoles()->toArray();
        foreach ($roles as $role) {
            $this->crud->patch($role, 'group', Crud::COLLECTION_REMOVE, $groups, $options);
        }
    }

    /**
     * Get the workspace storage directory.
     */
    public function getStorageDirectory(Workspace $workspace): string
    {
        return $this->filesDir.DIRECTORY_SEPARATOR.'WORKSPACE_'.$workspace->getId();
    }

    public function getWorkspaceOptions(Workspace $workspace): WorkspaceOptions
    {
        $workspaceOptions = $this->om->getRepository(WorkspaceOptions::class)->findOneBy(['workspace' => $workspace]);

        // might not be required
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
                'use_workspace_opening_resource' => false,
                'workspace_opening_resource' => null,
            ];
            $workspaceOptions->setDetails($details);
            $workspace->setOptions($workspaceOptions);
            $this->om->persist($workspaceOptions);
            $this->om->persist($workspace);
        }

        return $workspaceOptions;
    }

    public function isRegistered(Workspace $workspace, User $user): bool
    {
        return $this->workspaceRepo->checkAccess($workspace, $user->getRoles());
    }

    /**
     * @deprecated use AuthorizationChecker::isGranted('ADMINISTRATE', $workspace)
     */
    public function isManager(Workspace $workspace, TokenInterface $token): bool
    {
        if (!$token->getUser() instanceof User) {
            return false;
        }

        if (in_array('ROLE_ADMIN', $token->getRoleNames())) {
            // this should be checked at a higher level
            return true;
        }

        // or we have the role_manager
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

            // if we're amongst the administrators of the organizations
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

    public function isImpersonated(?TokenInterface $token): bool
    {
        if (!$token) {
            return false;
        }

        return in_array('ROLE_USURPATE_WORKSPACE_ROLE', $token->getRoleNames());
    }

    public function getTokenRoles(?TokenInterface $token, Workspace $workspace): array
    {
        if (!$token) {
            return [];
        }

        return array_values(array_filter($workspace->getRoles()->toArray(), function (Role $role) use ($token) {
            return in_array($role->getName(), $token->getRoleNames());
        }));
    }

    public function getDefaultModel(bool $restore = false): Workspace
    {
        $name = 'default_workspace';
        $this->logger->debug('Search default workspace '.$name);
        $workspace = $this->workspaceRepo->findOneBy(['code' => $name]);

        if (!$workspace || $restore) {
            $this->logger->debug('Rebuilding...');
            if ($workspace && $restore) {
                $this->logger->debug('Removing workspace...');
                $this->om->remove($workspace);
                $this->om->flush();
            }

            $this->logger->debug(sprintf('Import from archive "%s"...', $this->defaultWorkspacePath));

            $workspace = new Workspace();
            $workspace->setName($name);
            $workspace->setCode($name);

            $workspace = $this->import($this->defaultWorkspacePath, $workspace);

            $workspace->setModel(true);

            $this->om->persist($workspace);
            $this->om->flush();
        }

        return $workspace;
    }
}
