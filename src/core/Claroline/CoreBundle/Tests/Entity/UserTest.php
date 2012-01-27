<?php

namespace Claroline\CoreBundle\Entity;

use Claroline\CoreBundle\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Entity\Role;

class UserTest extends FunctionalTestCase
{
    /** @var Claroline\CoreBundle\Security\RoleManager */
    private $roleManager;
    
    /** @var Doctrine\ORM\EntityManager */
    private $entityManager;
    
    public function setUp()
    {
        parent::setUp();
        $container = $this->client->getContainer();
        $this->roleManager = $container->get('claroline.security.role_manager');
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->loadUserFixture();
    }
    
    /**
     * @dataProvider userRoleProvider
     */
    public function testHasRoleSearchesInStoredRolesAndInTheirAncestors($userReference, array $roles)
    {
        $user = $this->getFixtureReference($userReference);
        
        foreach ($roles as $role)
        {
            $this->assertTrue($user->hasRole($role));
        }
    }
    
    public function testGetRoleCollectionReturnsOnlyLeafChildrenRolesByDefault()
    {
        $user = $this->getFixtureReference('user/admin');
        $this->assertEquals(1, count($user->getRoleCollection()));
    }
    
    public function testGetRoleCollectionCanIncludeAncestorRolesInTheList()
    {
        $user = $this->getFixtureReference('user/admin');
        $roles = $user->getRoleCollection(true);
        $this->assertEquals(3, count($roles));
    }
    
    public function testGetRolesAlwaysIncludesAncestorRolesInTheList()
    {
        $admin = $this->getFixtureReference('user/admin');
        $roles = $admin->getRoles();
        $expectedRoles = array('ROLE_ADMIN', 'ROLE_WS_CREATOR', 'ROLE_USER');
        $this->assertEquals($expectedRoles, $roles);
    }
    
    public function testAddThenRemoveNonHierarchicalRole()
    {
        $user = $this->getFixtureReference('user/user');
        
        $role = new Role();
        $role->setName('ROLE_TEST');
        $this->roleManager->create($role);
        
        $user->addRole($role);
        $this->entityManager->flush();
        
        $this->logUser($user);
        $this->assertTrue($this->getSecurityContext()->isGranted('ROLE_TEST'));
        
        $user->removeRole($role);
        $this->entityManager->flush();
        
        $this->logUser($user);
        $this->assertFalse($this->getSecurityContext()->isGranted('ROLE_TEST'));
    }
    
    public function testAddARoleWhichIsAnAncestorOfAnAlreadyStoredRoleHasNoEffect()
    {
        $admin = $this->getFixtureReference('user/admin');
        $userRole = $this->getFixtureReference('role/user');
        
        $this->assertEquals(1, count($admin->getRoleCollection()));
        
        $admin->addRole($userRole);
        
        $this->assertEquals(1, count($admin->getRoleCollection()));
    }
    
    public function testRemoveAChildrenRoleDoesntAffectParentRole()
    {
        $user = $this->getFixtureReference('user/ws_creator');
        $wsCreatorRole = $this->getFixtureReference('role/ws_creator');
        
        $user->removeRole($wsCreatorRole);
        $this->entityManager->flush();
        
        $this->logUser($user);
        $this->assertFalse($this->getSecurityContext()->isGranted('ROLE_WS_CREATOR'));
        $this->assertTrue($this->getSecurityContext()->isGranted('ROLE_USER'));
    }
    
    public function testRemoveAnAncestorRoleRemovesDescendantsIfAny()
    {
        $admin = $this->getFixtureReference('user/admin');
        $userRole = $this->getFixtureReference('role/user');
        $wsCreatorRole = $this->getFixtureReference('role/user');
        
        $specialRole = new Role();
        $specialRole->setName('ROLE_TEST');
        $specialRole->setParent($wsCreatorRole);
        $this->roleManager->create($specialRole);
        $admin->addRole($specialRole);
        $this->assertTrue($admin->hasRole('ROLE_TEST'));
        
        $admin->removeRole($userRole);
        $this->entityManager->flush();
        
        $this->logUser($admin);
        $this->assertFalse($this->getSecurityContext()->isGranted('ROLE_ADMIN'));
        $this->assertFalse($this->getSecurityContext()->isGranted('ROLE_TEST'));
        $this->assertFalse($this->getSecurityContext()->isGranted('ROLE_WS_CREATOR'));
        $this->assertFalse($this->getSecurityContext()->isGranted('ROLE_USER'));
    }
    
    public function userRoleProvider()
    {
        return array(
            array('user/user', array('ROLE_USER')),
            array('user/ws_creator', array('ROLE_USER', 'ROLE_WS_CREATOR')),
            array('user/admin', array('ROLE_USER', 'ROLE_WS_CREATOR', 'ROLE_ADMIN'))
        );
    }
}