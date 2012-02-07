<?php

namespace Claroline\CoreBundle\Tests\Security\RightManager;

use Claroline\CoreBundle\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Tests\Stub\Entity\TestEntity\FirstEntity;

abstract class RightManagerTestCase extends FunctionalTestCase
{
    /** @var UserManager */
    private $userManager;
    
    /** @var RoleManager */
    private $roleManager;
    
    /** @var Doctrine\ORM\EntityManager */
    private $em;
    
    protected function setUp()
    {
        parent::setUp();
        $this->userManager = $this->client->getContainer()->get('claroline.user.manager');
        $this->roleManager = $this->client->getContainer()->get('claroline.security.role_manager');
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    }
    
    protected function createUser
    (
        $firstName = "John", 
        $lastName = "Doe", 
        $username = "jdoe", 
        $password = "topsecret",
        $role = null
    )
    {
        $user = new User();
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setUserName($username);
        $user->setPlainPassword($password);
        $role && $user->addRole($role);
        
        $this->userManager->create($user);
        
        return $user;
    }
    
    protected function createRole($roleName = 'ROLE_FOO')
    {
        $role = new Role();
        $role->setName($roleName);
        $this->roleManager->create($role);
        
        return $role;
    }
    
    protected function createEntity($value = "foo")
    {
        $entity = new FirstEntity();
        $entity->setFirstEntityField($value);
        $this->em->persist($entity);
        $this->em->flush();
        return $entity;
    }
}