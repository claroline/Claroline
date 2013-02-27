<?php

namespace Claroline\CoreBundle\Listener;

use Doctrine\ORM\Events;
use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Tests\Stub\Entity\SpecificResource1;
use Claroline\CoreBundle\Tests\Stub\Entity\SpecificResource2;

class ResourceExtenderTest extends FunctionalTestCase
{
    public function testResourceExtenderIsSubscribed()
    {
        $listeners = $this->em->getEventManager()->getListeners(Events::loadClassMetadata);

        foreach ($listeners as $listener) {
            if ($listener instanceof ResourceExtender) {
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
        $conn = $this->em->getConnection();

        // Insert a fake extension plugin
        $sql = "INSERT INTO claro_plugin ( vendor_name, short_name)"
            . " VALUES ( 'test', 'Test')";
        $conn->exec($sql);
        $pluginId = $conn->lastInsertId();

        // Insert two specific resource types (see test/Stub/Entity)
        $sql = "INSERT INTO claro_resource_type (plugin_id, class, name, is_visible, is_browsable)"
            . " VALUES ({$pluginId}, 'Claroline\\\CoreBundle\\\Tests\\\Stub\\\Entity\\\SpecificResource1',"
            . " 'SpecificResource1', true, false),"
            . " ({$pluginId}, 'Claroline\\\CoreBundle\\\Tests\\\Stub\\\Entity\\\SpecificResource2',"
            . " 'SpecificResource2', true, false)";
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
        $firstRes->setCreator($this->getFixtureReference('user/user'));
        $firstRes->setWorkspace($this->getFixtureReference('user/user')->getPersonalWorkspace());
        $firstRes->setName('name');
        $firstRes->setIcon($defaultIcon);
        $firstRes->setOwnerRights(
            array(
                'sharable' => true,
                'editable' => true,
                'exportable' => true,
                'deletable' => true,
                'copiable' => true
            )
        );

        $secondRes = new SpecificResource2();
        $secondRes->setSomeField('Test');
        $secondRes->setCreator($this->getFixtureReference('user/ws_creator'));
        $secondRes->setWorkspace($this->getFixtureReference('user/ws_creator')->getPersonalWorkspace());
        $secondRes->setName('name');
        $secondRes->setIcon($defaultIcon);
        $secondRes->setOwnerRights(
            array(
                'sharable' => true,
                'editable' => true,
                'exportable' => true,
                'deletable' => true,
                'copiable' => true
            )
        );

        $thirdRes = new SpecificResource2();
        $thirdRes->setSomeField('Test');
        $thirdRes->setCreator($this->getFixtureReference('user/admin'));
        $thirdRes->setWorkspace($this->getFixtureReference('user/admin')->getPersonalWorkspace());
        $thirdRes->setName('name');
        $thirdRes->setIcon($defaultIcon);
        $thirdRes->setOwnerRights(
            array(
                'sharable' => true,
                'editable' => true,
                'exportable' => true,
                'deletable' => true,
                'copiable' => true
            )
        );

        $fourthRes = new Directory();
        $fourthRes->setName('Test');
        $fourthRes->setCreator($this->getFixtureReference('user/admin'));
        $fourthRes->setWorkspace($this->getFixtureReference('user/user')->getPersonalWorkspace());
        $fourthRes->setName('name');
        $fourthRes->setIcon($defaultIcon);
        $fourthRes->setOwnerRights(
            array(
                'sharable' => true,
                'editable' => true,
                'exportable' => true,
                'deletable' => true,
                'copiable' => true
            )
        );

        $this->em->persist($firstRes);
        $this->em->persist($secondRes);
        $this->em->persist($thirdRes);
        $this->em->persist($fourthRes);

        $this->em->flush();
    }
}