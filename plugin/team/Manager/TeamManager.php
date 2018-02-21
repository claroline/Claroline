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
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolRightsManager;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\TeamBundle\API\Serializer\TeamSerializer;
use Claroline\TeamBundle\Entity\Team;
use Claroline\TeamBundle\Entity\WorkspaceTeamParameters;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("claroline.manager.team_manager")
 */
class TeamManager
{
    private $om;
    private $pagerFactory;
    private $resourceManager;
    private $rightsManager;
    private $roleManager;
    private $teamSerializer;
    private $translator;

    private $teamRepo;
    private $workspaceTeamParamsRepo;
    private $toolRightsManager;

    /**
     * @DI\InjectParams({
     *     "om"                = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory"      = @DI\Inject("claroline.pager.pager_factory"),
     *     "resourceManager"   = @DI\Inject("claroline.manager.resource_manager"),
     *     "rightsManager"     = @DI\Inject("claroline.manager.rights_manager"),
     *     "toolRightsManager" = @DI\Inject("claroline.manager.tool_rights_manager"),
     *     "roleManager"       = @DI\Inject("claroline.manager.role_manager"),
     *     "teamSerializer"   = @DI\Inject("claroline.serializer.team"),
     *     "translator"        = @DI\Inject("translator")
     * })
     */
    public function __construct(
        ObjectManager $om,
        PagerFactory $pagerFactory,
        ResourceManager $resourceManager,
        RightsManager $rightsManager,
        RoleManager $roleManager,
        TeamSerializer $teamSerializer,
        TranslatorInterface $translator,
        ToolRightsManager $toolRightsManager
    ) {
        $this->om = $om;
        $this->pagerFactory = $pagerFactory;
        $this->resourceManager = $resourceManager;
        $this->rightsManager = $rightsManager;
        $this->roleManager = $roleManager;
        $this->teamSerializer = $teamSerializer;
        $this->translator = $translator;
        $this->toolRightsManager = $toolRightsManager;

        $this->teamRepo = $om->getRepository('ClarolineTeamBundle:Team');
        $this->workspaceTeamParamsRepo = $om->getRepository('ClarolineTeamBundle:WorkspaceTeamParameters');
    }

    public function createMultipleTeams(
        Workspace $workspace,
        User $user,
        $name,
        $nbTeams,
        $description,
        $maxUsers,
        $isPublic,
        $selfRegistration,
        $selfUnregistration,
        ResourceNode $resource = null,
        array $creatableResources = []
    ) {
        $this->om->startFlushSuite();
        $teams = [];
        $nodes = [];
        $index = 1;

        for ($i = 0; $i < $nbTeams; ++$i) {
            $team = new Team();
            $validName = $this->computeValidTeamName($workspace, $name, $index);
            $team->setName($validName['name']);
            $index = $validName['index'] + 1;
            $team->setWorkspace($workspace);
            $team->setDescription($description);
            $team->setMaxUsers($maxUsers);
            $team->setIsPublic($isPublic);
            $team->setSelfRegistration($selfRegistration);
            $team->setSelfUnregistration($selfUnregistration);

            $this->createTeam(
                $team,
                $workspace,
                $user,
                $resource,
                $creatableResources
            );
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

    public function createTeam(
        Team $team,
        Workspace $workspace,
        User $user,
        ResourceNode $resource = null,
        array $creatableResources = []
    ) {
        $this->om->startFlushSuite();
        $team->setWorkspace($workspace);
        $validName = $this->computeValidTeamName($workspace, $team->getName(), 0);
        $team->setName($validName['name']);
        $role = $this->createTeamRole($team, $workspace);
        $team->setRole($role);
        $teamManagerRole = $this->createTeamManagerRole($team, $workspace);
        $team->setTeamManagerRole($teamManagerRole);
        $directory = $this->createTeamDirectory(
            $team,
            $workspace,
            $user,
            $role,
            $teamManagerRole,
            $resource,
            $creatableResources
        );
        $team->setDirectory($directory);
        $this->om->persist($team);
        $this->om->endFlushSuite();
    }

    public function persistTeam(Team $team)
    {
        $this->om->persist($team);
        $this->om->flush();
    }

    public function deleteTeam(Team $team, $withDirectory = false)
    {
        $teamRole = $team->getRole();
        $teamManagerRole = $team->getTeamManagerRole();

        if (!is_null($teamManagerRole)) {
            $this->om->remove($teamManagerRole);
        }

        if (!is_null($teamRole)) {
            $this->om->remove($teamRole);
        }
        $this->om->remove($team);
        $teamDirectory = $team->getDirectory();

        if ($withDirectory && !is_null($teamDirectory)) {
            $this->resourceManager->delete($teamDirectory->getResourceNode());
        }
        $this->om->flush();
    }

    public function initializeTeamRights(Team $team)
    {
        $workspace = $team->getWorkspace();
        $teamRole = $team->getRole();
        $teamManagerRole = $team->getTeamManagerRole();
        $isPublic = $team->getIsPublic();
        $resourceNode = !is_null($team->getDirectory()) ?
            $team->getDirectory()->getResourceNode() :
            null;

        if (!is_null($resourceNode)) {
            $workspaceRoles = $this->roleManager->getRolesByWorkspace($workspace);
            $rights = [];

            foreach ($workspaceRoles as $role) {
                if ($role->getId() !== $teamRole->getId() &&
                    $role->getId() !== $teamManagerRole->getId() &&
                    !is_null($resourceNode)) {
                    $roleName = $role->getName();
                    $rights[$roleName] = [];
                    $rights[$roleName]['role'] = $role;
                    $rights[$roleName]['create'] = [];

                    if ($isPublic) {
                        $rights[$roleName]['open'] = true;
                    }
                }
            }
            $this->applyRightsToResources($resourceNode, $rights);
        }
    }

    public function registerUserToTeam(Team $team, User $user)
    {
        $this->om->startFlushSuite();
        $teamRole = $team->getRole();
        $team->addUser($user);

        if (!is_null($teamRole)) {
            $this->roleManager->associateRole($user, $teamRole);
        }
        $this->om->persist($team);
        $this->om->endFlushSuite();
    }

    public function unregisterUserFromTeam(Team $team, User $user)
    {
        $this->om->startFlushSuite();
        $teamRole = $team->getRole();
        $team->removeUser($user);

        if (!is_null($teamRole)) {
            $this->roleManager->dissociateRole($user, $teamRole);
        }
        $this->om->persist($team);
        $this->om->endFlushSuite();
    }

    public function registerUsersToTeam(Team $team, array $users)
    {
        $this->om->startFlushSuite();
        $teamRole = $team->getRole();

        foreach ($users as $user) {
            $team->addUser($user);

            if (!is_null($teamRole)) {
                $this->roleManager->associateRole($user, $teamRole);
            }
        }
        $this->om->persist($team);
        $this->om->endFlushSuite();
    }

    public function unregisterUsersFromTeam(Team $team, array $users)
    {
        $this->om->startFlushSuite();
        $teamRole = $team->getRole();

        foreach ($users as $user) {
            $team->removeUser($user);

            if (!is_null($teamRole)) {
                $this->roleManager->dissociateRole($user, $teamRole);
            }
        }
        $this->om->persist($team);
        $this->om->endFlushSuite();
    }

    public function registerManagerToTeam(Team $team, User $user)
    {
        $this->om->startFlushSuite();
        $currentTeamManager = $team->getTeamManager();
        $teamManagerRole = $team->getTeamManagerRole();
        $team->setTeamManager($user);

        if (!is_null($teamManagerRole)) {
            if (!is_null($currentTeamManager)) {
                $this->roleManager
                    ->dissociateRole($currentTeamManager, $teamManagerRole);
            }
            $this->roleManager->associateRole($user, $teamManagerRole);
        }
        $this->om->persist($team);
        $this->om->endFlushSuite();
    }

    public function unregisterManagerFromTeam(Team $team)
    {
        $this->om->startFlushSuite();
        $teamManager = $team->getTeamManager();
        $teamManagerRole = $team->getTeamManagerRole();
        $team->setTeamManager(null);

        if (!is_null($teamManagerRole) && !is_null($teamManager)) {
            $this->roleManager->dissociateRole($teamManager, $teamManagerRole);
        }
        $this->om->persist($team);
        $this->om->endFlushSuite();
    }

    public function deleteTeams(array $teams, $withDirectory = false)
    {
        $this->om->startFlushSuite();
        $nodes = [];

        foreach ($teams as $team) {
            if ($withDirectory) {
                $directory = $team->getDirectory();

                if (!is_null($directory)) {
                    $nodes[] = $directory->getResourceNode();
                }
            }
            $this->deleteTeam($team);
        }
        $this->om->endFlushSuite();

        if (count($nodes) > 0) {
            foreach ($nodes as $node) {
                $this->resourceManager->delete($node);
            }
            $this->om->flush();
        }
    }

    public function emptyTeams(array $teams)
    {
        $this->om->startFlushSuite();

        foreach ($teams as $team) {
            $users = $team->getUsers();
            $this->unregisterUsersFromTeam($team, $users);
        }
        $this->om->endFlushSuite();
    }

    public function fillTeams(Workspace $workspace, array $teams)
    {
        $this->om->startFlushSuite();
        $workspaceTeams = $this->teamRepo->findTeamsByWorkspace($workspace);
        $users = $this->teamRepo
            ->findUsersWithNoTeamByWorkspace($workspace, $workspaceTeams);

        foreach ($teams as $team) {
            $maxUsers = $team->getMaxUsers();

            if (is_null($maxUsers)) {
                $this->registerUsersToTeam($team, $users);
                break;
            } else {
                $nbFreeSpaces = $maxUsers - count($team->getUsers());

                while ($nbFreeSpaces > 0 && count($users) > 0) {
                    $index = rand(0, count($users) - 1);
                    $this->registerUserToTeam($team, $users[$index]);
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

    private function createTeamRole(Team $team, Workspace $workspace)
    {
        $teamName = $team->getName();
        $roleName = $this->computeValidRoleName(
            strtoupper(str_replace(' ', '_', $teamName)),
            $workspace->getGuid()
        );
        $roleKey = $this->computeValidRoleTranslationKey($workspace, $teamName);

        $role = $this->roleManager->createWorkspaceRole(
            $roleName,
            $roleKey,
            $workspace
        );

        $root = $this->resourceManager->getWorkspaceRoot($workspace);
        $this->rightsManager->editPerms(['open' => true], $role, $root);
        $this->setRightsForOldTeams($workspace, $role);
        $orderedTool = $this->om->getRepository('ClarolineCoreBundle:Tool\OrderedTool')
          ->findOneBy(['workspace' => $workspace, 'name' => 'resource_manager']);
        $this->toolRightsManager->setToolRights($orderedTool, $role, 1);

        return $role;
    }

    private function createTeamManagerRole(Team $team, Workspace $workspace)
    {
        $teamName = $team->getName();
        $roleName = $this->computeValidRoleName(
            strtoupper(str_replace(' ', '_', $teamName.'_MANAGER')),
            $workspace->getGuid()
        );
        $roleKey = $this->computeValidRoleTranslationKey(
            $workspace,
            $teamName.' manager'
        );

        $role = $this->roleManager->createWorkspaceRole(
            $roleName,
            $roleKey,
            $workspace
        );

        $root = $this->resourceManager->getWorkspaceRoot($workspace);
        $this->rightsManager->editPerms(['open' => true], $role, $root);
        $orderedTool = $this->om->getRepository('ClarolineCoreBundle:Tool\OrderedTool')->findOneBy(['workspace' => $workspace, 'name' => 'resource_manager']);
        $this->toolRightsManager->setToolRights($orderedTool, $role, 1);

        $this->setRightsForOldTeams($workspace, $role);

        return $role;
    }

    private function setRightsForOldTeams(Workspace $workspace, Role $role)
    {
        $this->om->startFlushSuite();
        $teams = $this->teamRepo->findTeamsByWorkspace($workspace);

        foreach ($teams as $team) {
            $directory = $team->getDirectory();

            if ($team->getIsPublic() && !is_null($directory)) {
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

    private function computeValidRoleName($roleName, $guid)
    {
        $i = 1;
        $name = 'ROLE_WS_'.$roleName.'_'.$guid;
        $role = $this->roleManager
            ->getRoleByName($name);

        while (!is_null($role)) {
            $name = 'ROLE_WS_'.$roleName.'_'.$i.'_'.$guid;
            $role = $this->roleManager
                ->getRoleByName($name);
            ++$i;
        }

        return $name;
    }

    private function computeValidRoleTranslationKey(Workspace $workspace, $key)
    {
        $i = 1;
        $translationKey = $key;
        $role = $this->roleManager->getRoleByTranslationKeyAndWorkspace(
            $translationKey,
            $workspace
        );

        while (!is_null($role)) {
            $translationKey = $key.' ('.$i.')';
            $role = $this->roleManager->getRoleByTranslationKeyAndWorkspace(
                $translationKey,
                $workspace
            );
            ++$i;
        }

        return $translationKey;
    }

    private function computeValidTeamName(Workspace $workspace, $teamName, $index)
    {
        $name = 0 === $index ? $teamName : $teamName.' '.$index;

        $teams = $this->teamRepo->findTeamsByWorkspaceAndName($workspace, $name);

        while (count($teams) > 0) {
            ++$index;
            $name = $teamName.' '.$index;

            $teams = $this->teamRepo
                ->findTeamsByWorkspaceAndName($workspace, $name);
        }

        return ['name' => $name, 'index' => $index];
    }

    private function createTeamDirectory(
        Team $team,
        Workspace $workspace,
        User $user,
        Role $teamRole,
        Role $teamManagerRole,
        ResourceNode $resource = null,
        array $creatableResources = []
    ) {
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
            $rights[$teamManagerRoleName]['create'][] =
                ['name' => $resourceType->getName()];
        }

        foreach ($creatableResources as $creatableResource) {
            $rights[$teamRoleName]['create'][] =
                ['name' => $creatableResource->getName()];
        }
        $decoders = $directoryType->getMaskDecoders();

        foreach ($decoders as $decoder) {
            $decoderName = $decoder->getName();
            $rights[$teamManagerRoleName][$decoderName] = true;

            if ('administrate' !== $decoderName && 'delete' !== $decoderName) {
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

        if (!is_null($resource)) {
            $this->resourceManager->copy(
                $resource,
                $teamDirectory->getResourceNode(),
                $user,
                true,
                true,
                $rights
            );
        }

        return $teamDirectory;
    }

    private function linkResourceNodesArray(Workspace $workspace, array $nodes)
    {
        if (count($nodes) > 0) {
            $rootNode = $this->resourceManager->getWorkspaceRoot($workspace);
            $index = $this->resourceManager->getLastIndex($rootNode) + 1;

            foreach ($nodes as $node) {
                $node->setIndex($index);
                $this->om->persist($node);
                ++$index;
            }
        }
    }

    private function applyRightsToResources(ResourceNode $node, array $rights)
    {
        $this->om->startFlushSuite();
        $this->resourceManager->createRights($node, $rights);

        if ('directory' === $node->getResourceType()->getName()) {
            foreach ($node->getChildren() as $child) {
                $this->applyRightsToResources($child, $rights);
            }
        }
        $this->om->endFlushSuite();
    }

    /***********************************
     * WorkspaceTeamParameters methods *
     ***********************************/

    public function createWorkspaceTeamParameters(Workspace $workspace)
    {
        $params = new WorkspaceTeamParameters();
        $params->setWorkspace($workspace);
        $params->setIsPublic(true);
        $params->setSelfRegistration(false);
        $params->setSelfUnregistration(false);
        $this->om->persist($params);
        $this->om->flush();

        return $params;
    }

    public function persistWorkspaceTeamParameters(WorkspaceTeamParameters $params)
    {
        $this->om->persist($params);
        $this->om->flush();
    }

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

    public function initializeTeamDirectoryPerms(Team $team)
    {
        $directory = $team->getDirectory();

        if (!is_null($directory)) {
            $this->om->startFlushSuite();

            $workspace = $team->getWorkspace();
            $teamRole = $team->getRole();
            $teamManagerRole = $team->getTeamManagerRole();
            $isPublic = $team->getIsPublic();
            $node = $directory->getResourceNode();
            $workspaceRoles = $this->roleManager->getRolesByWorkspace($workspace);

            foreach ($workspaceRoles as $role) {
                if ($role->getId() !== $teamRole->getId() &&
                    $role->getId() !== $teamManagerRole->getId()) {
                    $rights = [];

                    if ($isPublic) {
                        $rights['open'] = true;
                    }
                    $this->rightsManager->editPerms($rights, $role, $node, true);
                }
            }
            $this->om->endFlushSuite();
        }
    }

    /************************************
     * Access to TeamRepository methods *
     ************************************/

    public function getTeamsByWorkspace(
        Workspace $workspace,
        $orderedBy = 'name',
        $order = 'ASC',
        $executeQuery = true
    ) {
        return $this->teamRepo->findTeamsByWorkspace(
            $workspace,
            $orderedBy,
            $order,
            $executeQuery
        );
    }

    public function getTeamsByUser(
        User $user,
        $orderedBy = 'name',
        $order = 'ASC',
        $executeQuery = true
    ) {
        return $this->teamRepo->findTeamsByUser(
            $user,
            $orderedBy,
            $order,
            $executeQuery
        );
    }

    public function getTeamsWithUsersByWorkspace(Workspace $workspace, $executeQuery = true)
    {
        return $this->teamRepo->findTeamsWithUsersByWorkspace($workspace, $executeQuery);
    }

    public function getTeamsByUserAndWorkspace(User $user, Workspace $workspace, $executeQuery = true)
    {
        return $this->teamRepo->findTeamsByUserAndWorkspace($user, $workspace, $executeQuery);
    }

    public function getSearializedTeamsByUserAndWorkspace(User $user, Workspace $workspace)
    {
        $serializedTeams = [];
        $teams = $this->teamRepo->findTeamsByUserAndWorkspace($user, $workspace);

        foreach ($teams as $team) {
            $serializedTeams[] = $this->teamSerializer->serialize($team);
        }

        return $serializedTeams;
    }

    public function getUnregisteredUsersByTeam(
        Team $team,
        $orderedBy = 'username',
        $order = 'ASC',
        $page = 1,
        $max = 50,
        $executeQuery = true
    ) {
        $users = $this->teamRepo->findUnregisteredUsersByTeam(
            $team,
            $orderedBy,
            $order,
            $executeQuery
        );

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($users, $page, $max) :
            $this->pagerFactory->createPager($users, $page, $max);
    }

    public function getSearchedUnregisteredUsersByTeam(
        Team $team,
        $search = '',
        $orderedBy = 'username',
        $order = 'ASC',
        $page = 1,
        $max = 50,
        $executeQuery = true
    ) {
        $users = $this->teamRepo->findSearchedUnregisteredUsersByTeam(
            $team,
            $search,
            $orderedBy,
            $order,
            $executeQuery
        );

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($users, $page, $max) :
            $this->pagerFactory->createPager($users, $page, $max);
    }

    public function getWorkspaceUsers(
        Workspace $workspace,
        $orderedBy = 'firstName',
        $order = 'ASC',
        $page = 1,
        $max = 50,
        $executeQuery = true
    ) {
        $users = $this->teamRepo->findWorkspaceUsers($workspace, $orderedBy, $order, $executeQuery);

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($users, $page, $max) :
            $this->pagerFactory->createPager($users, $page, $max);
    }

    public function getSearchedWorkspaceUsers(
        Workspace $workspace,
        $search = '',
        $orderedBy = 'firstName',
        $order = 'ASC',
        $page = 1,
        $max = 50,
        $executeQuery = true
    ) {
        $users = $this->teamRepo->findSearchedWorkspaceUsers(
            $workspace,
            $search,
            $orderedBy,
            $order,
            $executeQuery
        );

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($users, $page, $max) :
            $this->pagerFactory->createPager($users, $page, $max);
    }

    public function getWorkspaceUsersWithManagers(
        Workspace $workspace,
        $orderedBy = 'firstName',
        $order = 'ASC',
        $page = 1,
        $max = 50,
        $executeQuery = true
    ) {
        $users = $this->teamRepo->findWorkspaceUsersWithManagers(
            $workspace,
            $orderedBy,
            $order,
            $executeQuery
        );

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($users, $page, $max) :
            $this->pagerFactory->createPager($users, $page, $max);
    }

    public function getSearchedWorkspaceUsersWithManagers(
        Workspace $workspace,
        $search = '',
        $orderedBy = 'firstName',
        $order = 'ASC',
        $page = 1,
        $max = 50,
        $executeQuery = true
    ) {
        $users = $this->teamRepo->findSearchedWorkspaceUsersWithManagers(
            $workspace,
            $search,
            $orderedBy,
            $order,
            $executeQuery
        );

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($users, $page, $max) :
            $this->pagerFactory->createPager($users, $page, $max);
    }

    public function getNbTeamsByUsers(Workspace $workspace, array $users, $executeQuery = true)
    {
        return count($users) > 0 ? $this->teamRepo->findNbTeamsByUsers($workspace, $users, $executeQuery) : [];
    }

    public function getTeamsWithExclusionsByWorkspace(
        Workspace $workspace,
        array $excludedTeams,
        $orderedBy = 'name',
        $order = 'ASC',
        $executeQuery = true
    ) {
        if (count($excludedTeams) > 0) {
            return $this->teamRepo->findTeamsWithExclusionsByWorkspace(
                $workspace,
                $excludedTeams,
                $orderedBy,
                $order,
                $executeQuery
            );
        } else {
            return $this->teamRepo->findTeamsByWorkspace($workspace, $orderedBy, $order, $executeQuery);
        }
    }

    /*******************************************************
     * Access to WorkspaceTeamParametersRepository methods *
     *******************************************************/

    public function getParametersByWorkspace(Workspace $workspace, $executeQuery = true)
    {
        return $this->workspaceTeamParamsRepo->findParametersByWorkspace($workspace, $executeQuery);
    }
}
