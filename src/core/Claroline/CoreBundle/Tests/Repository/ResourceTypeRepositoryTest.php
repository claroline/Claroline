<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Entity\Plugin;
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
        $this->repo = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType');
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
        $this->assertEquals('Type y', $lastType->getName());
        $lastType = array_pop($types);
        $this->assertEquals('Type x', $lastType->getName());
    }

    public function testFindPluginResourceNameFqcns()
    {
        $typeCount = count($this->repo->findPluginResourceNameFqcns());

        $this->createResourceTypes();

        $newTypeCount = count($types = $this->repo->findPluginResourceNameFqcns());

        // see previous test
        $this->assertEquals($newTypeCount, $typeCount + 2);
        $lastType = array_pop($types);
        $this->assertEquals('YYY/YYY/YYY', $lastType['class']);
        $lastType = array_pop($types);
        $this->assertEquals('XXX/XXX/XXX', $lastType['class']);
    }

    public function testFindByIds()
    {
        $resourceTypes = $this->repo->findAll();
        $this->assertGreaterThan(1, count($resourceTypes));
        $retrievedTypes = $this->repo->findByIds(
            array($resourceTypes[0]->getId(), $resourceTypes[1]->getId())
        );
        $this->assertEquals(2, count($retrievedTypes));
        $this->assertEquals($resourceTypes[0], $retrievedTypes[0]);
        $this->assertEquals($resourceTypes[1], $retrievedTypes[1]);
    }

    private function createResourceTypes()
    {
        $plugin = new Plugin();
        $plugin->setVendorName('Test');
        $plugin->setBundleName('Test');
        $plugin->setHasOptions(true);
        $plugin->setIcon('fakeicon');

        $firstType = new ResourceType();
        $firstType->setName('Type x');
        $firstType->setClass('XXX/XXX/XXX');
        $firstType->setBrowsable(false);
        $firstType->setPlugin($plugin);
        $firstType->setExportable(false);

        $secondType = new ResourceType();
        $secondType->setName('Type y');
        $secondType->setClass('YYY/YYY/YYY');
        $secondType->setBrowsable(false);
        $secondType->setPlugin($plugin);
        $secondType->setExportable(false);

        $thirdType = new ResourceType();
        $thirdType->setName('Type z');
        $thirdType->setBrowsable(false);
        $thirdType->setExportable(false);

        $this->em->persist($plugin);
        $this->em->persist($firstType);
        $this->em->persist($secondType);
        $this->em->persist($thirdType);
        $this->em->flush();
    }
}