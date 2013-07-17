<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Repository\GroupRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Pager\PagerFactory;
use Symfony\Component\Translation\Translator;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.group_manager")
 */
class GroupManager
{
    private $om;
    /** @var GroupRepository */
    private $groupRepo;
    /** @var UserRepository */
    private $userRepo;
    private $pagerFactory;
    private $translator;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     * "om"           = @DI\Inject("claroline.persistence.object_manager"),
     * "pagerFactory" = @DI\Inject("claroline.pager.pager_factory"),
     * "translator"   = @DI\Inject("translator")
     * })
     */
    public function __construct(
        ObjectManager $om,
        PagerFactory $pagerFactory,
        Translator $translator
    )
    {
        $this->om = $om;
        $this->groupRepo = $om->getRepository('ClarolineCoreBundle:Group');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->pagerFactory = $pagerFactory;
        $this->translator = $translator;
    }

    public function insertGroup(Group $group)
    {
        $this->om->persist($group);
        $this->om->flush();
    }

    public function deleteGroup(Group $group)
    {
        $this->om->remove($group);
        $this->om->flush();
    }

    public function updateGroup(Group $group)
    {
        $this->om->persist($group);
        $this->om->flush();
    }

    public function addUsersToGroup(Group $group, array $users)
    {
        foreach ($users as $user) {
            if (!$group->containsUser($user)) {
                $group->addUser($user);
            }
        }

        $this->om->persist($group);
        $this->om->flush();
    }

    public function removeUsersFromGroup(Group $group, array $users)
    {
        foreach ($users as $user) {
            $group->removeUser($user);
        }

        $this->om->persist($group);
        $this->om->flush();
    }

    public function importUsers(Group $group, array $users)
    {
        $toImport = array();
        $nonImportedUsers = array();

        foreach ($users as $user) {
            $firstName = $user[0];
            $lastName = $user[1];
            $username = $user[2];

            $existingUser = $this->userRepo->findOneBy(
                array(
                    'username' => $username,
                    'firstName' => $firstName,
                    'lastName' => $lastName
                )
            );

            if (is_null($existingUser)) {
                $nonImportedUsers[] = $username;
            }
            else {
                $toImport[] = $existingUser;
            }
        }
        $this->addUsersToGroup($group, $toImport);

        return $nonImportedUsers;
    }

    public function convertGroupsToArray(array $groups)
    {
        $content = array();
        $i = 0;

        foreach ($groups as $group) {
            $content[$i]['id'] = $group->getId();
            $content[$i]['name'] = $group->getName();

            $rolesString = '';
            $roles = $group->getEntityRoles();
            $rolesCount = count($roles);
            $j = 0;

            foreach ($roles as $role) {
                $rolesString .= "{$this->translator->trans($role->getTranslationKey(), array(), 'platform')}";

                if ($j < $rolesCount - 1) {
                    $rolesString .= ' ,';
                }
                $j++;
            }
            $content[$i]['roles'] = $rolesString;
            $i++;
        }

        return $content;
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
