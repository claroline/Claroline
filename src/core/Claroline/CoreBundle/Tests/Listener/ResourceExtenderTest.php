<?php

namespace Claroline\CoreBundle\Listener;

use Doctrine\ORM\Events;
use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Tests\Stub\Entity\SpecificResource1;
use Claroline\CoreBundle\Tests\Stub\Entity\SpecificResource2;
use Claroline\CoreBundle\Entity\Plugin;

class ResourceExtenderTest extends FunctionalTestCase
{
    public function testResourceExtenderIsSubscribed()
    {
        $listeners = $this->em->getEventManager()->getListeners(Events::loadClassMetadata);

        foreach ($listeners as $listener) {
            if ($listener === 'claroline.core_bundle.listener.resource_extender') {
                return;
            }
        }

        $this->fail('The ResourceExtender listener is not attached to the default EntityManager.');
    }

    public function testExtenderAddsPluginResourceTypesToTheDiscriminatorMap()
    {
        $this->registerSpecificResourceTypes();
        $this->createSpecificResources();

        $allRes = $this->em
            ->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->findAll();
        $firstSpecRes = $this->em
            ->getRepository('Claroline\CoreBundle\Tests\Stub\Entity\SpecificResource1')
            ->findAll();
        $secondSpecRes = $this->em
            ->getRepository('Claroline\CoreBundle\Tests\Stub\Entity\SpecificResource2')
            ->findAll();
        $dirRes = $this->em
            ->getRepository('ClarolineCoreBundle:Resource\Directory')
            ->findAll();

        //there is also 1 directory for each workspace, wich mean 5 directories are added with fixtures
        $this->assertEquals(7, count($allRes));
        $this->assertEquals(1, count($firstSpecRes));
        $this->assertEquals(2, count($secondSpecRes));
        $this->assertEquals(4, count($dirRes));
    }

    /**
     * Helper method inserting two plugin resource types. It uses raw sql to avoid
     * loading entity metadata (otherwise the extender listener will be called
     * before the insertion of plugin types and these types won't be added to
     * the discriminator map : this is an issue only if resource types are added
     * and resources are retrieved via the entity manager in the same script
     * invocation, which is unlikely to happen in a production context)
     */
    private function registerSpecificResourceTypes()
    {
        $plugin = new Plugin();
        $plugin->setVendorName('test');
        $plugin->setBundleName('Test');
        $plugin->setHasOptions(false);
        $plugin->setIcon('no_icon');
        $this->em->persist($plugin);
        $this->em->flush();

        $conn = $this->em->getConnection();

        // Insert two specific resource types (see test/Stub/Entity)
        $firstFqcn = $conn->quote('Claroline\CoreBundle\Tests\Stub\Entity\SpecificResource1');
        $secondFqcn = $conn->quote('Claroline\CoreBundle\Tests\Stub\Entity\SpecificResource2');
        $sql = "INSERT INTO claro_resource_type (plugin_id, class, name, is_browsable, is_exportable)"
            . " VALUES ({$plugin->getId()}, {$firstFqcn},"
            . " 'SpecificResource1', false, false),"
            . " ({$plugin->getId()}, {$secondFqcn},"
            . " 'SpecificResource2', false, false)";
        $conn->exec($sql);
    }

    private function createSpecificResources()
    {
        $defaultIcon = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')
            ->findOneBy(array ('type' => 'default'));

        $this->loadPlatformRolesFixture();
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin', 'ws_creator' => 'ws_creator'));

        $firstRes = new SpecificResource1();
        $firstRes->setSomeField('Test');
        $firstRes->setCreator($this->getUser('user'));
        $firstRes->setWorkspace($this->getUser('user')->getPersonalWorkspace());
        $firstRes->setName('name');
        $firstRes->setIcon($defaultIcon);

        $secondRes = new SpecificResource2();
        $secondRes->setSomeField('Test');
        $secondRes->setCreator($this->getUser('ws_creator'));
        $secondRes->setWorkspace($this->getUser('ws_creator')->getPersonalWorkspace());
        $secondRes->setName('name');
        $secondRes->setIcon($defaultIcon);

        $thirdRes = new SpecificResource2();
        $thirdRes->setSomeField('Test');
        $thirdRes->setCreator($this->getUser('admin'));
        $thirdRes->setWorkspace($this->getUser('admin')->getPersonalWorkspace());
        $thirdRes->setName('name');
        $thirdRes->setIcon($defaultIcon);

        $fourthRes = new Directory();
        $fourthRes->setName('Test');
        $fourthRes->setCreator($this->getUser('admin'));
        $fourthRes->setWorkspace($this->getUser('user')->getPersonalWorkspace());
        $fourthRes->setName('name');
        $fourthRes->setIcon($defaultIcon);

        $this->em->persist($firstRes);
        $this->em->persist($secondRes);
        $this->em->persist($thirdRes);
        $this->em->persist($fourthRes);

        $this->em->flush();
    }
}