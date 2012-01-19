<?php

namespace Claroline\CoreBundle\Entity;

use Claroline\CoreBundle\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Tests\stub\Entity\SpecificResource;

class ResourceTest extends TransactionalTestCase
{
    /** @var Doctrine\ORM\EntityManager */
    private $em;
    
    public function setUp()
    {
        parent::setUp();
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    }
    
    public function testANewResourceHasCreationAndModificationDatesWhenFlushed()
    {
        $resource = new Resource();      
        $this->em->persist($resource);
        $this->em->flush();
        $creationTime = new \DateTime(); 
        
        $this->assertInstanceOf('DateTime', $resource->getCreationDate());
        $this->assertInstanceOf('DateTime', $resource->getModificationDate());
        $this->assertEquals($resource->getCreationDate(), $resource->getModificationDate());
        
        $interval = $creationTime->diff($resource->getCreationDate());
        
        $this->assertLessThanOrEqual(1, $interval->s);        
    }
    
    public function testModificationDateIsUpdatedWhenUpdatingAnExistentResource()
    {
        $resource = new SpecificResource();
        $resource->setContent('Test content');      
        $this->em->persist($resource);
        $this->em->flush();
        
        sleep(1);
        
        $resource->setContent('Updated content');
        $this->em->persist($resource);
        $this->em->flush();
        
        $interval = $resource->getCreationDate()->diff($resource->getModificationDate());
        
        $this->assertGreaterThanOrEqual(1, $interval->s);
    }
}