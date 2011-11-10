<?php

namespace Claroline\SecurityBundle\Service;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Claroline\UserBundle\Entity\User;
use Claroline\SecurityBundle\Entity\Role;
use Claroline\SecurityBundle\Acl\Domain\ClassIdentity;
use Claroline\SecurityBundle\Service\Exception\RightManagerException;
use Claroline\SecurityBundle\Tests\Stub\Entity\TestEntity\FirstEntity;
use Claroline\SecurityBundle\Tests\Stub\Entity\TestEntity\FirstEntityChild;
use Claroline\SecurityBundle\Tests\Stub\Entity\TestEntity\SecondEntity;
use Claroline\SecurityBundle\Tests\Stub\Entity\TestEntity\ThirdEntity;

class RightManagerTest extends WebTestCase
{
    /** @var Claroline\CommonBundle\Service\Testing\TransactionalTestClient */
    private $client;
   
    /** @var Claroline\SecurityBundle\Service\RightManager */
    private $rightManager;
    
    /** @var Claroline\UserBundle\Service\UserManager\Manager */
    private $userManager;
    
    /** @var RoleManager */
    private $roleManager;
    
    /** @var Symfony\Component\Security\Acl\Dbal\AclProvider */
    private $aclProvider;
    
    /** @var Doctrine\ORM\EntityManager */
    private $em;
    
    public function setUp()
    {
        $this->client = self::createClient();
        $this->rightManager = $this->client->getContainer()->get('claroline.security.right_manager');
        $this->userManager = $this->client->getContainer()->get('claroline.user.manager');
        $this->roleManager = $this->client->getContainer()->get('claroline.security.role_manager');
        $this->aclProvider = $this->client->getContainer()->get('security.acl.provider');
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        
        $this->client->beginTransaction();
    }
    
    public function tearDown()
    {
        $this->client->rollback();
    }
    
    public function testSetEntityPermissionsForUserThrowsAnExceptionOnInvalidEntityState()
    {
        $this->assertSetEntityPermissionsForUserThrowsAnException(
            new FirstEntity(), // unmanaged entity
            MaskBuilder::MASK_VIEW, 
            $this->createUser('John', 'Doe', 'jdoe', '123'),
            RightManagerException::INVALID_ENTITY_STATE
        );
    }
    
    /**
     * @dataProvider invalidMaskProvider
     */
    public function testSetEntityPermissionsForUserThrowsAnExceptionOnInvalidPermissionMask($mask)
    {
        $this->assertSetEntityPermissionsForUserThrowsAnException(
            $this->createFirstEntityInstance(), 
            $mask,
            $this->createUser('John', 'Doe', 'jdoe', '123'),
            RightManagerException::INVALID_PERMISSION_MASK
        );
    }
    
    public function testSetEntityPermissionsForUserThrowsAnExceptionOnInvalidUserState()
    {
        $this->assertSetEntityPermissionsForUserThrowsAnException(
            $this->createFirstEntityInstance(),
            MaskBuilder::MASK_VIEW,
            new User(), // unmanaged user
            RightManagerException::INVALID_USER_STATE
        );
    }
    
    public function testSetEntityPermissionsForUserThrowsAnExceptionOnAttemptToSetMultipleEntityOwners()
    {
        try
        {
            $entity = new FirstEntity();
            $entity->setFirstEntityField('First entity field');
            $john = $this->createUser('John', 'Doe', 'jdoe', '123');
            $dave = $this->createUser('Dave', 'Doe', 'ddoe', '123');
            $this->rightManager->createEntityWithOwner($entity, $john);            
            $this->rightManager->setEntityPermissionsForUser($entity, MaskBuilder::MASK_OWNER, $dave);
            $this->fail('No exception thrown');
        }
        catch (RightManagerException $ex)
        {
            $this->assertEquals(RightManagerException::MULTIPLE_OWNERS_ATTEMPT, $ex->getCode());
        }
    }
    
    public function testSetEntityPermissionsForUserDoesntThrowAnExceptionIfCalledSeveralTimesForSameOwner()
    {
        $entity = $this->createFirstEntityInstance();
        $user = $this->createUser('John', 'Doe', 'jdoe', '123');         
        $this->rightManager->setEntityPermissionsForUser($entity, MaskBuilder::MASK_OWNER, $user);
        $this->rightManager->setEntityPermissionsForUser($entity, MaskBuilder::MASK_OWNER, $user);
    }
    
    /**
     * @dataProvider maskAndGreaterPermissionProvider
     */
    public function testSetEntityPermissionsForUserCreatesExpectedAclIfNotExists($mask, $greaterPermission)
    {
        $entity = $this->createFirstEntityInstance();
        $user = $this->createUser('John', 'Doe', 'jdoe', '123');
        $this->rightManager->setEntityPermissionsForUser($entity, $mask, $user);
        $this->logUser($user);
        
        $this->assertTrue($this->getSecurityContext()->isGranted($greaterPermission, $entity));
    }
    
    public function testSetEntityPermissionsForUserCanUpdateExistingAcl()
    {
        $entity = $this->createFirstEntityInstance();
        $user = $this->createUser('John', 'Doe', 'jdoe', '123');
        $this->rightManager->setEntityPermissionsForUser($entity, MaskBuilder::MASK_DELETE, $user);     
        $this->rightManager->setEntityPermissionsForUser($entity, MaskBuilder::MASK_CREATE, $user);  
        $this->logUser($user);
        
        $this->assertFalse($this->getSecurityContext()->isGranted('DELETE', $entity));
        $this->assertTrue($this->getSecurityContext()->isGranted('CREATE', $entity));
    }
    
    public function testCreateEntityWithOwnerThrowsAnExceptionOnInvalidEntityState()
    {
        $this->assertCreateEntityWithOwnerThrowsAnException(
            $this->createFirstEntityInstance(), // managed entity
            $this->createUser('John', 'Doe', 'jdoe', '123'),
            RightManagerException::INVALID_ENTITY_STATE
        );
    }
    
    public function testCreateEntityWithOwnerThrowsAnExceptionOnInvalidUserState()
    {
        $this->assertCreateEntityWithOwnerThrowsAnException(
            new FirstEntity(),
            new User(), // unmanaged user
            RightManagerException::INVALID_USER_STATE
        );
    }
    
    public function testCreateEntityWithOwnerMakesPassedUserTheOwnerOfTheEntity()
    {
        $entity = new FirstEntity();
        $entity->setFirstEntityField('First entity field');
        $user = $this->createUser('John', 'Doe', 'jdoe', '123');
        $this->rightManager->createEntityWithOwner($entity, $user);
        $this->logUser($user);
        
        $this->assertTrue($this->getSecurityContext()->isGranted('OWNER', $entity));
    }
    
    public function testGetAllowedUsersOnEntityByMaskThrowsAnExceptionIfEntityHasNoGetIdMethod()
    {
        try
        {
            $entity = new \stdClass();
            $this->rightManager->getAllowedUsersOnEntityByMask($entity, MaskBuilder::MASK_VIEW);
            $this->fail('No exception thrown');
        }
        catch (RightManagerException $ex)
        {
            $this->assertEquals(RightManagerException::NO_GET_ID_METHOD, $ex->getCode());
        }
    }
    
    public function testGetAllowedUsersOnEntityByMaskReturnsExpectedUsers()
    {
        $entity = new FirstEntity();
        $entity->setFirstEntityField('First entity field');
        
        $john = $this->createUser('John', 'Doe', 'jdoe', '123');
        $dave = $this->createUser('Dave', 'Doe', 'ddoe', '123');
        $lisa = $this->createUser('Lisa', 'Doe', 'ldoe', '123');
        $bart = $this->createUser('Bart', 'Doe', 'bdoe', '123');
        
        $this->rightManager->createEntityWithOwner($entity, $john);
        $this->rightManager->setEntityPermissionsForUser($entity, MaskBuilder::MASK_DELETE, $dave);
        $this->rightManager->setEntityPermissionsForUser($entity, MaskBuilder::MASK_CREATE, $lisa);
        $this->rightManager->setEntityPermissionsForUser($entity, MaskBuilder::MASK_DELETE, $bart);
        
        $users = $this->rightManager->getAllowedUsersOnEntityByMask($entity, MaskBuilder::MASK_DELETE);
        
        $this->assertEquals(2, count($users));
        $this->assertEquals($bart, $users[0]);
        $this->assertEquals($dave, $users[1]);
    }
    
    public function testGetAllowedUsersOnEntityByPermissionThrowsAnExceptionIfEntityHasNoGetIdMethod()
    {
        try
        {
            $entity = new \stdClass();
            $this->rightManager->getAllowedUsersOnEntityByPermission($entity, 'CREATE');
            $this->fail('No exception thrown');
        }
        catch (RightManagerException $ex)
        {
            $this->assertEquals(RightManagerException::NO_GET_ID_METHOD, $ex->getCode());
        }
    }
    
    public function testGetAllowedUsersOnEntityByPermissionReturnsExpectedUsers()
    {
        $entity = new FirstEntity();
        $entity->setFirstEntityField('First entity field');
        
        $john = $this->createUser('John', 'Doe', 'jdoe', '123');
        $dave = $this->createUser('Dave', 'Doe', 'ddoe', '123');
        $lisa = $this->createUser('Lisa', 'Doe', 'ldoe', '123');
        $bart = $this->createUser('Bart', 'Doe', 'bdoe', '123');
        
        $this->rightManager->createEntityWithOwner($entity, $john);
        $this->rightManager->setEntityPermissionsForUser($entity, MaskBuilder::MASK_DELETE, $dave);
        $this->rightManager->setEntityPermissionsForUser($entity, MaskBuilder::MASK_CREATE, $lisa);
        $this->rightManager->setEntityPermissionsForUser($entity, MaskBuilder::MASK_DELETE, $bart);
        
        $users = $this->rightManager->getAllowedUsersOnEntityByPermission($entity, 'DELETE');
        
        $this->assertEquals(3, count($users));
        $this->assertEquals($bart, $users[0]);
        $this->assertEquals($dave, $users[1]);
        $this->assertEquals($john, $users[2]);
    } 

    public function testGetEntityOwnerThrowsAnExceptionIfEntityHasMultipleOwners()
    {
        try
        {
            $entity = new FirstEntity();
            $entity->setFirstEntityField('First entity field');
            $john = $this->createUser('John', 'Doe', 'jdoe', '123');
            $dave = $this->createUser('Dave', 'Doe', 'ddoe', '123');
            $this->rightManager->createEntityWithOwner($entity, $john);
            
            $objectIdentity = ObjectIdentity::fromDomainObject($entity);
            $acl = $this->aclProvider->findAcl($objectIdentity);
            $securityIdentity = UserSecurityIdentity::fromAccount($dave);
            $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
            $this->aclProvider->updateAcl($acl);
            
            $this->rightManager->getEntityOwner($entity);
            $this->fail('No exception thrown');
        }
        catch (RightManagerException $ex)
        {
            $this->assertEquals(RightManagerException::MULTIPLE_OWNERS_ENTITY, $ex->getCode());
        }
    }
    
    public function testGetEntityOwnerReturnsExpectedUser()
    {
        $entity = new FirstEntity();
        $entity->setFirstEntityField('First entity field');
        $john = $this->createUser('John', 'Doe', 'jdoe', '123');
        $bill = $this->createUser('Bill', 'Doe', 'bdoe', '123');
        $this->rightManager->createEntityWithOwner($entity, $john);
        $this->rightManager->setEntityPermissionsForUser($entity, MaskBuilder::MASK_VIEW, $bill);
        
        $user = $this->rightManager->getEntityOwner($entity);
        
        $this->assertEquals($john, $user);
    }
    
    public function testSetEntityOwnerCreatesOwnerRightIfNoPreviousOwner()
    {
        $entity = $this->createFirstEntityInstance();
        $user = $this->createUser('John', 'Doe', 'jdoe', '123');
        
        $this->rightManager->setEntityOwner($entity, $user);
        $this->logUser($user);
        
        $this->assertTrue($this->getSecurityContext()->isGranted('OWNER', $entity));
    }
    
    public function testSetEntityOwnerTransfersOwnershipAndRemovesOldOwnerPermissionsIfNoNewMaskIsPassed()
    {
        $entity = new FirstEntity();
        $entity->setFirstEntityField('First entity field');
        $john = $this->createUser('John', 'Doe', 'jdoe', '123');
        $this->rightManager->createEntityWithOwner($entity, $john);
        $suze = $this->createUser('Suze', 'Doe', 'sdoe', '123');

        $this->rightManager->setEntityOwner($entity, $suze);

        $this->logUser($john);        
        $this->assertFalse($this->getSecurityContext()->isGranted('VIEW', $entity));
        $this->assertFalse($this->getSecurityContext()->isGranted('OWNER', $entity));
        
        $this->logUser($suze);        
        $this->assertTrue($this->getSecurityContext()->isGranted('OWNER', $entity));
    }
    
    /**
     * @dataProvider maskAndGreaterPermissionProvider
     */
    public function testSetEntityOwnerTransfersOwnershipAndAssignsPassedMaskToOldOwner($mask, $permission)
    {
        $entity = new FirstEntity();
        $entity->setFirstEntityField('First entity field');
        $john = $this->createUser('John', 'Doe', 'jdoe', '123');
        $this->rightManager->createEntityWithOwner($entity, $john);
        $suze = $this->createUser('Suze', 'Doe', 'sdoe', '123');

        $this->rightManager->setEntityOwner($entity, $suze, $mask);

        $this->logUser($john);        
        $this->assertFalse($this->getSecurityContext()->isGranted('OWNER', $entity));
        $this->assertTrue($this->getSecurityContext()->isGranted($permission, $entity));
        
        $this->logUser($suze);        
        $this->assertTrue($this->getSecurityContext()->isGranted('OWNER', $entity));
    }
    
    public function testDeleteEntityPermissionsForUserUnsetsUserRightsOnEntity()
    {
        $entity = new FirstEntity();
        $entity->setFirstEntityField('First entity field');
        $user = $this->createUser('John', 'Doe', 'jdoe', '123');
        $this->rightManager->createEntityWithOwner($entity, $user);
        
        $this->rightManager->deleteEntityPermissionsForUser($entity, $user);
        $this->logUser($user);
        
        $this->assertFalse($this->getSecurityContext()->isGranted('VIEW', $entity));
        $this->assertFalse($this->getSecurityContext()->isGranted('OWNER', $entity));
    }
    
    public function testDeleteEntityAndPermissionsThrowsAnExceptionOnInvalidEntityState()
    {     
        try
        {
            $entity = new FirstEntity();
            $entity->setFirstEntityField('First entity field');
            $this->rightManager->deleteEntityAndPermissions($entity);
            $this->fail('No exception thrown');
        }
        catch (RightManagerException $ex)
        {
            $this->assertEquals(RightManagerException::INVALID_ENTITY_STATE, $ex->getCode());
        }
    }
    
    public function testDeleteEntityAndPermissionsRemovesEntityAndAssociatedAcl()
    {
        $this->setExpectedException('Symfony\Component\Security\Acl\Exception\AclNotFoundException');
             
        $entity = new FirstEntity();
        $entity->setFirstEntityField('First entity field');
        $user = $this->createUser('John', 'Doe', 'jdoe', '123');
        $this->rightManager->createEntityWithOwner($entity, $user);
        $entityIdentity = ObjectIdentity::fromDomainObject($entity);
        $entityIdentifier = $entity->getId();
        $this->rightManager->deleteEntityAndPermissions($entity);
        
        $retreivedEntity = $this->em->find(
            'Claroline\SecurityBundle\Tests\Stub\Entity\TestEntity\FirstEntity', 
            $entityIdentifier
        );
        $this->assertNull($retreivedEntity);
        $this->aclProvider->findAcl($entityIdentity);
    }
    
    public function testDeleteEntityAndPermissionsRemovesEntityEvenIfNoAssociatedAcl()
    {
        $this->setExpectedException('Symfony\Component\Security\Acl\Exception\AclNotFoundException');
        
        $entity = $this->createFirstEntityInstance();
        $entityIdentifier = $entity->getId();
        $entityIdentity = ObjectIdentity::fromDomainObject($entity);
        $this->rightManager->deleteEntityAndPermissions($entity);
        
        $retreivedEntity = $this->em->find(
            'Claroline\SecurityBundle\Tests\Stub\Entity\TestEntity\FirstEntity', 
            $entityIdentifier
        );
        $this->assertNull($retreivedEntity);
        $this->aclProvider->findAcl($entityIdentity);
    }
    
    public function testSetEntityPermissionsForRoleThrowsAnExceptionOnInvalidEntityState()
    {
        try
        {
            $entity = new FirstEntity();
            $entity->setFirstEntityField('First entity field');
            $role = $this->createRole('ROLE_TEST');
            $this->rightManager->setEntityPermissionsForRole($entity, MaskBuilder::MASK_DELETE, $role);
            $this->fail('No exception thrown');
        }
        catch (RightManagerException $ex)
        {
            $this->assertEquals(RightManagerException::INVALID_ENTITY_STATE, $ex->getCode());
        }
    }
    
    public function testSetEntityPermissionsForRoleThrowsAnExceptionOnInvalidRoleState()
    {
        try
        {
            $entity = $this->createFirstEntityInstance();
            $role = new Role();
            $this->rightManager->setEntityPermissionsForRole($entity, MaskBuilder::MASK_DELETE, $role);
            $this->fail('No exception thrown');
        }
        catch (RightManagerException $ex)
        {
            $this->assertEquals(RightManagerException::INVALID_ROLE_STATE, $ex->getCode());
        }
    }
    
    public function testSetEntityPermissionsForRoleThrowsAnExceptionOnMaskOwnerArgument()
    {
        try
        {
            $entity = $this->createFirstEntityInstance();
            $role = $this->createRole('ROLE_TEST');
            $this->rightManager->setEntityPermissionsForRole($entity, MaskBuilder::MASK_OWNER, $role);
            $this->fail('No exception thrown');
        }
        catch (RightManagerException $ex)
        {
            $this->assertEquals(RightManagerException::NOT_ALLOWED_OWNER_MASK, $ex->getCode());
        }
    }
    
    public function testSetEntityPermissionsForRoleGrantsPermissionsToAllUsersWhoHaveThatRole()
    {
        $entity = $this->createFirstEntityInstance();
        $role = $this->createRole('ROLE_SPECIAL');
        $john = $this->createUser('John', 'Doe', 'jdoe', '123', $role);
        $suze = $this->createUser('Suze', 'Doe', 'sdoe', '123', $role);
        $bill = $this->createUser('Bill', 'Doe', 'bdoe', '123');
        
        $this->rightManager->setEntityPermissionsForRole($entity, MaskBuilder::MASK_VIEW, $role);
        
        $this->logUser($john);
        $this->assertTrue($this->getSecurityContext()->isGranted('VIEW', $entity));
        $this->logUser($suze);
        $this->assertTrue($this->getSecurityContext()->isGranted('VIEW', $entity));
        $this->logUser($bill);
        $this->assertFalse($this->getSecurityContext()->isGranted('VIEW', $entity));
    }
    
    public function testSetEntityPermissionsForRoleCanUpdateCurrentPermissions()
    {
        $entity = $this->createFirstEntityInstance();
        $role = $this->createRole('ROLE_SPECIAL');
        $user = $this->createUser('John', 'Doe', 'jdoe', '123', $role);
        
        $this->rightManager->setEntityPermissionsForRole($entity, MaskBuilder::MASK_DELETE, $role);  
        $this->logUser($user);
        $this->assertTrue($this->getSecurityContext()->isGranted('DELETE', $entity));
        
        $this->rightManager->setEntityPermissionsForRole($entity, MaskBuilder::MASK_VIEW, $role);  
        $this->logUser($user);
        $this->assertTrue($this->getSecurityContext()->isGranted('VIEW', $entity));
        $this->assertFalse($this->getSecurityContext()->isGranted('DELETE', $entity));
    }
    
    public function testDeleteEntityPermissionsForRoleThrowsAnExceptionOnInvalidEntityState()
    {
        try
        {
            $entity = new FirstEntity();
            $entity->setFirstEntityField('First entity field');
            $role = $this->createRole('ROLE_TEST');
            $this->rightManager->deleteEntityPermissionsForRole($entity, $role);
            $this->fail('No exception thrown');
        }
        catch (RightManagerException $ex)
        {
            $this->assertEquals(RightManagerException::INVALID_ENTITY_STATE, $ex->getCode());
        }
    }
    
    public function testDeleteEntityPermissionsForRoleThrowsAnExceptionOnInvalidRoleState()
    {
        try
        {
            $entity = $this->createFirstEntityInstance();
            $role = new Role();
            $this->rightManager->deleteEntityPermissionsForRole($entity, $role);
            $this->fail('No exception thrown');
        }
        catch (RightManagerException $ex)
        {
            $this->assertEquals(RightManagerException::INVALID_ROLE_STATE, $ex->getCode());
        }
    }
    
    public function testDeleteEntityPermissionsForRoleRemovesPermissionsForAllUsersWhoHaveThatRole()
    {
        $entity = $this->createFirstEntityInstance();
        $role = $this->createRole('ROLE_SPECIAL');
        $john = $this->createUser('John', 'Doe', 'jdoe', '123', $role);
        $suze = $this->createUser('Suze', 'Doe', 'sdoe', '123', $role);
        
        $this->rightManager->setEntityPermissionsForRole($entity, MaskBuilder::MASK_OPERATOR, $role);
        $this->logUser($john);
        $this->assertTrue($this->getSecurityContext()->isGranted('OPERATOR', $entity));
        $this->logUser($suze);
        $this->assertTrue($this->getSecurityContext()->isGranted('OPERATOR', $entity));
        
        $this->rightManager->deleteEntityPermissionsForRole($entity, $role);
        $this->logUser($john);
        $this->assertFalse($this->getSecurityContext()->isGranted('OPERATOR', $entity));
        $this->assertFalse($this->getSecurityContext()->isGranted('VIEW', $entity));
        $this->logUser($suze);
        $this->assertFalse($this->getSecurityContext()->isGranted('OPERATOR', $entity));
        $this->assertFalse($this->getSecurityContext()->isGranted('VIEW', $entity));
    }
    
    /**
     * @dataProvider invalidMaskProvider
     *//*
    public function testSetClassPermissionsForUserThrowsAnExceptionOnInvalidPermissionMask($mask)
    {
        try
        {
            $user = $this->createUser('John', 'Doe', 'jdoe', '123');
            $this->rightManager->setClassPermissionsForUser('Dummy\Class\FQCN', $mask, $user);
            $this->fail('No exception thrown');
        }
        catch (RightManagerException $ex)
        {
            $this->assertEquals(RightManagerException::INVALID_PERMISSION_MASK, $ex->getCode());
        }
    }
    
    public function testSetClassPermissionsForUserThrowsAnExceptionOnInvalidUserState()
    {
        try
        {
            $user = new User();
            $this->rightManager->setClassPermissionsForUser('Dummy\Class\FQCN', MaskBuilder::MASK_EDIT, $user);
            $this->fail('No exception thrown');
        }
        catch (RightManagerException $ex)
        {
            $this->assertEquals(RightManagerException::INVALID_USER_STATE, $ex->getCode());
        }
    }
    
    public function testSetClassPermissionsForUserGrantsPermissionsForClassIdentityAndForEachInstance()
    {
        $fqcn = 'Claroline\SecurityBundle\Tests\Stub\Entity\TestEntity\FirstEntity';
        $user = $this->createUser('John', 'Doe', 'jdoe', '123');
        $classIdentity = ClassIdentity::fromDomainClass($fqcn);
        $firstEntity = $this->createFirstEntityInstance();
        $secondEntity = $this->createFirstEntityInstance();
        $thirdEntity = $this->createFirstEntityInstance();
        
        $this->rightManager->setClassPermissionsForUser($fqcn, MaskBuilder::MASK_DELETE, $user);
        $this->logUser($user);
        
        $this->assertTrue($this->getSecurityContext()->isGranted('DELETE', $classIdentity));        
        $this->assertTrue($this->getSecurityContext()->isGranted('VIEW', $firstEntity)); // fails
        $this->assertTrue($this->getSecurityContext()->isGranted('CREATE', $secondEntity)); // fails      
        $this->assertTrue($this->getSecurityContext()->isGranted('DELETE', $thirdEntity)); // fails
    }
    
    public function testSetClassPermissionsForUserCanUpdatePreviousPermissions()
    {
        $fqcn = 'Claroline\SecurityBundle\Tests\Stub\Entity\TestEntity\FirstEntity';
        $user = $this->createUser('John', 'Doe', 'jdoe', '123');
        $classIdentity = ClassIdentity::fromDomainClass($fqcn);
        
        $this->rightManager->setClassPermissionsForUser($fqcn, MaskBuilder::MASK_MASTER, $user);
        $this->rightManager->setClassPermissionsForUser($fqcn, MaskBuilder::MASK_DELETE, $user);
        $this->logUser($user);
        
        $this->assertFalse($this->getSecurityContext()->isGranted('OWNER', $classIdentity));        
        $this->assertTrue($this->getSecurityContext()->isGranted('DELETE', $classIdentity));
    }
    */
    
    /*********************************************************************************************/
    /************************************** Data providers ***************************************/
    /*********************************************************************************************/
    
    public function invalidMaskProvider()
    {
        return array(
            array('invalid_mask'), // non integer value
            array(123456) // value not in the built-in mask list
        );
    }
    
    public function maskAndGreaterPermissionProvider()
    {
        return array(
            array(MaskBuilder::MASK_VIEW, 'VIEW'),
            array(MaskBuilder::MASK_UNDELETE, 'UNDELETE'),
            array(MaskBuilder::MASK_MASTER, 'MASTER'),
        );
    }
    
    /*********************************************************************************************/
    /************************************** Helper methods ***************************************/
    /*********************************************************************************************/
    
    private function assertSetEntityPermissionsForUserThrowsAnException($entity, $mask, $user, $exceptionCode)
    {
        try
        {
            $this->rightManager->setEntityPermissionsForUser($entity, $mask, $user);
            $this->fail('No exception thrown');
        }
        catch (RightManagerException $ex)
        {
            $this->assertEquals($exceptionCode, $ex->getCode());
        }
    }
    
    private function assertCreateEntityWithOwnerThrowsAnException($entity, $user, $exceptionCode)
    {
        try
        {
            $this->rightManager->createEntityWithOwner($entity, $user);
            $this->fail('No exception thrown');
        }
        catch (RightManagerException $ex)
        {
            $this->assertEquals($exceptionCode, $ex->getCode());
        }
    }
    
    private function createFirstEntityInstance()
    {
        $entity = new FirstEntity();
        $entity->setFirstEntityField('First entity field'); 
        
        $this->em->persist($entity);
        $this->em->flush();
        
        return $entity;
    }
    
    private function createRole($roleName)
    {
        $role = new Role();
        $role->setName($roleName);
        $this->roleManager->create($role);
        
        return $role;
    }
    
    private function createUser($firstName, $lastName, $username, $password, $additionalRole = false)
    {
        $user = new User();
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setUserName($username);
        $user->setPlainPassword($password);
        $additionalRole ? $user->addRole($additionalRole) : null;
        
        $this->userManager->create($user);
        
        return $user;
    }
    
    private function logUser(User $user)
    {
        $this->client->request('GET', '/logout');
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->filter('input[id=_submit]')->form();
        $form['_username'] = $user->getUsername();
        $form['_password'] = $user->getPlainPassword();
        $this->client->submit($form);
    }
    
    private function getSecurityContext()
    {
        return $this->client->getContainer()->get('security.context');
    }       
}