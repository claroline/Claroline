<?php

namespace Claroline\SecurityBundle\Manager\RightManager;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Claroline\SecurityBundle\Tests\FunctionalTestCase;
use Claroline\SecurityBundle\Tests\Stub\Entity\TestEntity\FirstEntity;
use Claroline\SecurityBundle\Exception\RightManagerException;

class RestrictedOwnerManagerTest extends FunctionalTestCase
{
    /** @var RestrictedOwnerRightManager */
    private $rightManager;

    public function setUp()
    {
        parent :: setUp();
        $this->rightManager = $this->client->getContainer()->get('claroline.security.restricted_owner_right_manager');
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
            
            $this->rightManager->addRight($fqcn, $role, MaskBuilder::MASK_OWNER);
            $this->fail('No exception thrown');
        }
        catch (RightManagerException $ex)
        {
            $this->assertEquals(RightManagerException::NOT_ALLOWED_OWNER_MASK, $ex->getCode());
        }
    }
}