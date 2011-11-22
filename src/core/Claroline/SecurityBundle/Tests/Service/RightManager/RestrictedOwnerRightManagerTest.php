<?php

namespace Claroline\SecurityBundle\Tests\Service\RightManager;



use Claroline\CommonBundle\Library\Testing\TransactionalTestCase;
use Claroline\SecurityBundle\Service\RightManager\RightManager;
use Claroline\SecurityBundle\Tests\Stub\Entity\TestEntity\FirstEntity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Claroline\UserBundle\Entity\User;
use Claroline\SecurityBundle\Service\Exception\RightManagerException;
use Claroline\SecurityBundle\Entity\Role;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Claroline\SecurityBundle\Service\RoleManager;
use Claroline\SecurityBundle\Service\RightManager\RestrictedOwnerRightManager;

class RestrictedOwnerManagerTest extends TransactionalTestCase
{
    /** @var RestrictedOwnerRightManager */
    private $rightManager;
    
    /** @var Claroline\UserBundle\Service\UserManager\Manager */
    private $userManager;
    
    /** @var RoleManager */
    private $roleManager;
    
    /** @var \Doctrine\ORM\EntityManager */
    private $em;
    
     
    public function setUp()
    {
        parent :: setUp();
        $this->rightManager = $this->client->getContainer()->get('claroline.security.restricted_owner_right_manager');
        $this->userManager = $this->client->getContainer()->get('claroline.user.manager');
        $this->roleManager = $this->client->getContainer()->get('claroline.security.role_manager');
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    }
    
   public function testThereCanOnlyBeOneOwnerMaximum()
    {
        try
        {
            $john = $this->createUser('John', 'Doe', 'jdoe', '123');
            $dave = $this->createUser('Dave', 'Doe', 'ddoe', '123');
            $entity = $this->createEntity();
            $mask = MaskBuilder::MASK_OWNER;
            $this->rightManager->addRight($entity, $john, $mask);
            $this->rightManager->addRight($entity, $dave, $mask);
            $this->fail('No exception thrown');
        }
        catch (RightManagerException $ex)
        {
            $this->assertEquals(RightManagerException::MULTIPLE_OWNERS_ATTEMPT, $ex->getCode());
        }
    }
    
    public function testSameOwnerCanBeDefinedAgainAndAgain()
    {
        $john = $this->createUser();
        $entity = $this->createEntity();
        $mask = MaskBuilder::MASK_OWNER;
        $this->rightManager->addRight($entity, $john, $mask);
        $this->rightManager->addRight($entity, $john, $mask);
    }
    
    public function testSettingAnOwnerRemovesTheOwningRightFromOlderOwner()
    {
        $john = $this->createUser('John', 'Doe', 'jdoe', '123');
        $dave = $this->createUser('Dave', 'Doe', 'ddoe', '123');
        $entity = $this->createEntity();
        $mb = new MaskBuilder();
        $mb ->add(MaskBuilder::MASK_OWNER)
            ->add(MaskBuilder::MASK_UNDELETE);
        $mask = $mb->get();
        $this->rightManager->addRight($entity, $john, $mask);
        $this->rightManager->setOwner($entity, $dave);
        
        $this->assertTrue($this->rightManager->hasRight($entity, $john, MaskBuilder::MASK_UNDELETE));
        $this->assertFalse($this->rightManager->hasRight($entity, $john, MaskBuilder::MASK_OWNER));
        $this->assertTrue($this->rightManager->hasRight($entity, $dave, MaskBuilder::MASK_OWNER));
        
    }
    
    public function testARoleCannotBeOwner()
    {
        try
        {
            $entity = $this->createEntity();
            $role = $this->createRole();
            $this->rightManager->addRight($entity, $role, MaskBuilder::MASK_OWNER);
            $this->fail('No exception thrown');
        }
        catch (RightManagerException $ex)
        {
            $this->assertEquals(RightManagerException::NOT_ALLOWED_OWNER_MASK, $ex->getCode());
        }
    }
    
    public function testUSersCannotOwnAClass()
    {
        try
        {
            $role = $this->createRole();
            $entity = new FirstEntity();
            $fqcn = get_class($entity);
            
            $this->rightManager->addRight($fqcn , $role, MaskBuilder::MASK_OWNER);
            $this->fail('No exception thrown');
        }
        catch (RightManagerException $ex)
        {
            $this->assertEquals(RightManagerException::NOT_ALLOWED_OWNER_MASK, $ex->getCode());
        }
    }
    
    
    
        private function createUser
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
    
    private function createRole($roleName = 'ROLE_FOO')
    {
        $role = new Role();
        $role->setName($roleName);
        $this->roleManager->create($role);
        
        return $role;
    }
    
    private function createEntity($value = "foo")
    {
        $entity = new FirstEntity();
        $entity->setFirstEntityField($value);
        $this->em->persist($entity);
        $this->em->flush();
        return $entity;
    }
    
    
}