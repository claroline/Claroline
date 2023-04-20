<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Manager;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Entity\Team;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\Tool\ToolRightsManager;

class TeamManager
{
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;
    /** @var ResourceManager */
    private $resourceManager;
    /** @var RightsManager */
    private $rightsManager;
    /** @var RoleManager */
    private $roleManager;
    /** @var ToolRightsManager */
    private $toolRightsManager;

    private $resourceNodeRepo;
    private $teamRepo;

    public function __construct(
        ObjectManager $om,
        Crud $crud,
        ResourceManager $resourceManager,
        RightsManager $rightsManager,
        RoleManager $roleManager,
        ToolRightsManager $toolRightsManager
    ) {
        $this->om = $om;
        $this->crud = $crud;
        $this->resourceManager = $resourceManager;
        $this->rightsManager = $rightsManager;
        $this->roleManager = $roleManager;
        $this->toolRightsManager = $toolRightsManager;

        $this->resourceNodeRepo = $om->getRepository(ResourceNode::class);
        $this->teamRepo = $om->getRepository(Team::class);
    }

    /**
     * Creates role for team members.
     */
    public function createTeamRole(Team $team, ?bool $isManager = false): Role
    {
        $workspace = $team->getWorkspace();
        $teamName = $team->getName();
        $roleName = $this->computeValidRoleName(
            strtoupper(str_replace(' ', '_', $isManager ? $teamName.'_MANAGER' : $teamName)),
            $workspace->getUuid()
        );
        $roleKey = $this->computeValidRoleTranslationKey($workspace, $isManager ? $teamName.' manager' : $teamName);
        $role = $this->roleManager->createWorkspaceRole($roleName, $roleKey, $workspace);

        $root = $this->resourceManager->getWorkspaceRoot($workspace);
        $this->rightsManager->update(['open' => true], $role, $root);
        $tool = $this->om->getRepository(Tool::class)->findOneBy(['name' => 'resources']);
        $orderedTool = $this->om
            ->getRepository(OrderedTool::class)
            ->findOneBy(['workspace' => $workspace, 'tool' => $tool]);

        if (!empty($orderedTool)) {
            $this->toolRightsManager->setToolRights($orderedTool, $role, 1);
        }
        $this->setRightsForOldTeams($workspace, $role);

        return $role;
    }

    public function deleteTeamRoles(Team $team): void
    {
        if (!empty($team->getRole())) {
            $this->om->remove($team->getRole());
        }

        if (!empty($team->getManagerRole())) {
            $this->om->remove($team->getManagerRole());
        }

        $this->om->flush();
    }

    /**
     * Creates team directory.
     */
    public function createTeamDirectory(Team $team, User $user, ?ResourceNode $resource = null, ?array $creatableResources = []): Directory
    {
        $workspace = $team->getWorkspace();

        $teamRole = $team->getRole();
        $teamManagerRole = $team->getManagerRole();
        $wsManagerRole = $workspace->getManagerRole();

        $teamRoleName = $teamRole->getName();
        $teamManagerRoleName = $teamManagerRole->getName();

        $rights = [];
        $rights[$teamRoleName] = [];
        $rights[$teamRoleName]['role'] = $teamRole;
        $rights[$teamRoleName]['create'] = [];

        $rights[$teamManagerRoleName] = [];
        $rights[$teamManagerRoleName]['role'] = $teamManagerRole;
        $rights[$teamManagerRoleName]['create'] = [];

        if ($wsManagerRole) {
            $rights[$wsManagerRole->getName()] = [];
            $rights[$wsManagerRole->getName()]['role'] = $wsManagerRole;
            $rights[$wsManagerRole->getName()]['create'] = [];
        }

        $resourceTypes = $this->resourceManager->getAllResourceTypes();
        foreach ($resourceTypes as $resourceType) {
            $rights[$teamManagerRoleName]['create'][] = ['name' => $resourceType->getName()];

            // because we don't copy the root rights, we need to correctly initialize the workspace manager rights
            if ($wsManagerRole) {
                $rights[$wsManagerRole->getName()]['create'][] = ['name' => $resourceType->getName()];
            }
        }

        foreach ($creatableResources as $creatableResource) {
            $rights[$teamRoleName]['create'][] = ['name' => $creatableResource];
        }

        $directoryType = $this->resourceManager->getResourceTypeByName('directory');
        $decoders = $directoryType->getMaskDecoders();
        foreach ($decoders as $decoder) {
            $decoderName = $decoder->getName();

            if ('create' !== $decoderName) {
                $rights[$teamManagerRoleName][$decoderName] = true;

                if ('administrate' !== $decoderName && 'delete' !== $decoderName) {
                    $rights[$teamRoleName][$decoderName] = true;
                }

                // because we don't copy the root rights, we need to correctly initialize the workspace manager rights
                if ($wsManagerRole) {
                    $rights[$wsManagerRole->getName()][$decoderName] = true;
                }
            }
        }

        // TODO : use crud
        $rootDirectory = $this->resourceManager->getWorkspaceRoot($workspace);

        $directory = new Directory();
        $directory->setName($team->getName());

        $this->resourceManager->create(
            $directory,
            $directoryType,
            $user,
            $workspace,
            $rootDirectory,
            $rights
        );

        // ATTENTION : because rights are pushed into DB in plain SQL we need to reload the entity to get the correct data
        $this->om->refresh($directory->getResourceNode());

        if (!is_null($resource)) {
            $this->crud->copy($resource, [Options::NO_RIGHTS, Crud::NO_PERMISSIONS], ['user' => $user, 'parent' => $directory->getResourceNode()]);
        }

        return $directory;
    }

    public function deleteTeamDirectory(Team $team): void
    {
        if ($team->isDirDeletable() && !empty($team->getDirectory())) {
            $this->crud->delete($team->getDirectory());
        }
    }

    /**
     * Gets user teams in a workspace.
     *
     * @return array
     */
    public function getTeamsByUserAndWorkspace(User $user, Workspace $workspace)
    {
        return $this->teamRepo->findTeamsByUserAndWorkspace($user, $workspace);
    }

    /**
     * Sets rights to team directory for all workspace roles.
     */
    public function initializeTeamRights(Team $team): void
    {
        $workspace = $team->getWorkspace();
        $teamRole = $team->getRole();
        $teamManagerRole = $team->getManagerRole();

        if (!empty($team->getDirectory()) && $team->isPublic()) {
            $workspaceRoles = $this->roleManager->getWorkspaceRoles($workspace);
            $rights = [];

            foreach ($workspaceRoles as $role) {
                if (!in_array($role->getUuid(), [$teamRole->getUuid(), $teamManagerRole->getUuid()])) {
                    $rights[$role->getName()] = [
                        'role' => $role,
                        'open' => $team->isPublic(),
                    ];
                }
            }

            $this->applyRightsToResourceNode($team->getDirectory(), $rights);
        }
    }

    /**
     * Updates permissions of team directory.
     */
    public function updateTeamDirectoryPerms(Team $team): void
    {
        if (!is_null($team->getDirectory())) {
            $this->om->startFlushSuite();

            $workspace = $team->getWorkspace();
            $teamRole = $team->getRole();
            $teamManagerRole = $team->getManagerRole();
            $workspaceRoles = $this->roleManager->getWorkspaceRoles($workspace);

            foreach ($workspaceRoles as $role) {
                if (!in_array($role->getUuid(), [$teamRole->getUuid(), $teamManagerRole->getUuid()])) {
                    $rights = ['open' => $team->isPublic()];
                    $this->rightsManager->update($rights, $role, $team->getDirectory(), true);
                }
            }
            $this->om->endFlushSuite();
        }
    }

    /**
     * Registers users to a team.
     */
    public function registerUsersToTeam(Team $team, array $users): void
    {
        $teamRole = $team->getRole();

        if (!is_null($teamRole)) {
            $this->om->startFlushSuite();

            foreach ($users as $user) {
                $team->addUser($user);
                if (!$user->hasRole($teamRole->getName())) {
                    $this->crud->patch($user, 'role', Crud::COLLECTION_ADD, [$teamRole], [Crud::NO_PERMISSIONS]);
                }
            }
            $this->om->persist($team);
            $this->om->endFlushSuite();
        }
    }

    /**
     * Unregisters users from a team.
     */
    public function unregisterUsersFromTeam(Team $team, array $users): void
    {
        $teamRole = $team->getRole();

        if (!is_null($teamRole)) {
            $this->om->startFlushSuite();

            foreach ($users as $user) {
                $team->removeUser($user);
                if ($user->hasRole($teamRole->getName())) {
                    $this->crud->patch($user, 'role', Crud::COLLECTION_REMOVE, [$teamRole], [Crud::NO_PERMISSIONS]);
                }
            }
            $this->om->persist($team);
            $this->om->endFlushSuite();
        }
    }

    /**
     * Registers users as team managers.
     */
    public function registerManagersToTeam(Team $team, array $users): void
    {
        $teamManagerRole = $team->getManagerRole();

        if (!is_null($teamManagerRole) && 0 < count($users)) {
            $this->om->startFlushSuite();

            foreach ($users as $user) {
                $this->crud->patch($user, 'role', Crud::COLLECTION_ADD, [$teamManagerRole], [Crud::NO_PERMISSIONS]);
            }
            $this->om->persist($team);
            $this->om->endFlushSuite();
        }
    }

    /**
     * Unregisters team managers.
     */
    public function unregisterManagersFromTeam(Team $team, array $users): void
    {
        $teamManagerRole = $team->getManagerRole();

        if (!is_null($teamManagerRole)) {
            $this->om->startFlushSuite();

            foreach ($users as $user) {
                $this->crud->patch($user, 'role', Crud::COLLECTION_REMOVE, [$teamManagerRole], [Crud::NO_PERMISSIONS]);
            }
            $this->om->persist($team);
            $this->om->endFlushSuite();
        }
    }

    /**
     * Empty team from all members.
     */
    public function emptyTeam(Team $team): void
    {
        $users = $team->getRole()->getUsers()->toArray();
        $this->unregisterUsersFromTeam($team, $users);
    }

    /**
     * Fills team with workspace users who belong to no team.
     */
    public function fillTeam(Team $team): void
    {
        $this->om->startFlushSuite();
        $workspaceTeams = $this->teamRepo->findBy(['workspace' => $team->getWorkspace()]);
        $users = $this->teamRepo->findUsersWithNoTeamByWorkspace($team->getWorkspace(), $workspaceTeams);

        $maxUsers = $team->getMaxUsers();

        if (is_null($maxUsers)) {
            $this->registerUsersToTeam($team, $users);
        } else {
            $nbFreeSpaces = $maxUsers - count($team->getUsers()->toArray());

            $this->om->startFlushSuite();
            while ($nbFreeSpaces > 0 && count($users) > 0) {
                $index = rand(0, count($users) - 1);
                $this->registerUsersToTeam($team, [$users[$index]]);
                unset($users[$index]);
                $users = array_values($users);
                --$nbFreeSpaces;
            }
            $this->om->endFlushSuite();
        }
    }

    /**
     * Checks and updates role name for unicity.
     */
    private function computeValidRoleName(string $roleName, string $uuid): string
    {
        $i = 1;
        $name = 'ROLE_WS_'.$roleName.'_'.$uuid;
        $role = $this->roleManager
            ->getRoleByName($name);

        while (!is_null($role)) {
            $name = 'ROLE_WS_'.$roleName.'_'.$i.'_'.$uuid;
            $role = $this->roleManager->getRoleByName($name);
            ++$i;
        }

        return $name;
    }

    /**
     * Checks and updates role translation key for unicity.
     */
    private function computeValidRoleTranslationKey(Workspace $workspace, string $key): string
    {
        $i = 1;
        $translationKey = $key;
        $role = $this->roleManager->getRoleByTranslationKeyAndWorkspace($translationKey, $workspace);

        while (!is_null($role)) {
            $translationKey = $key.' ('.$i.')';
            $role = $this->roleManager->getRoleByTranslationKeyAndWorkspace($translationKey, $workspace);
            ++$i;
        }

        return $translationKey;
    }

    /**
     * Grants access to all directories of existing teams which are set as public for role.
     */
    private function setRightsForOldTeams(Workspace $workspace, Role $role): void
    {
        $this->om->startFlushSuite();
        $teams = $this->teamRepo->findBy(['workspace' => $workspace]);

        foreach ($teams as $team) {
            if ($team->isPublic() && !is_null($team->getDirectory())) {
                $rights = [];
                $rights['open'] = true;
                $this->rightsManager->update(
                    $rights,
                    $role,
                    $team->getDirectory(),
                    true
                );
            }
        }
        $this->om->endFlushSuite();
    }

    private function applyRightsToResourceNode(ResourceNode $node, array $rights): void
    {
        $this->om->startFlushSuite();
        $this->resourceManager->createRights($node, $rights, false);

        if ('directory' === $node->getResourceType()->getName()) {
            foreach ($node->getChildren() as $child) {
                $this->applyRightsToResourceNode($child, $rights);
            }
        }
        $this->om->endFlushSuite();
    }
}
