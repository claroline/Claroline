<?php

namespace Claroline\CoreBundle\Entity;

use Claroline\CoreBundle\Testing\FixtureTestCase;
use Claroline\CoreBundle\Security\PlatformRoles;

class AbstractRoleSubjectTest extends FixtureTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->loadGroupFixture();
    }
    
    public function testgetOwnedRolesReturnsOnlyLeafChildrenRolesByDefault()
    {
        $admin = $this->getFixtureReference('user/admin');
        $this->assertEquals(1, count($admin->getOwnedRoles()));
        
        $groupA = $this->getFixtureReference('group/group_a');
        $this->assertEquals(1, count($groupA->getOwnedRoles()));
    }
    
    public function testgetOwnedRolesCanIncludeAncestorRolesInTheList()
    {
        $admin = $this->getFixtureReference('user/admin');
        $this->assertEquals(3, count($admin->getOwnedRoles(true)));
        
        $groupB = $this->getFixtureReference('group/group_b');      
        $this->assertEquals(2, count($groupB->getOwnedRoles(true)));
    }
    
    public function testAddAndRemoveNonHierarchicalRole()
    {
        $roleA = $this->getFixtureReference('role/role_a');
        
        $user = $this->getFixtureReference('user/user');
        $user->addRole($roleA);
        $this->assertEquals(2, count($user->getOwnedRoles()));        
        $user->removeRole($roleA);
        $this->assertEquals(1, count($user->getOwnedRoles()));
        
        $groupA = $this->getFixtureReference(('group/group_a'));
        $groupA->removeRole($roleA);
        $this->assertEquals(0, count($groupA->getOwnedRoles()));
    }
    
    public function testAddARoleWhichIsAnAncestorOfAnAlreadyStoredRoleHasNoEffect()
    {
        $admin = $this->getFixtureReference('user/admin');        
        $this->assertEquals(1, count($admin->getOwnedRoles()));      
        $admin->addRole($this->getFixtureReference('role/user'));
        $this->assertEquals(1, count($admin->getOwnedRoles()));
        
        $groupC = $this->getFixtureReference(('group/group_c'));        
        $this->assertEquals(1, count($groupC->getOwnedRoles()));      
        $admin->addRole($this->getFixtureReference('role/role_e'));
        $this->assertEquals(1, count($groupC->getOwnedRoles()));
    }
    
    public function testRemoveAChildrenRoleDoesntAffectParentRole()
    {
        $user = $this->getFixtureReference('user/ws_creator');
        $wsCreatorRole = $this->getFixtureReference('role/ws_creator');       
        $user->removeRole($wsCreatorRole);
        $userRoles = $user->getOwnedRoles(true);
        $this->assertEquals(1, count($userRoles));
        $this->assertEquals(PlatformRoles::USER, $userRoles[0]->getName());
        
        $groupB = $this->getFixtureReference('group/group_b');
        $roleD = $this->getFixtureReference('role/role_d');       
        $groupB->removeRole($roleD);
        $groupRoles = $groupB->getOwnedRoles(true);
        $this->assertEquals(1, count($groupRoles));
        $this->assertEquals('ROLE_C', $groupRoles[0]->getName());
    }
    
    public function testRemoveAnAncestorRoleRemovesDescendantsIfAny()
    {
        $admin = $this->getFixtureReference('user/admin');
        $userRole = $this->getFixtureReference('role/user');
        $admin->removeRole($userRole);
        $this->assertEquals(0, count($admin->getOwnedRoles(true)));
        
        $groupC = $this->getFixtureReference('group/group_c');
        $roleE = $this->getFixtureReference('role/role_e');       
        $groupC->removeRole($roleE);
        $groupRoles = $groupC->getOwnedRoles(true);
        $this->assertEquals(1, count($groupRoles));
        $this->assertEquals('ROLE_C', $groupRoles[0]->getName());
    }
}