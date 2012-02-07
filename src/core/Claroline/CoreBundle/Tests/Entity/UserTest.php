<?php

namespace Claroline\CoreBundle\Entity;

use Claroline\CoreBundle\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Security\PlatformRoles;

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
        $this->logUser($wsCreator);
        $securityContext = $this->getSecurityContext();
        
        $this->assertTrue($securityContext->isGranted(PlatformRoles::USER));
        $this->assertTrue($securityContext->isGranted(PlatformRoles::WS_CREATOR));
        
        $groupC = $this->getFixtureReference('group/group_c');     
        $groupC->addUser($wsCreator);
        $this->getEntityManager()->flush();
        $securityContext->getToken()->setUser($wsCreator); // refresh session info
                        
        $this->assertTrue($securityContext->isGranted(PlatformRoles::USER));
        $this->assertTrue($securityContext->isGranted(PlatformRoles::WS_CREATOR));
        $this->assertTrue($securityContext->isGranted('ROLE_C'));
        $this->assertTrue($securityContext->isGranted('ROLE_E'));
        $this->assertTrue($securityContext->isGranted('ROLE_F'));
        
        $groupC->removeUser($wsCreator);
        $this->getEntityManager()->flush();
        $securityContext->getToken()->setUser($wsCreator); // refresh session info
        
        $this->assertTrue($securityContext->isGranted(PlatformRoles::USER));
        $this->assertTrue($securityContext->isGranted(PlatformRoles::WS_CREATOR));
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