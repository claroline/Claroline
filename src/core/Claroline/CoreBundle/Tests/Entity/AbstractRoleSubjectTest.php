<?php

namespace Claroline\CoreBundle\Entity;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;

class AbstractRoleSubjectTest extends FixtureTestCase
{
    //must add 1 role everytime because there is a personnalWS every single time for users.
    protected function setUp()
    {
        parent::setUp();
        $this->loadPlatformRolesFixture();
    }

    public function testAddAndRemoveRole()
    {
        $this->loadRoleData(array('role_a'));
        $this->loadUserData(array('user' => 'user'));
        $roleA = $this->getRole('role_a');
        $user = $this->getUser('user');
        $user->addRole($roleA);
        $this->assertEquals(3, count($user->getEntityRoles()));
        $user->removeRole($roleA);
        $this->assertEquals(2, count($user->getEntityRoles()));
        $this->loadGroupData(array('group_a' => array()));
        $groupA = $this->getGroup(('group_a'));
        $groupA->removeRole($this->getRole('group_a'));
        $this->assertEquals(0, count($groupA->getEntityRoles()));
    }

    public function testAddAlreadyOwnedRoleDoesntAddNewRole()
    {
        $this->loadUserData(array('user' => 'user'));
        $userRole = $this->em
            ->getRepository('ClarolineCoreBundle:Role')
            ->findOneByName('ROLE_USER');
        $user = $this->getUser('user');
        $this->assertEquals(2, count($user->getRoles()));
        $user->addRole($userRole);
        $this->assertEquals(2, count($user->getRoles()));
    }
}