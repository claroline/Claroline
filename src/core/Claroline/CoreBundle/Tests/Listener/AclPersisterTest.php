<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\Security\Acl\Dbal\AclProvider;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Tests\Stub\Entity\TestEntity\FirstEntity;

class AclPersisterTest extends TransactionalTestCase
{
    /** @var AclProvider */
    private $aclProvider;
    
    /** @var EntityManager */
    private $em;   
    
    public function setUp()
    {
        parent::setUp();
        $this->aclProvider = $this->client->getContainer()->get('security.acl.provider');
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    }
         
    public function testPersistingAnEntityCreatesAnAcl()
    {        
        $entity = new FirstEntity();
        $entity->setFirstEntityField('foo');
        $this->em->persist($entity);
        $this->em->flush();
        
        $entityIdentity = ObjectIdentity::fromDomainObject($entity);
        
        $acl = $this->aclProvider->findAcl($entityIdentity);
        $this->assertNotNull($acl);
    }
    
    public function testDeletingAnEntityRemovesAnAcl()
    {        
        $this->setExpectedException('Symfony\Component\Security\Acl\Exception\AclNotFoundException');
        
        $entity = new FirstEntity();        
        $entity->setFirstEntityField('foo');
        $this->em->persist($entity);
        $this->em->flush();
        $entityIdentity = ObjectIdentity::fromDomainObject($entity);
        $this->em->remove($entity);
        $this->em->flush();       
        
        $this->aclProvider->findAcl($entityIdentity);
    }
}