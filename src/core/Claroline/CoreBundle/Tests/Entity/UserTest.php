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

    public function testGetRolesReturnsUserOwnedRolesAndGroupOwnedRoles()
    {
        $this->loadUserData(array('ws_creator' => 'ws_creator'));
        $this->loadRoleData(array('role_c', 'role_d', 'role_f'));
        $this->loadGroupData(array('group_c' => array('ws_creator')));
        $group = $this->getGroup('group_c');
        $group->addRole($this->getRole('role_f'));

        $wsCreator = $this->getUser('ws_creator');

        $this->logUser($wsCreator);
        $securityContext = $this->getSecurityContext();
        $this->assertTrue($securityContext->isGranted(PlatformRoles::USER));
        $this->assertTrue($securityContext->isGranted(PlatformRoles::WS_CREATOR));

        $groupC = $this->getGroup('group_c');
        //$wsCreator->addRole($this->getRole('role_c'));
        //$this->em->persist($wsCreator);
        //$this->em->flush();
        //$securityContext->getToken()->setUser($wsCreator); // refresh session info

        //$this->assertTrue($securityContext->isGranted(PlatformRoles::USER));
        //$this->assertTrue($securityContext->isGranted(PlatformRoles::WS_CREATOR));
        //$this->assertTrue($securityContext->isGranted('ROLE_role_c'));
        //$this->assertTrue($securityContext->isGranted('ROLE_group_c'));
        //$this->assertTrue($securityContext->isGranted('ROLE_role_f'));
        //
        $groupC->removeUser($wsCreator);
        $this->em->persist($wsCreator);
        $this->getEntityManager()->flush();
        $securityContext->getToken()->setUser($wsCreator); // refresh session info

        $this->assertTrue($securityContext->isGranted(PlatformRoles::USER));
        $this->assertTrue($securityContext->isGranted(PlatformRoles::WS_CREATOR));
        $this->assertFalse($securityContext->isGranted('ROLE_group_c'));
        $this->assertFalse($securityContext->isGranted('ROLE_role_f'));
    }
}