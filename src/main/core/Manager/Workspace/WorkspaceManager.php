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
use Claroline\CommunityBundle\Repository\UserRepository;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceOptions;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class WorkspaceManager implements LoggerAwareInterface
{
    use LoggableTrait;

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

    public function createPersonalWorkspace(User $user, Workspace $model = null): Workspace
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

    public function export(Workspace $workspace): string
    {
        return $this->transferManager->export($workspace);
    }

    public function import(string $archivePath, Workspace $workspace = null): Workspace
    {
        return $this->transferManager->import($archivePath, $workspace ?? new Workspace());
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
        //$workspace->setName('[archive]'.$workspace->getName());
        //$workspace->setCode('[archive]'.$workspace->getCode().uniqid());
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
        $this->log('Search default workspace '.$name);
        $workspace = $this->workspaceRepo->findOneBy(['code' => $name]);

        if (!$workspace || $restore) {
            $this->log('Rebuilding...');
            if ($workspace && $restore) {
                $this->log('Removing workspace...');
                $this->om->remove($workspace);
                $this->om->flush();
            }

            $this->log(sprintf('Import from archive "%s"...', $this->defaultWorkspacePath));

            $workspace = new Workspace();
            $workspace->setName($name);
            $workspace->setCode($name);

            $workspace = $this->import($this->defaultWorkspacePath, $workspace);

            // just in case
            $workspace->setPersonal($isPersonal);
            $workspace->setModel(true);

            $this->om->persist($workspace);
            $this->om->flush();
        }

        return $workspace;
    }

    public function unregister(AbstractRoleSubject $subject, Workspace $workspace, array $options = []): void
    {
        $this->crud->patch($subject, 'role', Crud::COLLECTION_REMOVE, $workspace->getRoles()->toArray(), $options);
    }

    /**
     * Generates a unique workspace code from given one by iterating it.
     */
    public function getUniqueCode(string $code): string
    {
        $existingCodes = $this->workspaceRepo->findCodesWithPrefix($code);
        if (empty($existingCodes)) {
            return $code;
        }

        $index = count($existingCodes);
        do {
            ++$index;
            $currentCode = $code.'_'.$index;
            $lowerCurrentCode = strtolower($currentCode);
        } while (in_array($lowerCurrentCode, $existingCodes));

        return $currentCode;
    }
}
