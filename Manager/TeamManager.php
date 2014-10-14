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

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\TeamBundle\Entity\Team;
use Claroline\TeamBundle\Entity\WorkspaceTeamParameters;
use JMS\DiExtraBundle\Annotation as DI;

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
    private $teamRepo;
    private $workspaceTeamParamsRepo;

    /**
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory"    = @DI\Inject("claroline.pager.pager_factory"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "rightsManager"   = @DI\Inject("claroline.manager.rights_manager"),
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager")
     * })
     */
    public function __construct(
        ObjectManager $om,
        PagerFactory $pagerFactory,
        ResourceManager $resourceManager,
        RightsManager $rightsManager,
        RoleManager $roleManager
    )
    {
        $this->om = $om;
        $this->pagerFactory = $pagerFactory;
        $this->resourceManager = $resourceManager;
        $this->rightsManager = $rightsManager;
        $this->roleManager = $roleManager;
        $this->teamRepo = $om->getRepository('ClarolineTeamBundle:Team');
        $this->workspaceTeamParamsRepo =
            $om->getRepository('ClarolineTeamBundle:WorkspaceTeamParameters');
    }

    public function createMultipleTeams(
        Workspace $workspace,
        User $user,
        $name,
        $nbTeams,
        $maxUsers,
        $isPublic,
        $selfRegistration,
        $selfUnregistration
    )
    {
        $this->om->startFlushSuite();
        $teams = array();
        $nodes = array();
        $index = 1;

        for ($i = 0; $i < $nbTeams; $i++) {
            $team = new Team();
            $validName = $this->computeValidTeamName($workspace, $name, $index);
            $team->setName($validName['name']);
            $index = $validName['index'] + 1;
            $team->setWorkspace($workspace);
            $team->setMaxUsers($maxUsers);
            $team->setIsPublic($isPublic);
            $team->setSelfRegistration($selfRegistration);
            $team->setSelfUnregistration($selfUnregistration);

            $this->createTeam($team, $workspace, $user);
            $node = $team->getDirectory()->getResourceNode();
            $node->setPrevious(null);
            $node->setNext(null);
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

    public function createTeam(Team $team, Workspace $workspace, User $user)
    {
        $this->om->startFlushSuite();
        $team->setWorkspace($workspace);
        $validName = $this->computeValidTeamName($workspace, $team->getName(), 0);
        $team->setName($validName['name']);
        $role = $this->createTeamRole($team, $workspace);
        $team->setRole($role);
        $directory = $this->createTeamDirectory($team, $workspace, $user, $role);
        $team->setDirectory($directory);
        $this->om->persist($team);
        $this->om->endFlushSuite();
    }

    public function persistTeam(Team $team)
    {
        $this->om->persist($team);
        $this->om->flush();
    }

    public function deleteTeam(Team $team)
    {
        $role = $team->getRole();

        if (!is_null($role)) {
            $this->om->remove($role);
        }
        $this->om->remove($team);
        $this->om->flush();
    }

    public function initializeTeamRights(Team $team)
    {
        $workspace = $team->getWorkspace();
        $teamRole = $team->getRole();
        $isPublic = $team->getIsPublic();
        $resourceNode = !is_null($team->getDirectory()) ?
            $team->getDirectory()->getResourceNode() :
            null;

        $workspaceRoles = $this->roleManager->getRolesByWorkspace($workspace);
        $rights = array();

        foreach ($workspaceRoles as $role) {

            if ($role->getId() !== $teamRole->getId() && !is_null($resourceNode)) {
                $roleName = $role->getName();
                $rights[$roleName] = array();
                $rights[$roleName]['role'] = $role;
                $rights[$roleName]['create'] = array();

                if ($isPublic) {
                    $rights[$roleName]['open'] = true;
                }
            }
        }
        $this->resourceManager->createRights($resourceNode, $rights);
    }

    public function registerUsersToTeam(Team $team, array $users)
    {
        $this->om->startFlushSuite();
        $teamRole = $team->getRole();

        foreach ($users as $user) {
            $this->roleManager->associateRole($user, $teamRole);
        }
        $this->om->endFlushSuite();
    }

    public function unregisterUsersFromTeam(Team $team, array $users)
    {
        $this->om->startFlushSuite();
        $teamRole = $team->getRole();

        foreach ($users as $user) {
            $this->roleManager->dissociateRole($user, $teamRole);
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

        //add the role to every resource of that workspace
        $nodes = $this->resourceManager->getByWorkspace($workspace);

        foreach ($nodes as $node) {
            $this->rightsManager->create(0, $role, $node, false, array());
        }

        return $role;
    }

    private function computeValidRoleName($roleName, $guid)
    {
        $i = 1;
        $name = 'ROLE_WS_' . $roleName . '_' . $guid;
        $role = $this->roleManager
            ->getRoleByName($name);

        while (!is_null($role)) {
            $name = 'ROLE_WS_' . $roleName . '_' . $i . '_' . $guid;
            $role = $this->roleManager
                ->getRoleByName($name);
            $i++;
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
            $translationKey = $key . ' (' . $i . ')';
            $role = $this->roleManager->getRoleByTranslationKeyAndWorkspace(
                $translationKey,
                $workspace
            );
            $i++;
        }

        return $translationKey;
    }

    private function computeValidTeamName(Workspace $workspace, $teamName, $index)
    {
        $name = $index === 0 ? $teamName : $teamName . ' ' . $index;

        $teams = $this->teamRepo->findTeamsByWorkspaceAndName($workspace, $name);

        while (count($teams) > 0) {
            $index++;
            $name = $teamName . ' ' . $index;

            $teams = $this->teamRepo
                ->findTeamsByWorkspaceAndName($workspace, $name);

        }

        return array('name' => $name, 'index' => $index);
    }

    private function createTeamDirectory(
        Team $team,
        Workspace $workspace,
        User $user,
        Role $role
    )
    {
        $rootDirectory = $this->resourceManager->getWorkspaceRoot($workspace);
        $directoryType = $this->resourceManager->getResourceTypeByName('directory');
        $resourceTypes = $this->resourceManager->getAllResourceTypes();

        $directory = $this->resourceManager->createResource(
            'Claroline\CoreBundle\Entity\Resource\Directory',
            $team->getName()
        );
        $roleName = $role->getName();
        $rights = array();
        $rights[$roleName] = array();
        $rights[$roleName]['role'] = $role;
        $rights[$roleName]['create'] = array();

        foreach ($resourceTypes as $resourceType) {
            $rights[$roleName]['create'][] =
                array('name' => $resourceType->getName());
        }
        $decoders = $directoryType->getMaskDecoders();

        foreach ($decoders as $decoder) {
            $rights[$roleName][$decoder->getName()] = true;
        }

        return $this->resourceManager->create(
            $directory,
            $directoryType,
            $user,
            $workspace,
            $rootDirectory,
            null,
            $rights
        );
    }

    private function linkResourceNodesArray(Workspace $workspace, array $nodes)
    {
        if (count($nodes) > 0) {
            $rootNode = $this->resourceManager->getWorkspaceRoot($workspace);
            $lastNode = $this->resourceManager
                ->findPreviousOrLastRes($rootNode);

            if (!is_null($lastNode)) {
                $nodes[0]->setPrevious($lastNode);
                $lastNode->setNext($nodes[0]);
                $this->om->persist($nodes[0]);
                $this->om->persist($lastNode);
            }

            for ($i = 0; $i < count($nodes); $i++) {

                if (isset($nodes[$i]) && isset($nodes[$i + 1])) {
                    $nodes[$i]->setNext($nodes[$i + 1]);
                    $nodes[$i + 1]->setPrevious($nodes[$i]);
                    $this->om->persist($nodes[$i]);
                    $this->om->persist($nodes[$i + 1]);
                }
            }
        }
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


    /************************************
     * Access to TeamRepository methods *
     ************************************/

    public function getTeamsByWorkspace(
        Workspace $workspace,
        $orderedBy = 'name',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        return $this->teamRepo->findTeamsByWorkspace(
            $workspace,
            $orderedBy,
            $order,
            $executeQuery
        );
    }

    public function getTeamsWithUsersByWorkspace(
        Workspace $workspace,
        $executeQuery = true
    )
    {
        return $this->teamRepo->findTeamsWithUsersByWorkspace(
            $workspace,
            $executeQuery
        );
    }


    /*******************************************************
     * Access to WorkspaceTeamParametersRepository methods *
     *******************************************************/

    public function getParametersByWorkspace(
        Workspace $workspace,
        $executeQuery = true
    )
    {
        return $this->workspaceTeamParamsRepo->findParametersByWorkspace(
            $workspace,
            $executeQuery
        );
    }
}
