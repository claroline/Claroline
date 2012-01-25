<?php

namespace Claroline\CoreBundle\Testing;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Claroline\CoreBundle\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\ORM\LoadUserData;
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
    
    protected function loadUserFixture()
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $refRepo = new ReferenceRepository($em);
        $userFixture = new LoadUserData();
        $userFixture->setContainer($this->client->getContainer());
        $userFixture->setReferenceRepository($refRepo);
        
        return $userFixture->load($em);
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
        
        return $this->client->submit($form);
    }
    
    /** @return \Symfony\Component\Security\Core\SecurityContextInterface */
    protected function getSecurityContext()
    {
        return $this->client->getContainer()->get('security.context');
    }
}