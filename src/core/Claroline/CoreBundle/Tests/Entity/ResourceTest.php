<?php

namespace Claroline\CoreBundle\Entity;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;
use Claroline\CoreBundle\Tests\Stub\Entity\SpecificResource;
use Claroline\CoreBundle\Tests\DataFixtures\LoadResourceTypeData;

class ResourceTest extends FixtureTestCase
{
    /** @var Doctrine\ORM\EntityManager */
    private $em;
    
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->loadFixture(new LoadResourceTypeData());
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    }
    
    public function testANewResourceHasCreationAndModificationDatesWhenFlushed()
    {
        $resource = new Resource(); 
        $resource->setUser($this->getFixtureReference('user/admin'));
        $resource->setResourceType($this->getFixtureReference('resource_type/file'));
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
        $resource->setUser($this->getFixtureReference('user/admin'));
        $resource->setResourceType($this->getFixtureReference('resource_type/file'));
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