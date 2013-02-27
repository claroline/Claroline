<?php

namespace Claroline\CoreBundle\Entity;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Library\Security\PlatformRoles;

class UserTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadPlatformRolesFixture();
    }

    public function testGetRolesReturnsUserOwnedRolesAndGroupOwnedRolesAndIncludesRoleAncestors()
    {
        $this->loadUserData(array('ws_creator' => 'ws_creator'));
        $this->loadRoleData(array(array('role_c' => null)));
        $this->loadRoleData(array(array('role_e' => 'role_c')));
        $this->loadRoleData(array(array('role_f' => 'role_e')));
        $this->loadGroupData(array('group_c' => array('ws_creator')));
        $group = $this->getGroup('group_c');
        $group->addRole($this->getRole('role_f'));

        $wsCreator = $this->getFixtureReference('user/ws_creator');

        $this->logUser($wsCreator);
        $securityContext = $this->getSecurityContext();
        $this->assertTrue($securityContext->isGranted(PlatformRoles::USER));
        $this->assertTrue($securityContext->isGranted(PlatformRoles::WS_CREATOR));

        $groupC = $this->getFixtureReference('group/group_c');
        $securityContext->getToken()->setUser($wsCreator); // refresh session info

        $this->assertTrue($securityContext->isGranted(PlatformRoles::USER));
        $this->assertTrue($securityContext->isGranted(PlatformRoles::WS_CREATOR));
        $this->assertTrue($securityContext->isGranted('ROLE_role_c'));
        $this->assertTrue($securityContext->isGranted('ROLE_role_e'));
        $this->assertTrue($securityContext->isGranted('ROLE_role_f'));

        $groupC->removeUser($wsCreator);
        $this->getEntityManager()->flush();
        $securityContext->getToken()->setUser($wsCreator); // refresh session info

        $this->assertTrue($securityContext->isGranted(PlatformRoles::USER));
        $this->assertTrue($securityContext->isGranted(PlatformRoles::WS_CREATOR));
        $this->assertFalse($securityContext->isGranted('ROLE_role_c'));
        $this->assertFalse($securityContext->isGranted('ROLE_role_e'));
        $this->assertFalse($securityContext->isGranted('ROLE_role_f'));
    }

    public function testHasRoleReliesOnGetRolesMethod()
    {
        $this->loadUserData(array('admin' => 'admin'));
        $this->loadRoleData(array(array('role_c' => null)));
        $this->loadRoleData(array(array('role_d' => 'role_c')));
        $this->loadGroupData(array('group_b' => array('admin')));
        $groupB = $this->getGroup('group_b');
        $groupB->addRole($this->getRole('role_d'));
        $admin = $this->getUser('admin');
        $this->assertTrue($admin->hasRole('ROLE_role_c'));
        $this->assertTrue($admin->hasRole('ROLE_role_d'));
    }
}