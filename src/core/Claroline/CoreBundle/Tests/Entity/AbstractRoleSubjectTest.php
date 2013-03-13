<?php

namespace Claroline\CoreBundle\Entity;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;
use Claroline\CoreBundle\Library\Security\PlatformRoles;

class AbstractRoleSubjectTest extends FixtureTestCase
{
    //must add 1 role everytime because there is a personnalWS every single time for users.
    protected function setUp()
    {
        parent::setUp();
        $this->loadPlatformRolesFixture();
    }

    public function testgetOwnedRolesReturnsOnlyLeafChildrenRolesByDefault()
    {
        $this->loadUserData(array('admin' => 'admin'));
        $admin = $this->getUser('admin');
        $this->assertEquals(2, count($admin->getOwnedRoles()));
        $this->loadGroupData(array('group_a' => array()));
        $groupA = $this->getGroup('group_a');
        $this->assertEquals(1, count($groupA->getOwnedRoles()));
    }

    public function testgetOwnedRolesCanIncludeAncestorRolesInTheList()
    {
        $this->loadUserData(array('admin' => 'admin'));
        $admin = $this->getUser('admin');
        $this->assertEquals(4, count($admin->getOwnedRoles(true)));
        $this->loadGroupData(array('group_b' => array()));
        $this->loadRoleData(array(array('role_c' => null), array('role_d' => 'role_c')));
        $groupB = $this->getGroup('group_b');
        $groupB->addRole($this->getRole('role_d'));
        $this->assertEquals(3, count($groupB->getOwnedRoles(true)));
    }

    public function testAddAndRemoveNonHierarchicalRole()
    {
        $this->loadRoleData(array(array('role_a' => null)));
        $this->loadUserData(array('user' => 'user'));
        $roleA = $this->getRole('role_a');
        $user = $this->getUser('user');
        $user->addRole($roleA);
        $this->assertEquals(3, count($user->getOwnedRoles()));
        $user->removeRole($roleA);
        $this->assertEquals(2, count($user->getOwnedRoles()));
        $this->loadGroupData(array('group_a' => array()));
        $groupA = $this->getGroup(('group_a'));
        $groupA->removeRole($this->getRole('group_a'));
        $this->assertEquals(0, count($groupA->getOwnedRoles()));
    }

    public function testAddARoleWhichIsAnAncestorOfAnAlreadyStoredRoleHasNoEffect()
    {
        $this->loadUserData(array('admin' => 'admin'));
        $admin = $this->getUser('admin');
        $this->assertEquals(2, count($admin->getOwnedRoles()));
        $admin->addRole($this->getRole('user'));
        $this->assertEquals(2, count($admin->getOwnedRoles()));
        $this->loadGroupData(array('group_c' => array()));
        $this->loadRoleData(array(array('role_e' => null), array('role_f' => 'role_e')));
        $groupC = $this->getGroup(('group_c'));
        $groupC->addRole($this->getRole('role_f'));
        $this->assertEquals(2, count($groupC->getOwnedRoles()));
        $admin->addRole($this->getRole('role_e'));
        $this->assertEquals(2, count($groupC->getOwnedRoles()));
    }

    public function testRemoveAChildrenRoleDoesntAffectParentRole()
    {
        $this->loadUserData(array('ws_creator' => 'ws_creator'));
        $user = $this->getUser('ws_creator');
        $wsCreatorRole = $this->getRole('ws_creator');
        $user->removeRole($wsCreatorRole);
        $userRoles = $user->getOwnedRoles(true);
        $this->assertEquals(2, count($userRoles));
        $this->assertEquals(PlatformRoles::USER, $userRoles[1]->getName());
        $this->loadGroupData(array('group_b' => array()));
        $this->loadRoleData(array(array('role_c' => null), array('role_d' => 'role_c')));
        $groupB = $this->getGroup('group_b');
        $groupB->addRole($this->getRole('role_c'));
        $roleD = $this->getRole('role_d');
        $groupB->removeRole($roleD);
        $groupRoles = $groupB->getOwnedRoles(true);
        $this->assertEquals(2, count($groupRoles));
        $this->assertEquals('ROLE_role_c', $groupRoles[1]->getName());
    }

    public function testRemoveAnAncestorRoleRemovesDescendantsIfAny()
    {
        $this->loadUserData(array('admin' => 'admin'));
        $admin = $this->getUser('admin');
        $userRole = $this->getRole('user');
        $admin->removeRole($userRole);
        $this->assertEquals(1, count($admin->getOwnedRoles(true)));
        $this->loadGroupData(array('group_c' => array()));
        $this->loadRoleData(array(array('role_e' => null), array('role_f' => 'role_e')));
        $groupC = $this->getGroup('group_c');
        $roleE = $this->getRole('role_e');
        $groupC->addRole($roleE);
        $groupC->removeRole($roleE);
        $groupRoles = $groupC->getOwnedRoles(true);
        $this->assertEquals(1, count($groupRoles));
        $this->assertEquals('ROLE_group_c', $groupRoles[0]->getName());
    }
}