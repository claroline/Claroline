<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Database\Writer;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Repository\GroupRepository;
use Claroline\CoreBundle\Pager\PagerFactory;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.group_manager")
 */
class GroupManager
{
    private $writer;
    private $groupRepo;
    private $pagerFactory;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "groupRepo"      = @DI\Inject("claroline.repository.group_repository"),
     *     "writer"         = @DI\Inject("claroline.database.writer"),
     *     "pagerFactory"   = @DI\Inject("claroline.pager.pager_factory")
     * })
     */
    public function __construct(
        GroupRepository $groupRepo,
        Writer $writer,
        PagerFactory $pagerFactory
    )
    {
        $this->writer = $writer;
        $this->groupRepo = $groupRepo;
        $this->pagerFactory = $pagerFactory;
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

    public function getWorkspaceOutsiders(AbstractWorkspace $workspace, $page)
    {
        $query = $this->groupRepo->findWorkspaceOutsiders($workspace, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    public function getWorkspaceOutsidersByName(AbstractWorkspace $workspace, $search, $page)
    {
        $query = $this->groupRepo->findWorkspaceOutsidersByName($workspace, $search, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    public function getGroupsByWorkspace(AbstractWorkspace $workspace, $page)
    {
        $query = $this->groupRepo->findByWorkspace($workspace, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    public function getGroupsByWorkspaceAndName(AbstractWorkspace $workspace, $search, $page)
    {
        $query = $this->groupRepo->findByWorkspaceAndName($workspace, $search, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    public function getGroups($page)
    {
        $query = $this->groupRepo->findAll(false);

        return $this->pagerFactory->createPager($query, $page);
    }

    public function getGroupsByName($search, $page)
    {
        $query = $this->groupRepo->findByName($search, false);

        return $this->pagerFactory->createPager($query, $page);
    }
}