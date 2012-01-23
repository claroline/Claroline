<?php

namespace Claroline\CoreBundle\Security\RightManager;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Claroline\CoreBundle\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Tests\Stub\Entity\TestEntity\FirstEntity;
use Claroline\CoreBundle\Exception\RightManagerException;
use Claroline\CoreBundle\Security\Acl\ClassIdentity;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Role;

class RightManagerTest extends FunctionalTestCase
{
    /** @var RightManagerInterface */
    private $rightManager;
      
    public function setUp()
    {
        parent::setUp();
        $this->rightManager = $this->client->getContainer()->get('claroline.security.right_manager');
    }
    
    public function testAddingViewRightGrantsViewRight()
    {
        $jdoe = $this->createUser();
        $someEntity = $this->createEntity();
        $isAllowed = $this->rightManager->hasRight($someEntity, $jdoe, MaskBuilder::MASK_VIEW);
        $this->assertFalse($isAllowed);
        $this->rightManager->addRight($someEntity, $jdoe, MaskBuilder::MASK_VIEW);
        $isAllowed = $this->rightManager->hasRight($someEntity, $jdoe, MaskBuilder::MASK_VIEW);
        $this->assertTrue($isAllowed);      
    }
    
    public function testAddingViewAndDeleteRightGrantViewRight()
    {
        $jdoe = $this->createUser();
        $someEntity = $this->createEntity();
        
        $mb = new MaskBuilder();
        $mb->add(MaskBuilder::MASK_VIEW);
        $mb->add(MaskBuilder::MASK_DELETE);
        $rightMask = $mb->get();
        
        $isAllowed = $this->rightManager->hasRight($someEntity, $jdoe, MaskBuilder::MASK_VIEW);
        $this->assertFalse($isAllowed);
        $this->rightManager->addRight($someEntity, $jdoe, $rightMask);
        $isAllowed = $this->rightManager->hasRight($someEntity, $jdoe, MaskBuilder::MASK_VIEW);
        $this->assertTrue($isAllowed);   
    }
    
    public function testCannotDefineRightOnUnsavedEntity()
    {
        try 
        {
            $jdoe = $this->createUser();
            $someEntity = new FirstEntity();
            $this->rightManager->addRight($someEntity, $jdoe, MaskBuilder::MASK_VIEW);
            $this->fail('No exception thrown');
        }
        catch (RightManagerException $ex)
        {
            $this->assertEquals(RightManagerException::INVALID_ENTITY_STATE, $ex->getCode());
        }
    }
    
    public function testCannotDefineRightForUnsavedUser()
    {
        try
        {
            $jdoe = new User();
            $jdoe->setUsername('jdoe');
            $someEntity = $this->createEntity();
            $this->rightManager->addRight($someEntity, $jdoe, MaskBuilder::MASK_VIEW);
            $this->fail('No exception thrown');
        }
        catch (RightManagerException $ex)
        {
            $this->assertEquals(RightManagerException::INVALID_USER_STATE, $ex->getCode());
        }
    }
    
    public function testPermissionCanBeGrantedThroughRoleAndUser()
    {
        $entity = $this->createEntity();
        $role = $this->createRole();
        $user = $this->createUser('John', 'Doe', 'jdoe', '123', $role);
           
        $this->rightManager->addRight($entity, $role, MaskBuilder::MASK_DELETE);
        $this->rightManager->addRight($entity, $user, MaskBuilder::MASK_VIEW);
           
        $this->logUser($user);
        
        $this->assertTrue($this->getSecurityContext()->isGranted('DELETE', $entity));
        $this->assertTrue($this->getSecurityContext()->isGranted('VIEW', $entity));
    }
    
    public function testRemovePermissionsForRoleRemovesPermissionsForAllUsersWhoHaveThatRole()
    {
        $entity = $this->createEntity();
        $role = $this->createRole();
        $john = $this->createUser('John', 'Doe', 'jdoe', '123', $role);
        $suze = $this->createUser('Suze', 'Doe', 'sdoe', '123', $role);
        
        $this->rightManager->addRight($entity, $role, MaskBuilder::MASK_OPERATOR);
        $this->rightManager->addRight($entity, $john, MaskBuilder::MASK_VIEW);
        $this->rightManager->removeRight($entity, $role, MaskBuilder::MASK_OPERATOR);
        
        $this->logUser($john);
        
        $this->assertFalse($this->getSecurityContext()->isGranted('OPERATOR', $entity));
        $this->assertTrue($this->getSecurityContext()->isGranted('VIEW', $entity));
        
        $this->logUser($suze);
        
        $this->assertFalse($this->getSecurityContext()->isGranted('OPERATOR', $entity));
        $this->assertFalse($this->getSecurityContext()->isGranted('VIEW', $entity));
    }
          
    /**
     * @dataProvider invalidMaskProvider
     */
    public function testPermissionMaskMustBeValid($mask)
    {
        $this->setExpectedException('InvalidArgumentException');
        $jdoe = $this->createUser();
        $someEntity = $this->createEntity();
        $this->rightManager->addRight($someEntity, $jdoe, $mask);       
    } 
    
    public function testRemoveRightsForbidAccess()
    {
        $jdoe = $this->createUser();
        $someEntity = $this->createEntity();        
        $rightMask = MaskBuilder::MASK_VIEW;
        
        $this->rightManager->addRight($someEntity, $jdoe, $rightMask);
        $isAllowed = $this->rightManager->hasRight($someEntity, $jdoe, $rightMask);
        $this->assertTrue($isAllowed);
        $isAllowed = $this->rightManager->removeRight($someEntity, $jdoe, $rightMask);
        $isAllowed = $this->rightManager->hasRight($someEntity, $jdoe, $rightMask);
        $this->assertFalse($isAllowed);
    }
    
    public function testRemoveAllRightsForbidAccess()
    {
        $jdoe = $this->createUser();
        $someEntity = $this->createEntity();        
        $view = MaskBuilder::MASK_VIEW;
        $edit = MaskBuilder::MASK_EDIT;
        
        $this->rightManager->addRight($someEntity, $jdoe, $view);
        $this->rightManager->addRight($someEntity, $jdoe, $edit);
        $isAllowed = $this->rightManager->hasRight($someEntity, $jdoe, $view);
        $this->assertTrue($isAllowed);
        $isAllowed = $this->rightManager->hasRight($someEntity, $jdoe, $edit);
        $this->assertTrue($isAllowed);
        $this->rightManager->removeAllRights($someEntity, $jdoe);
        $isAllowed = $this->rightManager->hasRight($someEntity, $jdoe, $view);
        $this->assertFalse($isAllowed);
        $isAllowed = $this->rightManager->hasRight($someEntity, $jdoe, $edit);
        $this->assertFalse($isAllowed);
    }
    
    public function testSettingRightRemoveAllOldRights()
    {
        $jdoe = $this->createUser();
        $someEntity = $this->createEntity();
        
        $mb = new MaskBuilder();
        $mb->add(MaskBuilder::MASK_VIEW);
        $mb->add(MaskBuilder::MASK_DELETE);
        $viewdel = $mb->get();
        
        $edit = MaskBuilder::MASK_EDIT;
        
        $this->rightManager->addRight($someEntity, $jdoe, $viewdel);        
        $isAllowedToViewDel = $this->rightManager->hasRight($someEntity, $jdoe, $viewdel);
        $this->assertTrue($isAllowedToViewDel);
        $this->rightManager->setRight($someEntity, $jdoe, $edit);
        $isAllowedToViewDel = $this->rightManager->hasRight($someEntity, $jdoe, $viewdel);
        $this->assertFalse($isAllowedToViewDel);
        $isAllowedToEdit = $this->rightManager->hasRight($someEntity, $jdoe, $edit);
        $this->assertTrue($isAllowedToEdit);
    }
    
    public function testGettingRightReturnsNullIfNoRightWasSet()
    {
        $jdoe = $this->createUser();
        $someEntity = $this->createEntity();
        $this->assertNull($this->rightManager->getRight($someEntity, $jdoe));
    }
    
    public function testGettingRightReturnsRightThatWasSet()
    {
        $jdoe = $this->createUser();
        $someEntity = $this->createEntity();
        $this->rightManager->setRight($someEntity, $jdoe, MaskBuilder::MASK_EDIT);
        $right = $this->rightManager->getRight($someEntity, $jdoe);
        $this->assertEquals(MaskBuilder::MASK_EDIT, $right);
    }
    
    /**
     * @dataProvider maskAndAllowedPermissionsProvider
     */
    public function testRightManagerIscompatibleWithSecurityContext($mask, $allowedPermission)
    {
        $jdoe = $this->createUser();
        $someEntity = $this->createEntity();       
        $this->rightManager->addRight($someEntity, $jdoe, $mask);
        
        $this->logUser($jdoe);
        
        $this->assertTrue($this->getSecurityContext()->isGranted($allowedPermission, $someEntity));
    }   
 
    public function testCannotGetSubjectAboutUnidentifiableEntities()
    {
        try
        {
            $entity = new \stdClass();
            $this->rightManager->getUsersWithRight($entity, MaskBuilder::MASK_VIEW);
            $this->fail('No exception thrown');
        }
        catch (RightManagerException $ex)
        {
            $this->assertEquals(RightManagerException::INVALID_ENTITY_STATE, $ex->getCode());
        }
    }

    public function testCannotGetSubjectAboutUnsavedEntities()
    {
        try
        {
            $entity = new FirstEntity();
            $this->rightManager->getUsersWithRight($entity, MaskBuilder::MASK_VIEW);
            $this->fail('No exception thrown');
        }
        catch (RightManagerException $ex)
        {
            $this->assertEquals(RightManagerException::INVALID_ENTITY_STATE, $ex->getCode());
        }
    }
    
    public function testGetAllowedUsersOnEntityByMaskReturnsExpectedUsers()
    {
        $entity = $this->createEntity();
        
        $john = $this->createUser('John', 'Doe', 'jdoe', '123');
        $dave = $this->createUser('Dave', 'Doe', 'ddoe', '123');
        $lisa = $this->createUser('Lisa', 'Doe', 'ldoe', '123');
        $bart = $this->createUser('Bart', 'Doe', 'bdoe', '123');
        
        $this->rightManager->addRight($entity, $john, MaskBuilder::MASK_OWNER);
        $this->rightManager->addRight($entity, $dave, MaskBuilder::MASK_DELETE);
        $this->rightManager->addRight($entity, $lisa, MaskBuilder::MASK_CREATE);
        $this->rightManager->addRight($entity, $bart, MaskBuilder::MASK_DELETE);
        
        $users = $this->rightManager->getUsersWithRight($entity, MaskBuilder::MASK_DELETE);
        
        $this->assertEquals(2, count($users));
        $this->assertEquals($bart, $users[0]);
        $this->assertEquals($dave, $users[1]);
    }
    
    public function testCannotGivePermissionToUnsavedRole()
    {
        try
        {
            $entity = $this->createEntity();
            $role = new Role();
            $role->setName('ROLE_FOO');
            $this->rightManager->addRight($entity, $role, MaskBuilder::MASK_EDIT);
            $this->fail('No exception thrown');
        }
        catch (RightManagerException $ex)
        {
            $this->assertEquals(RightManagerException::INVALID_ROLE_STATE, $ex->getCode());
        }
    }
    
    public function testGiveRightsForRoleGrantsPermissionsToAllUsersWhoHaveThatRole()
    {
        $entity = $this->createEntity();
        $role = $this->createRole();
        
        $john = $this->createUser('John', 'Doe', 'jdoe', '123', $role);
        $suze = $this->createUser('Suze', 'Doe', 'sdoe', '123', $role);
        $bill = $this->createUser('Bill', 'Doe', 'bdoe', '123');
        
        
        $this->rightManager->addRight($entity, $role, MaskBuilder::MASK_VIEW);
        
        $this->logUser($john);
        $this->assertTrue($this->getSecurityContext()->isGranted('VIEW', $entity));
        $this->logUser($suze);
        $this->assertTrue($this->getSecurityContext()->isGranted('VIEW', $entity));
        $this->logUser($bill);
        $this->assertFalse($this->getSecurityContext()->isGranted('VIEW', $entity));
    }
    
    public function testGiveClassPermissionsToUserGrantsPermissionsForClassIdentityAndForEachInstance()
    {       
        $user = $this->createUser();
        $entity = $this->createEntity();
        $fqcn = get_class($entity);
        $classIdentity = ClassIdentity::fromDomainClass($fqcn);
        
        $this->rightManager->addRight($fqcn, $user, MaskBuilder::MASK_EDIT);
        $this->logUser($user);
        
        $this->assertTrue($this->getSecurityContext()->isGranted('EDIT', $classIdentity));        
        $this->assertTrue($this->getSecurityContext()->isGranted('VIEW', $entity)); 
        $this->assertTrue($this->getSecurityContext()->isGranted('EDIT', $entity));       
        $this->assertFalse($this->getSecurityContext()->isGranted('DELETE', $entity));
    }
    
    public function testSetClassPermissionsForUserCanUpdatePreviousPermissions()
    {
        $user = $this->createUser();
        $entity = $this->createEntity();
        $fqcn = get_class($entity);
        $classIdentity = ClassIdentity::fromDomainClass($fqcn);
        
        $this->rightManager->addRight($fqcn, $user, MaskBuilder::MASK_MASTER);
        $this->rightManager->setRight($fqcn, $user, MaskBuilder::MASK_DELETE);
        
        $this->logUser($user);
        
        $this->assertFalse($this->getSecurityContext()->isGranted('OWNER', $classIdentity));        
        $this->assertTrue($this->getSecurityContext()->isGranted('DELETE', $classIdentity));
    }
    
    public function invalidMaskProvider()
    {
        return array(
            array('SOME_RIGHT'), 
            array(new \stdClass()),
            array((float) 12.0)            
        );
    }
    
    public function maskAndAllowedPermissionsProvider()
    {
        return array(
            array(MaskBuilder::MASK_VIEW, 'VIEW'),
            array(MaskBuilder::MASK_UNDELETE, 'UNDELETE'),
            array(MaskBuilder::MASK_MASTER, 'MASTER'),
            array(MaskBuilder::MASK_MASTER, 'VIEW'),
            array(MaskBuilder::MASK_MASTER, 'EDIT'),
        );
    }
}