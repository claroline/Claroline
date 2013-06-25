<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class ResourceTypeRepositoryTest extends RepositoryTestCase
{
    /** @var \Claroline\CoreBundle\Repository\ResourceTypeRepository */
    private static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::$em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType');
        self::loadPlatformRoleData();
        self::loadUserData(array('john' => 'user'));

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

        self::$em->persist($plugin);
        self::$em->persist($firstType);
        self::$em->persist($secondType);
        self::$em->persist($thirdType);
        self::$em->flush();
    }

    public function testFindPluginResourceTypes()
    {
        //should be 2 when the issue #34 is resolved
        $this->assertEquals(8, count(self::$repo->findPluginResourceTypes()));
    }

    public function testFindPluginResourceNameFqcns()
    {
        $rt = self::$repo->findPluginResourceNameFqcns();

        //should be 2 when the issue #34 is resolved
        $this->assertEquals(8, count($rt));
        $lastType = array_pop($rt);
        $this->assertEquals('YYY/YYY/YYY', $lastType['class']);
        $lastType = array_pop($rt);
        $this->assertEquals('XXX/XXX/XXX', $lastType['class']);
    }

    public function testFindByIds()
    {
        $resourceTypes = self::$repo->findAll();
        $this->assertGreaterThan(1, count($resourceTypes));
        $retrievedTypes = self::$repo->findByIds(
            array($resourceTypes[0]->getId(), $resourceTypes[1]->getId())
        );
        $this->assertEquals(2, count($retrievedTypes));
        $this->assertEquals($resourceTypes[0], $retrievedTypes[0]);
        $this->assertEquals($resourceTypes[1], $retrievedTypes[1]);
    }
}