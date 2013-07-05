<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Database\Writer;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Repository\GroupRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.group_manager")
 */
class GroupManager
{
    private $writer;
    private $groupRepo;

 /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "groupRepo" = @DI\Inject("claroline.repository.group_repository"),
     *     "writer" = @DI\Inject("claroline.database.writer")
     * })
     */
    public function __construct(
        GroupRepository $groupRepo,
        Writer $writer
    )
    {
        $this->writer = $writer;
        $this->groupRepo = $groupRepo;
    }

    public function insertGroup(Group $group)
    {
        $this->writer->create($group);
    }

    public function deleteGroup(Group $group)
    {
        $this->writer->delete($group);
    }

    public function updateGroup(Group $group)
    {
        $this->writer->update($group);
    }

    public function addUsersToGroup(Group $group, array $users)
    {
        foreach ($users as $user) {
            $group->addUser($user);
        }
        $this->writer->update($group);
    }

    public function removeUsersFromGroup(Group $group, array $users)
    {
        foreach ($users as $user) {
            $group->removeUser($user);
        }
        $this->writer->update($group);
    }

    public function getWorkspaceOutsiders(AbstractWorkspace $workspace, $getQuery = false)
    {
        return $this->groupRepo->findWorkspaceOutsiders($workspace, $getQuery);
    }

    public function getWorkspaceOutsidersByName(AbstractWorkspace $workspace, $search, $getQuery = false)
    {
        return $this->groupRepo->findWorkspaceOutsidersByName($workspace, $search, $getQuery);
    }

    public function getGroupsByWorkspaceAndName(AbstractWorkspace $workspace, $search, $getQuery = false)
    {
        return $this->groupRepo->findByWorkspaceAndName($workspace, $search, $getQuery);
    }

    public function getAllGroups($getQuery = false)
    {
        return $this->groupRepo->findAll($getQuery);
    }

    public function getGroupsByName($search, $getQuery = false)
    {
        return $this->groupRepo->findByName($search, $getQuery);
    }

    public function getGroupsByWorkspace(AbstractWorkspace $workspace, $getQuery = false)
    {
        return $this->groupRepo->findByWorkspace($workspace, $getQuery);
    }
}