<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Entity\Extension;
use Claroline\CoreBundle\Entity\Resource\ResourceType;

class ResourceTypeRepositoryTest extends TransactionalTestCase
{
    /** @var Doctrine\ORM\EntityManager */
    private $em;
    
    /** @var ResourceTypeRepository */
    private $repo;
    
    protected function setUp()
    {
        parent::setUp();
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->repo = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType');
    }
    
    public function testFindPluginResourceTypes()
    {
        $typeCount = count($this->repo->findPluginResourceTypes());
        
        $this->createResourceTypes();
        
        $newTypeCount = count($types = $this->repo->findPluginResourceTypes());
        
        // Some plugin types may be already registered, so we only test
        // that the repository can retrieve the ones we have added
        $this->assertEquals($newTypeCount, $typeCount + 2);
        $lastType = array_pop($types);
        $this->assertEquals('Type y', $lastType->getType());
        $lastType = array_pop($types);
        $this->assertEquals('Type x', $lastType->getType());
    }
    
    public function testFindPluginResourceTypeFqcns()
    {
        $typeCount = count($this->repo->findPluginResourceTypeFqcns());
        
        $this->createResourceTypes();
        
        $newTypeCount = count($types = $this->repo->findPluginResourceTypeFqcns());
        
        // see previous test
        $this->assertEquals($newTypeCount, $typeCount + 2);
        $lastType = array_pop($types);
        $this->assertEquals('Type y', $lastType['type']);
        $lastType = array_pop($types);
        $this->assertEquals('Type x', $lastType['type']);
    }
    
    private function createResourceTypes()
    {
        $plugin = new Extension();
        $plugin->setBundleFQCN('Test\Test');
        $plugin->setVendorName('Test');
        $plugin->setBundleName('Test');
        $plugin->setType('test');
        $plugin->setNameTranslationKey('test');
        $plugin->setDescriptionTranslationKey('test');
        
        $firstType = new ResourceType();
        $firstType->setType('Type x');
        $firstType->setBundle('CoreBundle');
        $firstType->setService('x.manager');
        $firstType->setController('X');
        $firstType->setListable(true);
        $firstType->setNavigable(false);
        $firstType->setPlugin($plugin);
        
        $secondType = new ResourceType();
        $secondType->setType('Type y');
        $secondType->setBundle('CoreBundle');
        $secondType->setService('y.manager');
        $secondType->setController('Y');
        $secondType->setListable(true);
        $secondType->setNavigable(false);
        $secondType->setPlugin($plugin);
        
        $thirdType = new ResourceType();
        $thirdType->setType('Type z');
        $thirdType->setBundle('CoreBundle');
        $thirdType->setService('z.manager');
        $thirdType->setController('Z');
        $thirdType->setListable(true);
        $thirdType->setNavigable(false);
        
        $this->em->persist($plugin);
        $this->em->persist($firstType);
        $this->em->persist($secondType);
        $this->em->persist($thirdType);
        $this->em->flush();
    }
}