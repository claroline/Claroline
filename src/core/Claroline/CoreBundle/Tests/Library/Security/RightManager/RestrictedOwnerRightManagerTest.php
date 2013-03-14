<?php

namespace Claroline\CoreBundle\Library\Security\RightManager;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Claroline\CoreBundle\Library\Testing\FixtureTestCase;
use Claroline\CoreBundle\Tests\Stub\Entity\TestEntity\FirstEntity;
use Claroline\CoreBundle\Library\Security\SecurityException;

class RestrictedOwnerManagerTest extends FixtureTestCase
{
    /** @var RestrictedOwnerRightManager */
    private $rightManager;

    protected function setUp()
    {
        parent::setUp();
        $this->loadPlatformRoleData();
        $this->loadUserData(array('user' => 'user', 'ws_creator' => 'ws_creator', 'admin' => 'admin'));
        $this->rightManager = $this->client->getContainer()
            ->get('claroline.security.restricted_owner_right_manager');
    }

    public function testThereCanOnlyBeOneOwnerMaximum()
    {
        try {
            $jane = $this->getUser('user');
            $henry = $this->getUser('ws_creator');
            $entity = $this->createEntity();
            $mask = MaskBuilder::MASK_OWNER;
            $this->rightManager->addRight($entity, $jane, $mask);
            $this->rightManager->addRight($entity, $henry, $mask);
            $this->fail('No exception thrown');
        } catch (SecurityException $ex) {
            $this->assertEquals(SecurityException::MULTIPLE_OWNERS_ATTEMPT, $ex->getCode());
        }
    }

    public function testSameOwnerCanBeDefinedAgainAndAgain()
    {
        $jane = $this->getUser('user');
        $entity = $this->createEntity();
        $mask = MaskBuilder::MASK_OWNER;
        $this->rightManager->addRight($entity, $jane, $mask);
        $this->rightManager->addRight($entity, $jane, $mask);
    }

    public function testSettingAnOwnerRemovesTheOwningRightFromOlderOwner()
    {
        $jane = $this->getUser('user');
        $henry = $this->getUser('ws_creator');
        $entity = $this->createEntity();
        $mb = new MaskBuilder();
        $mask = $mb->add(MaskBuilder::MASK_OWNER)
            ->add(MaskBuilder::MASK_UNDELETE)
            ->get();
        $this->rightManager->addRight($entity, $jane, $mask);
        $this->rightManager->setOwner($entity, $henry);

        $this->assertTrue($this->rightManager->hasRight($entity, $jane, MaskBuilder::MASK_UNDELETE));
        $this->assertFalse($this->rightManager->hasRight($entity, $jane, MaskBuilder::MASK_OWNER));
        $this->assertTrue($this->rightManager->hasRight($entity, $henry, MaskBuilder::MASK_OWNER));
    }

    public function testARoleCannotBeOwner()
    {
        try {
            $entity = $this->createEntity();
            $role = $this->getRole('user');
            $this->rightManager->addRight($entity, $role, MaskBuilder::MASK_OWNER);
            $this->fail('No exception thrown');
        } catch (SecurityException $ex) {
            $this->assertEquals(SecurityException::NOT_ALLOWED_OWNER_MASK, $ex->getCode());
        }
    }

    public function testUsersCannotOwnAClass()
    {
        try {
            $role = $this->getRole('user');
            $entity = new FirstEntity();
            $fqcn = get_class($entity);

            $this->rightManager->addRight($fqcn, $role, MaskBuilder::MASK_OWNER);
            $this->fail('No exception thrown');
        } catch (SecurityException $ex) {
            $this->assertEquals(SecurityException::NOT_ALLOWED_OWNER_MASK, $ex->getCode());
        }
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