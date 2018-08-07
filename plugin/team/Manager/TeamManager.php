<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TeamBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolRightsManager;
use Claroline\TeamBundle\Entity\Team;
use Claroline\TeamBundle\Entity\WorkspaceTeamParameters;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.team_manager")
 */
class TeamManager
{
    private $om;
    private $resourceManager;
    private $rightsManager;
    private $roleManager;
    private $toolRightsManager;

    private $resourceNodeRepo;
    private $teamRepo;
    private $workspaceTeamParamsRepo;

    /**
     * @DI\InjectParams({
     *     "om"                = @DI\Inject("claroline.persistence.object_manager"),
     *     "resourceManager"   = @DI\Inject("claroline.manager.resource_manager"),
     *     "rightsManager"     = @DI\Inject("claroline.manager.rights_manager"),
     *     "toolRightsManager" = @DI\Inject("claroline.manager.tool_rights_manager"),
     *     "roleManager"       = @DI\Inject("claroline.manager.role_manager")
     * })
     */
    public function __construct(
        ObjectManager $om,
        ResourceManager $resourceManager,
        RightsManager $rightsManager,
        RoleManager $roleManager,
        ToolRightsManager $toolRightsManager
    ) {
        $this->om = $om;
        $this->resourceManager = $resourceManager;
        $this->rightsManager = $rightsManager;
        $this->roleManager = $roleManager;
        $this->toolRightsManager = $toolRightsManager;

        $this->resourceNodeRepo = $om->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $this->teamRepo = $om->getRepository('ClarolineTeamBundle:Team');
        $this->workspaceTeamParamsRepo = $om->getRepository('ClarolineTeamBundle:WorkspaceTeamParameters');
    }

    /**
     * Gets team parameters for a workspace.
     *
     * @param Workspace $workspace
     *
     * @return WorkspaceTeamParameters
     */
    public function getWorkspaceTeamParameters(Workspace $workspace)
    {
        $teamParams = $this->workspaceTeamParamsRepo->findOneBy(['workspace' => $workspace]);

        if (empty($teamParams)) {
            $teamParams = new WorkspaceTeamParameters();
            $teamParams->setWorkspace($workspace);
        }

        return $teamParams;
    }

    /**
     * Creates several teams from data.
     *
     * @param Workspace $workspace
     * @param User      $user
     * @param array     $data
     */
    public function createMultipleTeams(Workspace $workspace, User $user, array $data)
    {
        $this->om->startFlushSuite();
        $teams = [];
        $nodes = [];
        $index = 1;
        $nbTeams = isset($data['nbTeams']) ? $data['nbTeams'] : 0;
        $name = isset($data['name']) ? $data['name'] : '';
        $description = isset($data['description']) ? $data['description'] : null;
        $selfRegistration = isset($data['selfRegistration']) ? $data['selfRegistration'] : false;
        $selfUnregistration = isset($data['selfUnregistration']) ? $data['selfUnregistration'] : false;
        $publicDirectory = isset($data['publicDirectory']) ? $data['publicDirectory'] : false;
        $deletableDirectory = isset($data['deletableDirectory']) ? $data['deletableDirectory'] : false;
        $maxUsers = isset($data['maxUsers']) ? $data['maxUsers'] : null;

        for ($i = 0; $i < $nbTeams; ++$i) {
            $team = new Team();
            $validName = $this->computeValidTeamName($workspace, $name, $index);
            $team->setName($validName['name']);
            $index = $validName['index'] + 1;
            $team->setWorkspace($workspace);
            $team->setDescription($description);
            $team->setMaxUsers($maxUsers);
            $team->setSelfRegistration($selfRegistration);
            $team->setSelfUnregistration($selfUnregistration);
            $team->setIsPublic($publicDirectory);
            $team->setDirDeletable($deletableDirectory);

            // Creates team role
            $teamRole = $this->createTeamRole($team);
            $teamRole->setMaxUsers($maxUsers);
            $team->setRole($teamRole);
            $this->om->persist($teamRole);

            // Creates team manager role
            $teamManagerRole = $this->createTeamRole($team, true);
            $team->setTeamManagerRole($teamManagerRole);

            // Creates team directory
            $defaultResource = isset($data['defaultResource']['id']) ?
                $this->resourceNodeRepo->findOneBy(['uuid' => $data['defaultResource']['id']]) :
                null;
            $creatableResources = isset($data['creatableResources']) ?
                $data['creatableResources'] :
                [];
            $directory = $this->createTeamDirectory(
                $team,
                $user,
                $defaultResource,
                $creatableResources
            );
            $team->setDirectory($directory);
            $this->om->persist($team);

            $node = $team->getDirectory()->getResourceNode();
            $node->setIndex(1);
            $this->om->persist($node);
            $teams[] = $team;
            $nodes[] = $node;
        }
        $this->linkResourceNodesArray($workspace, $nodes);
        $this->om->forceFlush();

        foreach ($teams as $team) {
            $this->initializeTeamRights($team);
        }
        $this->om->endFlushSuite();
    }

    /**
     * Deletes multiple teams.
     *
     * @param array $teams
     */
    public function deleteTeams(array $teams)
    {
        $this->om->startFlushSuite();

        foreach ($teams as $team) {
            $this->deleteTeam($team);
        }

        $this->om->endFlushSuite();
    }

    /**
     * Deletes a team.
     *
     * @param Team $team
     */
    public function deleteTeam(Team $team)
    {
        $teamRole = $team->getRole();
        $teamManagerRole = $team->getTeamManagerRole();

        if (!is_null($teamManagerRole)) {
            $this->om->remove($teamManagerRole);
        }
        if (!is_null($teamRole)) {
            $this->om->remove($teamRole);
        }
        $teamDirectory = $team->getDirectory();

        if ($team->isDirDeletable() && !is_null($teamDirectory)) {
            $this->resourceManager->delete($teamDirectory->getResourceNode());
        }
        $this->om->remove($team);
        $this->om->flush();
    }

    /**
     * Checks if name already exists and returns a incremented version if it does.
     *
     * @param Workspace $workspace
     * @param string    $teamName
     * @param int       $index
     *
     * @return array
     */
    public function computeValidTeamName(Workspace $workspace, $teamName, $index = 0)
    {
        $name = 0 === $index ? $teamName : $teamName.' '.$index;

        $teams = $this->teamRepo->findBy(['workspace' => $workspace, 'name' => $name]);

        while (count($teams) > 0) {
            ++$index;
            $name = $teamName.' '.$index;
            $teams = $this->teamRepo->findBy(['workspace' => $workspace, 'name' => $name]);
        }

        return ['name' => $name, 'index' => $index];
    }

    /**
     * Creates role for team members.
     *
     * @param Team $team
     * @param bool $isManager
     *
     * @return Role
     */
    public function createTeamRole(Team $team, $isManager = false)
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
        $this->rightsManager->editPerms(['open' => true], $role, $root);
        $orderedTool = $this->om
            ->getRepository('ClarolineCoreBundle:Tool\OrderedTool')
            ->findOneBy(['workspace' => $workspace, 'name' => 'resource_manager']);

        if (!empty($orderedTool)) {
            $this->toolRightsManager->setToolRights($orderedTool, $role, 1);
        }
        $this->setRightsForOldTeams($workspace, $role);

        return $role;
    }

    /**
     * Creates team directory.
     *
     * @param Team         $team
     * @param User         $user
     * @param ResourceNode $resource
     * @param array        $creatableResources
     *
     * @return Directory
     */
    public function createTeamDirectory(Team $team, User $user, ResourceNode $resource = null, array $creatableResources = [])
    {
        $workspace = $team->getWorkspace();
        $teamRole = $team->getRole();
        $teamManagerRole = $team->getTeamManagerRole();
        $rootDirectory = $this->resourceManager->getWorkspaceRoot($workspace);
        $directoryType = $this->resourceManager->getResourceTypeByName('directory');
        $resourceTypes = $this->resourceManager->getAllResourceTypes();

        $directory = $this->resourceManager->createResource(
            'Claroline\CoreBundle\Entity\Resource\Directory',
            $team->getName()
        );
        $teamRoleName = $teamRole->getName();
        $teamManagerRoleName = $teamManagerRole->getName();
        $rights = [];
        $rights[$teamRoleName] = [];
        $rights[$teamRoleName]['role'] = $teamRole;
        $rights[$teamRoleName]['create'] = [];
        $rights[$teamManagerRoleName] = [];
        $rights[$teamManagerRoleName]['role'] = $teamManagerRole;
        $rights[$teamManagerRoleName]['create'] = [];

        foreach ($resourceTypes as $resourceType) {
            $rights[$teamManagerRoleName]['create'][] = ['name' => $resourceType->getName()];
        }

        foreach ($creatableResources as $creatableResource) {
            $rights[$teamRoleName]['create'][] = ['name' => $creatableResource];
        }
        $decoders = $directoryType->getMaskDecoders();

        foreach ($decoders as $decoder) {
            $decoderName = $decoder->getName();

            if ('create' !== $decoderName) {
                $rights[$teamManagerRoleName][$decoderName] = true;
            }
            if ('administrate' !== $decoderName && 'delete' !== $decoderName && 'create' !== $decoderName) {
                $rights[$teamRoleName][$decoderName] = true;
            }
        }

        $teamDirectory = $this->resourceManager->create(
            $directory,
            $directoryType,
            $user,
            $workspace,
            $rootDirectory,
            null,
            $rights
        );

        // TODO : manage rights
        if (!is_null($resource)) {
            $this->resourceManager->copy(
                $resource,
                $teamDirectory->getResourceNode(),
                $user,
                true,
                false
            );
        }

        return $teamDirectory;
    }

    /**
     * Gets user teams in a workspace.
     *
     * @param User      $user
     * @param Workspace $workspace
     *
     * @return array
     */
    public function getTeamsByUserAndWorkspace(User $user, Workspace $workspace)
    {
        return $this->teamRepo->findTeamsByUserAndWorkspace($user, $workspace);
    }

    /**
     * Sets rights to team directory for all workspace roles.
     *
     * @param Team $team
     */
    public function initializeTeamRights(Team $team)
    {
        $workspace = $team->getWorkspace();
        $teamRole = $team->getRole();
        $teamManagerRole = $team->getTeamManagerRole();
        $resourceNode = !is_null($team->getDirectory()) ? $team->getDirectory()->getResourceNode() : null;

        if (!is_null($resourceNode)) {
            $workspaceRoles = $this->roleManager->getRolesByWorkspace($workspace);
            $rights = [];

            foreach ($workspaceRoles as $role) {
                if (!in_array($role->getUuid(), [$teamRole->getUuid(), $teamManagerRole->getUuid()])) {
                    $rights[$role->getName()] = [
                        'role' => $role,
                        'create' => [],
                        'open' => $team->isPublic(),
                    ];
                }
            }
            $this->applyRightsToResourceNode($resourceNode, $rights);
        }
    }

    /**
     * Updates permissions of team directory..
     *
     * @param Team $team
     */
    public function updateTeamDirectoryPerms(Team $team)
    {
        $directory = $team->getDirectory();

        if (!is_null($directory)) {
            $this->om->startFlushSuite();

            $workspace = $team->getWorkspace();
            $teamRole = $team->getRole();
            $teamManagerRole = $team->getTeamManagerRole();
            $workspaceRoles = $this->roleManager->getRolesByWorkspace($workspace);

            foreach ($workspaceRoles as $role) {
                if (!in_array($role->getUuid(), [$teamRole->getUuid(), $teamManagerRole->getUuid()])) {
                    $rights = ['open' => $team->isPublic()];
                    $this->rightsManager->editPerms($rights, $role, $directory->getResourceNode(), true);
                }
            }
            $this->om->endFlushSuite();
        }
    }

    /**
     * Initializes directory permissions. Used in command.
     *
     * @param Team  $team
     * @param array $roles
     */
    public function initializeTeamPerms(Team $team, array $roles)
    {
        $directory = $team->getDirectory();

        if (!is_null($directory)) {
            $this->om->startFlushSuite();
            $node = $directory->getResourceNode();

            foreach ($roles as $role) {
                if ($role === $team->getRole()) {
                    $perms = ['open' => true, 'edit' => true, 'export' => true, 'copy' => true];
                    $creatable = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();
                } elseif ($role === $team->getTeamManagerRole()) {
                    $perms = ['open' => true, 'edit' => true, 'export' => true, 'copy' => true, 'delete' => true, 'administrate' => true];
                    $creatable = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();
                } elseif ($team->isPublic()) {
                    $perms = ['open' => true];
                    $creatable = [];
                }

                $this->rightsManager->editPerms($perms, $role, $node, true, $creatable, true);
            }

            $this->om->endFlushSuite();
        }
    }

    /**
     * Registers users to a team.
     *
     * @param Team  $team
     * @param array $users
     */
    public function registerUsersToTeam(Team $team, array $users)
    {
        $teamRole = $team->getRole();

        if (!is_null($teamRole)) {
            $this->om->startFlushSuite();

            foreach ($users as $user) {
                $team->addUser($user);
                $this->roleManager->associateRole($user, $teamRole);
            }
            $this->om->persist($team);
            $this->om->endFlushSuite();
        }
    }

    /**
     * Unregisters users from a team.
     *
     * @param Team  $team
     * @param array $users
     */
    public function unregisterUsersFromTeam(Team $team, array $users)
    {
        $teamRole = $team->getRole();

        if (!is_null($teamRole)) {
            $this->om->startFlushSuite();

            foreach ($users as $user) {
                $team->removeUser($user);
                $this->roleManager->dissociateRole($user, $teamRole);
            }
            $this->om->persist($team);
            $this->om->endFlushSuite();
        }
    }

    /**
     * Registers users as team managers.
     *
     * @param Team  $team
     * @param array $users
     */
    public function registerManagersToTeam(Team $team, array $users)
    {
        $teamManagerRole = $team->getTeamManagerRole();

        if (!is_null($teamManagerRole) && 0 < count($users)) {
            $this->om->startFlushSuite();

            $team->setTeamManager($users[0]);

            foreach ($users as $user) {
                $this->roleManager->associateRole($user, $teamManagerRole);
            }
            $this->om->persist($team);
            $this->om->endFlushSuite();
        }
    }

    /**
     * Unregisters team managers.
     *
     * @param Team  $team
     * @param array $users
     */
    public function unregisterManagersFromTeam(Team $team, array $users)
    {
        $teamManagerRole = $team->getTeamManagerRole();

        if (!is_null($teamManagerRole)) {
            $this->om->startFlushSuite();

            $teamManager = $team->getTeamManager();

            foreach ($users as $user) {
                $this->roleManager->dissociateRole($user, $teamManagerRole);

                if ($teamManager && $teamManager->getid() === $user->getId()) {
                    $team->setTeamManager(null);
                }
            }
            $this->om->persist($team);
            $this->om->endFlushSuite();
        }
    }

    /**
     * Empty teams from all members.
     *
     * @param array $teams
     */
    public function emptyTeams(array $teams)
    {
        $this->om->startFlushSuite();

        foreach ($teams as $team) {
            $users = $team->getRole()->getUsers()->toArray();
            $this->unregisterUsersFromTeam($team, $users);
        }
        $this->om->endFlushSuite();
    }

    /**
     * Fills teams with workspace users who belong to no team.
     *
     * @param Workspace $workspace
     * @param array     $teams
     */
    public function fillTeams(Workspace $workspace, array $teams)
    {
        $this->om->startFlushSuite();
        $workspaceTeams = $this->teamRepo->findBy(['workspace' => $workspace]);
        $users = $this->teamRepo->findUsersWithNoTeamByWorkspace($workspace, $workspaceTeams);

        foreach ($teams as $team) {
            $maxUsers = $team->getMaxUsers();

            if (is_null($maxUsers)) {
                $this->registerUsersToTeam($team, $users);
                break;
            } else {
                $nbFreeSpaces = $maxUsers - count($team->getUsers()->toArray());

                while ($nbFreeSpaces > 0 && count($users) > 0) {
                    $index = rand(0, count($users) - 1);
                    $this->registerUsersToTeam($team, [$users[$index]]);
                    unset($users[$index]);
                    $users = array_values($users);
                    --$nbFreeSpaces;
                }

                if (0 === count($users)) {
                    break;
                }
            }
        }
        $this->om->endFlushSuite();
    }

    /**
     * Checks and updates role name for unicity.
     *
     * @param string $roleName
     * @param string $uuid
     *
     * @return string
     */
    private function computeValidRoleName($roleName, $uuid)
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
     *
     * @param Workspace $workspace
     * @param string    $key
     *
     * @return string
     */
    private function computeValidRoleTranslationKey(Workspace $workspace, $key)
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
     *
     * @param Workspace $workspace
     * @param Role      $role
     */
    private function setRightsForOldTeams(Workspace $workspace, Role $role)
    {
        $this->om->startFlushSuite();
        $teams = $this->teamRepo->findBy(['workspace' => $workspace]);

        foreach ($teams as $team) {
            $directory = $team->getDirectory();

            if ($team->isPublic() && !is_null($directory)) {
                $rights = [];
                $rights['open'] = true;
                $this->rightsManager->editPerms(
                    $rights,
                    $role,
                    $directory->getResourceNode(),
                    true
                );
            }
        }
        $this->om->endFlushSuite();
    }

    /**
     * @param ResourceNode $node
     * @param array        $rights
     */
    private function applyRightsToResourceNode(ResourceNode $node, array $rights)
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

    /**
     * @param Workspace $workspace
     * @param array     $nodes
     */
    private function linkResourceNodesArray(Workspace $workspace, array $nodes)
    {
        if (0 < count($nodes)) {
            $rootNode = $this->resourceManager->getWorkspaceRoot($workspace);
            $index = $this->resourceManager->getLastIndex($rootNode) + 1;

            foreach ($nodes as $node) {
                $node->setIndex($index);
                $this->om->persist($node);
                ++$index;
            }
        }
    }

    /*******************
     *  Old functions  *
     ******************/

    /**
     * @param Workspace $workspace
     * @param string    $orderedBy
     * @param string    $order
     *
     * @return array
     */
    public function getTeamsByWorkspace(Workspace $workspace, $orderedBy = 'name', $order = 'ASC')
    {
        return $this->teamRepo->findBy(['workspace' => $workspace], [$orderedBy => $order]);
    }

    /**
     * @param User   $user
     * @param string $orderedBy
     * @param string $order
     *
     * @return array
     */
    public function getTeamsByUser(User $user, $orderedBy = 'name', $order = 'ASC')
    {
        return $this->teamRepo->findTeamsByUser($user, $orderedBy, $order);
    }

    /**
     * Find all content for a given user and replace him by another.
     *
     * @param User $from
     * @param User $to
     *
     * @return int
     */
    public function replaceManager(User $from, User $to)
    {
        $teams = $this->teamRepo->findByTeamManager($from);

        if (count($teams) > 0) {
            foreach ($teams as $team) {
                $team->setTeamManager($to);
            }

            $this->om->flush();
        }

        return count($teams);
    }

    /**
     * Find all content for a given user and replace him by another.
     *
     * @param User $from
     * @param User $to
     *
     * @return int
     */
    public function replaceUser(User $from, User $to)
    {
        $teams = $this->teamRepo->findTeamsByUser($from);

        if (count($teams) > 0) {
            foreach ($teams as $team) {
                $team->removeUser($from);
                $team->addUser($to);
            }

            $this->om->flush();
        }

        return count($teams);
    }
}
