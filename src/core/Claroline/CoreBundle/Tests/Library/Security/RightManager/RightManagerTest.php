<?php

namespace Claroline\CoreBundle\Library\Security\RightManager;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Tests\Stub\Entity\TestEntity\FirstEntity;
use Claroline\CoreBundle\Library\Security\SecurityException;
use Claroline\CoreBundle\Library\Security\Acl\ClassIdentity;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Role;

class RightManagerTest extends FunctionalTestCase
{
    /** @var RightManagerInterface */
    private $rightManager;

    protected function setUp()
    {
        parent::setUp();
        $this->loadPlatformRolesFixture();
        $this->loadUserData(
            array(
                'user' => 'user',
                'user_2' => 'user',
                'user_3' => 'user',
                'ws_creator' => 'ws_creator'
            )
        );
        $this->rightManager = $this->client->getContainer()->get('claroline.security.right_manager');
    }

    public function testAddingViewRightGrantsViewRight()
    {
        $jane = $this->getUser('user');
        $someEntity = $this->createEntity();
        $isAllowed = $this->rightManager->hasRight($someEntity, $jane, MaskBuilder::MASK_VIEW);
        $this->assertFalse($isAllowed);
        $this->rightManager->addRight($someEntity, $jane, MaskBuilder::MASK_VIEW);
        $isAllowed = $this->rightManager->hasRight($someEntity, $jane, MaskBuilder::MASK_VIEW);
        $this->assertTrue($isAllowed);
    }

    public function testAddingViewAndDeleteRightGrantViewRight()
    {
        $jane = $this->getUser('user');
        $someEntity = $this->createEntity();

        $mb = new MaskBuilder();
        $rightMask = $mb->add(MaskBuilder::MASK_VIEW)
            ->add(MaskBuilder::MASK_DELETE)
            ->get();

        $isAllowed = $this->rightManager->hasRight($someEntity, $jane, MaskBuilder::MASK_VIEW);
        $this->assertFalse($isAllowed);
        $this->rightManager->addRight($someEntity, $jane, $rightMask);
        $isAllowed = $this->rightManager->hasRight($someEntity, $jane, MaskBuilder::MASK_VIEW);
        $this->assertTrue($isAllowed);
    }

    public function testCannotDefineRightOnUnsavedEntity()
    {
        try {
            $jane = $this->getUser('user');
            $someEntity = new FirstEntity();
            $this->rightManager->addRight($someEntity, $jane, MaskBuilder::MASK_VIEW);
            $this->fail('No exception thrown');
        } catch (SecurityException $ex) {
            $this->assertEquals(SecurityException::INVALID_ENTITY_STATE, $ex->getCode());
        }
    }

    public function testCannotDefineRightForUnsavedUser()
    {
        try {
            $jdoe = new User();
            $jdoe->setUsername('jdoe');
            $someEntity = $this->createEntity();
            $this->rightManager->addRight($someEntity, $jdoe, MaskBuilder::MASK_VIEW);
            $this->fail('No exception thrown');
        } catch (SecurityException $ex) {
            $this->assertEquals(SecurityException::INVALID_USER_STATE, $ex->getCode());
        }
    }

    public function testPermissionCanBeGrantedThroughRoleAndUser()
    {
        $this->loadRoleData(array('role_a'));
        $entity = $this->createEntity();
        $jane = $this->getUser('user');
        $roleA = $this->getRole('role_a');
        $jane->addRole($roleA);
        $this->getEntityManager()->flush();

        $this->rightManager->addRight($entity, $roleA, MaskBuilder::MASK_DELETE);
        $this->rightManager->addRight($entity, $jane, MaskBuilder::MASK_VIEW);

        $this->logUser($jane);

        $this->assertTrue($this->getSecurityContext()->isGranted('DELETE', $entity));
        $this->assertTrue($this->getSecurityContext()->isGranted('VIEW', $entity));
    }

    public function testRemovePermissionsForRoleRemovesPermissionsForAllUsersWhoHaveThatRole()
    {
        $this->loadRoleData(array('role_d'));
        $entity = $this->createEntity();
        $roleD = $this->getRole('role_d');
        $jane = $this->getUser('user');
        $henry = $this->getUser('ws_creator');

        $this->rightManager->addRight($entity, $roleD, MaskBuilder::MASK_OPERATOR);
        $this->rightManager->addRight($entity, $jane, MaskBuilder::MASK_VIEW);
        $this->rightManager->removeRight($entity, $roleD, MaskBuilder::MASK_OPERATOR);

        $this->logUser($jane);

        $this->assertFalse($this->getSecurityContext()->isGranted('OPERATOR', $entity));
        $this->assertTrue($this->getSecurityContext()->isGranted('VIEW', $entity));

        $this->logUser($henry);

        $this->assertFalse($this->getSecurityContext()->isGranted('OPERATOR', $entity));
        $this->assertFalse($this->getSecurityContext()->isGranted('VIEW', $entity));
    }

    /**
     * @dataProvider invalidMaskProvider
     */
    public function testPermissionMaskMustBeValid($mask)
    {
        $this->setExpectedException('InvalidArgumentException');
        $jane = $this->getUser('user');
        $someEntity = $this->createEntity();
        $this->rightManager->addRight($someEntity, $jane, $mask);
    }

    public function testRemoveRightsForbidAccess()
    {
        $jane = $this->getUser('user');
        $someEntity = $this->createEntity();
        $rightMask = MaskBuilder::MASK_VIEW;

        $this->rightManager->addRight($someEntity, $jane, $rightMask);
        $isAllowed = $this->rightManager->hasRight($someEntity, $jane, $rightMask);
        $this->assertTrue($isAllowed);
        $isAllowed = $this->rightManager->removeRight($someEntity, $jane, $rightMask);
        $isAllowed = $this->rightManager->hasRight($someEntity, $jane, $rightMask);
        $this->assertFalse($isAllowed);
    }

    public function testRemoveAllRightsForbidAccess()
    {
        $jane = $this->getUser('user');
        $someEntity = $this->createEntity();
        $view = MaskBuilder::MASK_VIEW;
        $edit = MaskBuilder::MASK_EDIT;

        $this->rightManager->addRight($someEntity, $jane, $view);
        $this->rightManager->addRight($someEntity, $jane, $edit);
        $isAllowed = $this->rightManager->hasRight($someEntity, $jane, $view);
        $this->assertTrue($isAllowed);
        $isAllowed = $this->rightManager->hasRight($someEntity, $jane, $edit);
        $this->assertTrue($isAllowed);
        $this->rightManager->removeAllRights($someEntity, $jane);
        $isAllowed = $this->rightManager->hasRight($someEntity, $jane, $view);
        $this->assertFalse($isAllowed);
        $isAllowed = $this->rightManager->hasRight($someEntity, $jane, $edit);
        $this->assertFalse($isAllowed);
    }

    public function testSettingRightRemoveAllOldRights()
    {
        $jane = $this->getUser('user');
        $someEntity = $this->createEntity();

        $mb = new MaskBuilder();
        $viewdel = $mb->add(MaskBuilder::MASK_VIEW)
            ->add(MaskBuilder::MASK_DELETE)
            ->get();
        $edit = MaskBuilder::MASK_EDIT;

        $this->rightManager->addRight($someEntity, $jane, $viewdel);
        $isAllowedToViewDel = $this->rightManager->hasRight($someEntity, $jane, $viewdel);
        $this->assertTrue($isAllowedToViewDel);
        $this->rightManager->setRight($someEntity, $jane, $edit);
        $isAllowedToViewDel = $this->rightManager->hasRight($someEntity, $jane, $viewdel);
        $this->assertFalse($isAllowedToViewDel);
        $isAllowedToEdit = $this->rightManager->hasRight($someEntity, $jane, $edit);
        $this->assertTrue($isAllowedToEdit);
    }

    public function testGettingRightReturnsNullIfNoRightWasSet()
    {
        $jane = $this->getUser('user');
        $someEntity = $this->createEntity();
        $this->assertNull($this->rightManager->getRight($someEntity, $jane));
    }

    public function testGettingRightReturnsRightThatWasSet()
    {
        $jane = $this->getUser('user');
        $someEntity = $this->createEntity();
        $this->rightManager->setRight($someEntity, $jane, MaskBuilder::MASK_EDIT);
        $right = $this->rightManager->getRight($someEntity, $jane);
        $this->assertEquals(MaskBuilder::MASK_EDIT, $right);
    }

    /**
     * @dataProvider maskAndAllowedPermissionsProvider
     */
    public function testRightManagerIscompatibleWithSecurityContext($mask, $allowedPermission)
    {
        $jane = $this->getUser('user');
        $someEntity = $this->createEntity();
        $this->rightManager->addRight($someEntity, $jane, $mask);

        $this->logUser($jane);

        $this->assertTrue($this->getSecurityContext()->isGranted($allowedPermission, $someEntity));
    }

    public function testCannotGetSubjectAboutUnidentifiableEntities()
    {
        try {
            $entity = new \stdClass();
            $this->rightManager->getUsersWithRight($entity, MaskBuilder::MASK_VIEW);
            $this->fail('No exception thrown');
        } catch (SecurityException $ex) {
            $this->assertEquals(SecurityException::INVALID_ENTITY_STATE, $ex->getCode());
        }
    }

    public function testCannotGetSubjectAboutUnsavedEntities()
    {
        try {
            $entity = new FirstEntity();
            $this->rightManager->getUsersWithRight($entity, MaskBuilder::MASK_VIEW);
            $this->fail('No exception thrown');
        } catch (SecurityException $ex) {
            $this->assertEquals(SecurityException::INVALID_ENTITY_STATE, $ex->getCode());
        }
    }

    public function testGetAllowedUsersOnEntityByMaskReturnsExpectedUsers()
    {
        $entity = $this->createEntity();

        $jane = $this->getUser('user');
        $bob = $this->getUser('user_2');
        $bill = $this->getUser('user_3');
        $henry = $this->getUser('ws_creator');

        $this->rightManager->addRight($entity, $jane, MaskBuilder::MASK_OWNER);
        $this->rightManager->addRight($entity, $bob, MaskBuilder::MASK_DELETE);
        $this->rightManager->addRight($entity, $bill, MaskBuilder::MASK_CREATE);
        $this->rightManager->addRight($entity, $henry, MaskBuilder::MASK_DELETE);

        $users = $this->rightManager->getUsersWithRight($entity, MaskBuilder::MASK_DELETE);

        $this->assertEquals(2, count($users));
        $this->assertEquals($henry, $users[0]);
        $this->assertEquals($bob, $users[1]);
    }

    public function testCannotGivePermissionToUnsavedRole()
    {
        try {
            $entity = $this->createEntity();
            $role = new Role();
            $role->setName('ROLE_FOO');
            $this->rightManager->addRight($entity, $role, MaskBuilder::MASK_EDIT);
            $this->fail('No exception thrown');
        } catch (SecurityException $ex) {
            $this->assertEquals(SecurityException::INVALID_ROLE_STATE, $ex->getCode());
        }
    }

    public function testGiveRightsForRoleGrantsPermissionsToAllUsersWhoHaveThatRole()
    {
        $this->loadRoleData(array('role_c'));
        $entity = $this->createEntity();

        $jane = $this->getUser('user');
        $bob = $this->getUser('user_2');
        $bill = $this->getUser('user_3');

        $roleC = $this->getRole('role_c');
        $jane->addRole($roleC);
        $bob->addRole($roleC);
        $this->getEntityManager()->flush();

        $this->rightManager->addRight($entity, $roleC, MaskBuilder::MASK_VIEW);

        $this->logUser($jane);
        $this->assertTrue($this->getSecurityContext()->isGranted('VIEW', $entity));
        $this->logUser($bob);
        $this->assertTrue($this->getSecurityContext()->isGranted('VIEW', $entity));
        $this->logUser($bill);
        $this->assertFalse($this->getSecurityContext()->isGranted('VIEW', $entity));
    }

    public function testGiveClassPermissionsToUserGrantsPermissionsForClassIdentityAndForEachInstanceWithAnAcl()
    {
        $jane = $this->getUser('user');
        $entity = $this->createEntity();
        $this->client->getContainer()
            ->get('security.acl.provider')
            ->createAcl(ObjectIdentity::fromDomainObject($entity));
        $fqcn = get_class($entity);
        $classIdentity = ClassIdentity::fromDomainClass($fqcn);

        $this->rightManager->addRight($fqcn, $jane, MaskBuilder::MASK_EDIT);
        $this->logUser($jane);

        $this->assertTrue($this->getSecurityContext()->isGranted('EDIT', $classIdentity));
        $this->assertTrue($this->getSecurityContext()->isGranted('VIEW', $entity));
        $this->assertTrue($this->getSecurityContext()->isGranted('EDIT', $entity));
        $this->assertFalse($this->getSecurityContext()->isGranted('DELETE', $entity));
    }

    public function testSetClassPermissionsForUserCanUpdatePreviousPermissions()
    {
        $jane = $this->getUser('user');
        $entity = $this->createEntity();
        $fqcn = get_class($entity);
        $classIdentity = ClassIdentity::fromDomainClass($fqcn);

        $this->rightManager->addRight($fqcn, $jane, MaskBuilder::MASK_MASTER);
        $this->rightManager->setRight($fqcn, $jane, MaskBuilder::MASK_DELETE);

        $this->logUser($jane);

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

    private function createEntity($value = "foo")
    {
        $entity = new FirstEntity();
        $entity->setFirstEntityField($value);
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return $entity;
    }
}