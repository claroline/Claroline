<?php

namespace Claroline\CoreBundle\Entity;

use Claroline\CoreBundle\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Entity\Role;

class UserTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->loadGroupFixture();
    }
    
    public function testGetRolesReturnsUserOwnedRolesAndGroupOwnedRolesAndIncludesRoleAncestors()
    {
        $wsCreator = $this->getFixtureReference('user/ws_creator');
        $groupC = $this->getFixtureReference('group/group_c');       
        $this->logUser($wsCreator);
        $securityContext = $this->getSecurityContext();
        
        $this->assertTrue($securityContext->isGranted('ROLE_USER'));
        $this->assertTrue($securityContext->isGranted('ROLE_WS_CREATOR'));
        
        $groupC->addUser($wsCreator);
        $this->getEntityManager()->flush();
        $securityContext->getToken()->setUser($wsCreator); // refresh session info
                        
        $this->assertTrue($securityContext->isGranted('ROLE_USER'));
        $this->assertTrue($securityContext->isGranted('ROLE_WS_CREATOR'));
        $this->assertTrue($securityContext->isGranted('ROLE_C'));
        $this->assertTrue($securityContext->isGranted('ROLE_E'));
        $this->assertTrue($securityContext->isGranted('ROLE_F'));
        
        $groupC->removeUser($wsCreator);
        $this->getEntityManager()->flush();
        $securityContext->getToken()->setUser($wsCreator); // refresh session info
        
        $this->assertTrue($securityContext->isGranted('ROLE_USER'));
        $this->assertTrue($securityContext->isGranted('ROLE_WS_CREATOR'));
        $this->assertFalse($securityContext->isGranted('ROLE_C'));
        $this->assertFalse($securityContext->isGranted('ROLE_E'));
        $this->assertFalse($securityContext->isGranted('ROLE_F'));
    }
    
    public function testHasRoleReliesOnGetRolesMethod()
    {
        $admin = $this->getFixtureReference('user/admin');
        $groupB = $this->getFixtureReference('group/group_b');
        
        $groupB->addUser($admin);
        
        $this->assertTrue($admin->hasRole('ROLE_C'));
        $this->assertTrue($admin->hasRole('ROLE_D'));
    }
}