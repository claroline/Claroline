<?php

namespace Claroline\CoreBundle\Testing;

use Claroline\CoreBundle\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Tests\Stub\Entity\TestEntity\FirstEntity;

abstract class FunctionalTestCase extends TransactionalTestCase
{
    /** @var UserManager */
    private $userManager;
    
    /** @var RoleManager */
    private $roleManager;
    
    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    public function setUp()
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
    
    protected function logUser(User $user)
    {
        $this->client->request('GET', '/logout');
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->filter('#login_form input[type=submit]')->form();
        $form['_username'] = $user->getUsername();
        $form['_password'] = $user->getPlainPassword();
        $this->client->submit($form);
    }
    
    /** @return \Symfony\Component\Security\Core\SecurityContextInterface */
    protected function getSecurityContext()
    {
        return $this->client->getContainer()->get('security.context');
    }
}