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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\GroupRepository;

class GroupManager
{
    /** @var ObjectManager */
    private $om;
    /** @var GroupRepository */
    private $groupRepo;
    /** @var StrictDispatcher */
    private $eventDispatcher;
    /** @var RoleManager */
    private $roleManager;

    /**
     * GroupManager constructor.
     *
     * @param ObjectManager    $om
     * @param StrictDispatcher $eventDispatcher
     * @param RoleManager      $roleManager
     */
    public function __construct(
        ObjectManager $om,
        StrictDispatcher $eventDispatcher,
        RoleManager $roleManager
    ) {
        $this->om = $om;
        $this->groupRepo = $om->getRepository('ClarolineCoreBundle:Group');
        $this->eventDispatcher = $eventDispatcher;
        $this->roleManager = $roleManager;
    }

    /**
     * Persists and flush a group.
     *
     * @param Group $group
     *
     * @return Group
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
     * @param Group $group
     *
     * @todo should use Crud
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
     * Adds an array of user to a group.
     *
     * @param Group  $group
     * @param User[] $users
     *
     * @return User[]
     *
     * @throws Exception\AddRoleException
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
     * Removes an array of users from a group.
     *
     * @param Group  $group
     * @param User[] $users
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
     * Sets an array of platform role to a group.
     *
     * @param Group $group
     * @param array $roles
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
}
