<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Repository\GroupRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
    private $eventDispatcher;
    private $roleManager;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory"    = @DI\Inject("claroline.pager.pager_factory"),
     *     "translator"      = @DI\Inject("translator"),
     *     "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "container"       = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        ObjectManager $om,
        PagerFactory $pagerFactory,
        TranslatorInterface $translator,
        StrictDispatcher $eventDispatcher,
        RoleManager $roleManager,
        ContainerInterface $container
    ) {
        $this->om = $om;
        $this->groupRepo = $om->getRepository('ClarolineCoreBundle:Group');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->pagerFactory = $pagerFactory;
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
        $this->roleManager = $roleManager;
        $this->container = $container;
    }

    /**
     * Persists and flush a group.
     *
     * @param \Claroline\CoreBundle\Entity\Group $group
     */
    public function insertGroup(Group $group)
    {
        $this->om->persist($group);
        $this->eventDispatcher->dispatch('log', 'Log\LogGroupCreate', [$group]);
        $this->om->flush();

        return $group;
    }

    /**
     * Removes a group.
     *
     * @param \Claroline\CoreBundle\Entity\Group $group
     */
    public function deleteGroup(Group $group)
    {
        $this->eventDispatcher->dispatch(
            'claroline_groups_delete',
            'GenericData',
            [[$group]]
        );

        $this->om->remove($group);
        $this->om->flush();
    }

    /**
     * @todo what does this method do ?
     *
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param Role[]                             $oldRoles
     */
    public function updateGroup(Group $group, array $oldRoles)
    {
        $unitOfWork = $this->om->getUnitOfWork();
        $unitOfWork->computeChangeSets();
        $changeSet = $unitOfWork->getEntityChangeSet($group);
        $newRoles = $group->getPlatformRoles();
        $oldRolesTranslationKeys = [];

        foreach ($oldRoles as $oldRole) {
            $oldRolesTranslationKeys[] = $oldRole->getTranslationKey();
        }

        $newRolesTransactionKeys = [];

        foreach ($newRoles as $newRole) {
            $newRolesTransactionKeys[] = $newRole->getTranslationKey();
        }

        $changeSet['platformRole'] = [$oldRolesTranslationKeys, $newRolesTransactionKeys];
        $this->eventDispatcher->dispatch('log', 'Log\LogGroupUpdate', [$group, $changeSet]);

        $this->om->persist($group);
        $this->om->flush();
    }

    /**
     * Adds an array of user to a group.
     *
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param User[]                             $users
     */
    public function addUsersToGroup(Group $group, array $users)
    {
        $addedUsers = [];

        if (!$this->validateAddUsersToGroup($users, $group)) {
            throw new Exception\AddRoleException();
        }

        foreach ($users as $user) {
            if (!$group->containsUser($user)) {
                $addedUsers[] = $user;
                $group->addUser($user);
                $this->eventDispatcher->dispatch('log', 'Log\LogGroupAddUser', [$group, $user]);
            }
        }

        $this->om->persist($group);
        $this->om->flush();

        return $addedUsers;
    }

    /**
     * Removes all users from a group.
     *
     * @param \Claroline\CoreBundle\Entity\Group $group
     */
    public function removeAllUsersFromGroup(Group $group)
    {
        $users = $group->getUsers();

        foreach ($users as $user) {
            $group->removeUser($user);
        }

        $this->om->persist($group);
        $this->om->flush();
    }

    /**
     * Removes an array of user from a group.
     *
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param User[]                             $users
     */
    public function removeUsersFromGroup(Group $group, array $users)
    {
        foreach ($users as $user) {
            $group->removeUser($user);
            $this->eventDispatcher->dispatch('log', 'Log\LogGroupRemoveUser', [$group, $user]);
        }

        $this->om->persist($group);
        $this->om->flush();
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param array                              $users
     *
     * @return array
     */
    public function importUsers(Group $group, array $users)
    {
        $toImport = $this->userRepo->findByUsernames($users);

        return $this->addUsersToGroup($group, $toImport);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param int                                              $page
     * @param int                                              $max
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getWorkspaceOutsiders(Workspace $workspace, $page, $max = 50)
    {
        $query = $this->groupRepo->findWorkspaceOutsiders($workspace, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param string                                           $search
     * @param int                                              $page
     * @param int                                              $max
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getWorkspaceOutsidersByName(Workspace $workspace, $search, $page, $max = 50)
    {
        $query = $this->groupRepo->findWorkspaceOutsidersByName($workspace, $search, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param int                                              $page
     * @param int                                              $max
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getGroupsByWorkspace(Workspace $workspace, $page, $max = 50)
    {
        $query = $this->groupRepo->findByWorkspace($workspace, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace[] $workspaces
     *
     * @return Group[]
     */
    public function getGroupsByWorkspaces(array $workspaces)
    {
        return $this->groupRepo->findGroupsByWorkspaces($workspaces, true);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace[] $workspaces
     * @param string                                             $search
     *
     * @return Group[]
     */
    public function getGroupsByWorkspacesAndSearch(array $workspaces, $search)
    {
        return $this->groupRepo->findGroupsByWorkspacesAndSearch(
            $workspaces,
            $search
        );
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param string                                           $search
     * @param int                                              $page
     * @param int                                              $max
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getGroupsByWorkspaceAndName(Workspace $workspace, $search, $page, $max = 50)
    {
        $query = $this->groupRepo->findByWorkspaceAndName($workspace, $search, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param int    $page
     * @param int    $max
     * @param string $orderedBy
     * @param string $order
     *
     * @deprecated use api instead
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getGroups($page, $max = 50, $orderedBy = 'id', $order = null)
    {
        $query = $this->groupRepo->findAll(false, $orderedBy, $order);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param string $search
     * @param int    $page
     * @param int    $max
     * @param string $orderedBy
     *
     * @deprecated use api instead
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getGroupsByName($search, $page, $max = 50, $orderedBy = 'id')
    {
        $query = $this->groupRepo->findByName($search, false, $orderedBy);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Role[] $roles
     * @param int                                 $page
     * @param int                                 $max
     * @param string                              $orderedBy
     * @param null                                $order
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getGroupsByRoles(array $roles, $page = 1, $max = 50, $orderedBy = 'id', $order = null)
    {
        $query = $this->groupRepo->findByRoles($roles, true, $orderedBy, $order);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Role[]              $roles
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param int                                              $page
     * @param int                                              $max
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getOutsidersByWorkspaceRoles(array $roles, Workspace $workspace, $page = 1, $max = 50)
    {
        $query = $this->groupRepo->findOutsidersByWorkspaceRoles($roles, $workspace, true);

        return  $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Role[] $roles
     * @param string                              $name
     * @param int                                 $page
     * @param int                                 $max
     * @param string                              $orderedBy
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getGroupsByRolesAndName(array $roles, $name, $page = 1, $max = 50, $orderedBy = 'id')
    {
        $query = $this->groupRepo->findByRolesAndName($roles, $name, true, $orderedBy);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Role[]              $roles
     * @param string                                           $name
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param int                                              $page
     * @param int                                              $max
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getOutsidersByWorkspaceRolesAndName(
        array $roles,
        $name,
        Workspace $workspace,
        $page = 1,
        $max = 50
    ) {
        $query = $this->groupRepo->findOutsidersByWorkspaceRolesAndName($roles, $name, $workspace, true);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * Sets an array of platform role to a group.
     *
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param array                              $roles
     */
    public function setPlatformRoles(Group $group, $roles)
    {
        foreach ($group->getPlatformRoles() as $role) {
            $group->removeRole($role);
        }

        $this->om->persist($group);
        $this->roleManager->associateRoles($group, $roles);
        $this->om->flush();
    }

    public function validateAddUsersToGroup(array $users, Group $group)
    {
        return true;
        $countToRegister = count($users);
        $roles = $group->getPlatformRoles();

        foreach ($roles as $role) {
            $max = $role->getMaxUsers();
            $countRegistered = $this->om->getRepository('ClarolineCoreBundle:User')->countUsersByRoleIncludingGroup($role);

            if ($max < $countRegistered + $countToRegister) {
                return false;
            }
        }

        return true;
    }

    public function getGroupById($id)
    {
        return $this->groupRepo->findById($id);
    }

    public function getGroupByName($name, $executeQuery = true)
    {
        return $this->groupRepo->findGroupByName($name, $executeQuery);
    }

    public function getGroupByNameAndScheduledForInsert($name)
    {
        $group = $this->groupRepo->findGroupByName($name, true);

        if (!$group) {
            $group = $this->getGroupByNameScheduledForInsert($name);
        }

        return $group;
    }

    public function getGroupByNameScheduledForInsert($name)
    {
        $scheduledForInsert = $this->om->getUnitOfWork()->getScheduledEntityInsertions();

        foreach ($scheduledForInsert as $entity) {
            if ('Claroline\CoreBundle\Entity\Group' === get_class($entity)) {
                if ($entity->getName() === $name) {
                    return $entity;
                }
            }
        }
    }

    public function emptyGroup(Group $group)
    {
        $users = $group->getUsers();

        foreach ($users as $user) {
            $group->removeUser($user);
            $this->om->persist($user);
        }

        $this->om->persist($group);
        $this->om->flush();
    }

    public function searchPartialList($searches, $page, $limit, $count = false, $exclude = false)
    {
        $baseFieldsName = Group::getSearchableFields();

        $qb = $this->om->createQueryBuilder();
        $count ? $qb->select('count(g)') : $qb->select('g');
        $qb->from('Claroline\CoreBundle\Entity\Group', 'g');

        //Admin can see everything, but the others... well they can only see their own organizations.
        //Cli always win aswell
        if ('cli' !== php_sapi_name() || 'test' === $this->container->get('kernel')->getEnvironment()) {
            if (!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                $currentUser = $this->container->get('security.token_storage')->getToken()->getUser();
                $qb->join('g.organizations', 'go');
                $qb->join('go.administrators', 'ga');
                $qb->andWhere('ga.id = :userId');
                $qb->setParameter('userId', $currentUser->getId());
            }
        }

        foreach ($searches as $key => $search) {
            foreach ($search as $id => $el) {
                if (in_array($key, $baseFieldsName)) {
                    $string = "UPPER (g.{$key})";
                    if ($exclude) {
                        $string .= ' NOT';
                    }
                    $string .= " LIKE :{$key}{$id}";
                    $qb->andWhere($string);
                    $qb->setParameter($key.$id, '%'.strtoupper($el).'%');
                }
            }
        }

        $query = $qb->getQuery();

        if (null !== $page && null !== $limit && !$count) {
            $query->setMaxResults($limit);
            $query->setFirstResult($page * $limit);
        }

        return $count ? $query->getSingleScalarResult() : $query->getResult();
    }

    public function getAll()
    {
        return $this->groupRepo->findAll();
    }

    /**
     * Serialize a group array.
     *
     * @param Group[] $groups
     *
     * @return array
     */
    public function convertGroupsToArray(array $groups)
    {
        $content = [];
        $i = 0;
        foreach ($groups as $group) {
            $content[$i]['id'] = $group->getId();
            $content[$i]['name'] = $group->getName();
            $rolesString = '';
            $roles = $group->getEntityRoles();
            $rolesCount = count($roles);
            $j = 0;
            foreach ($roles as $role) {
                $rolesString .= "{$this->translator->trans($role->getTranslationKey(), [], 'platform')}";
                if ($j < $rolesCount - 1) {
                    $rolesString .= ' ,';
                }
                ++$j;
            }
            $content[$i]['roles'] = $rolesString;
            ++$i;
        }

        return $content;
    }

    /**
     * @param int $page
     * @param int $max
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getAllGroups($page, $max = 50)
    {
        $query = $this->groupRepo->findAll(false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param int    $page
     * @param string $search
     * @param int    $max
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getAllGroupsBySearch($page, $search, $max = 50)
    {
        $query = $this->groupRepo->findAllGroupsBySearch($search);

        return $this->pagerFactory->createPagerFromArray($query, $page, $max);
    }

    /**
     * @param string[] $names
     *
     * @return Group[]
     */
    public function getGroupsByNames(array $names)
    {
        if (count($names) > 0) {
            return $this->groupRepo->findGroupsByNames($names);
        }

        return [];
    }

    public function getAllGroupsWithoutPager(
        $orderedBy = 'id',
        $order = 'ASC',
        $executeQuery = true
    ) {
        return $this->groupRepo->findAllGroups($orderedBy, $order, $executeQuery);
    }

    public function importMembers($data, $group)
    {
        $data = $this->container->get('claroline.utilities.misc')->formatCsvOutput($data);
        $lines = str_getcsv($data, PHP_EOL);

        foreach ($lines as $line) {
            $users[] = str_getcsv($line, ';');
        }

        if ($this->validateAddUsersToGroup($users, $group)) {
            $roleUser = $this->roleManager->getRoleByName('ROLE_USER');
            $max = $roleUser->getMaxUsers();
            $total = $this->container->get('claroline.manager.user_manager')->countUsersByRoleIncludingGroup($roleUser);

            if ($total + count($users) > $max) {
                return false;
            }

            return $this->importUsers($group, $users);
        }
    }
}
