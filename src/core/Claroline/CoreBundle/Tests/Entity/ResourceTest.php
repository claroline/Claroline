<?php

namespace Claroline\CoreBundle\Entity;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;
use Claroline\CoreBundle\Entity\Resource\Directory;

class ResourceTest extends FixtureTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
    }
    
    public function testANewResourceHasCreationAndModificationDatesWhenFlushed()
    {
        $resource = new Directory();
        $resource->setName('Test');
        $resource->setUser($this->getFixtureReference('user/admin'));
        $this->getEntityManager()->persist($resource);
        $this->getEntityManager()->flush();
        $creationTime = new \DateTime(); 
        
        $this->assertInstanceOf('DateTime', $resource->getCreationDate());
        $this->assertInstanceOf('DateTime', $resource->getModificationDate());
        $this->assertEquals($resource->getCreationDate(), $resource->getModificationDate());
        
        $interval = $creationTime->diff($resource->getCreationDate());
        
        $this->assertLessThanOrEqual(1, $interval->s);        
    }
    
    public function testModificationDateIsUpdatedWhenUpdatingAnExistentResource()
    {
        $resource = new Directory();
        $resource->setName('Test');
        $resource->setUser($this->getFixtureReference('user/admin'));
        $this->getEntityManager()->persist($resource);
        $this->getEntityManager()->flush();
        
        sleep(1);
        
        $resource->setName('Updated name');
        $this->getEntityManager()->persist($resource);
        $this->getEntityManager()->flush();
        
        $interval = $resource->getCreationDate()->diff($resource->getModificationDate());
        
        $this->assertGreaterThanOrEqual(1, $interval->s);
    }
}