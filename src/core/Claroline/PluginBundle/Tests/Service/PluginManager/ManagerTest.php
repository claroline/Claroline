<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\PluginBundle\Tests\Fixtures\VirtualPlugins;
use \vfsStream;

class ManagerTest extends WebTestCase
{
    private $client;
    private $manager;
    private $fileHandler;
    private $em;

    public function setUp()
    {
        $this->client = self::createClient();
        
        $fixtures = new VirtualPlugins();
        $fixtures->buildVirtualPluginFiles();

        $this->manager = $this->client->getContainer()->get('claroline.plugin.manager');
        $this->manager->setParameters(vfsStream::url('virtual/plugin'),
                                      vfsStream::url('virtual/config/namespaces'),
                                      vfsStream::url('virtual/config/bundles'),
                                      vfsStream::url('virtual/config/routing.yml'));
        $this->fileHandler = $this->manager->getFileHandler();
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $this->client->beginTransaction();
    }

    public function  tearDown()
    {
        $this->client->rollback();
    }

    public function testInstallAValidPluginRegistersItInConfig()
    {
        $plugin = 'VendorX\FirstPluginBundle\VendorXFirstPluginBundle';
        $this->manager->install($plugin);

        $this->assertEquals(array('VendorX'), $this->fileHandler->getRegisteredNamespaces());
        $this->assertEquals(array($plugin), $this->fileHandler->getRegisteredBundles());
        $expectedRouting = array(
            'VendorXFirstPluginBundle_0' => array(
                'resource' => '@VendorXFirstPluginBundle/Resources/config/routing.yml'
                )
            );
        $this->assertEquals($expectedRouting, $this->fileHandler->getRoutingResources());
        $this->assertEquals(true, $this->manager->isInstalled($plugin));
    }

    public function testInstallAnInvalidPluginThrowsAValidationException()
    {
        $this->setExpectedException('Claroline\PluginBundle\Service\PluginManager\Exception\ValidationException');
        $plugin = 'VendorY\FourthPluginBundle\VendorYFourthPluginBundle';
        $this->manager->install($plugin);
        $this->assertEquals(false, $this->manager->isInstalled($plugin));
    }

    public function testInstallAnAlreadyInstalledPluginThrowsAConfigurationException()
    {
        $this->setExpectedException('Claroline\PluginBundle\Service\PluginManager\Exception\ConfigurationException');

        $plugin = 'VendorX\SecondPluginBundle\VendorXSecondPluginBundle';
        $this->manager->install($plugin);
        $this->manager->install($plugin);

        $this->assertEquals(array('VendorX'), $this->fileHandler->getRegisteredNamespaces());
        $this->assertEquals(array($plugin), $this->fileHandler->getRegisteredBundles());
        $expectedRouting = array(
            'VendorXSecondPluginBundle_0' => array(
                'resource' => '@VendorXSecondPluginBundle/Resources/config/routing.yml'
                )
            );
        $this->assertEquals($expectedRouting, $this->fileHandler->getRoutingResources());
        $this->assertEquals(false, $this->manager->isInstalled($plugin));
    }

    public function testInstallSeveralValidPlugins()
    {
        $plugin1 = 'VendorX\FirstPluginBundle\VendorXFirstPluginBundle';
        $plugin2 = 'VendorX\SecondPluginBundle\VendorXSecondPluginBundle';
        $plugin3 = 'VendorY\ThirdPluginBundle\VendorYThirdPluginBundle';
        
        $this->manager->install($plugin1);
        $this->manager->install($plugin2);
        $this->manager->install($plugin3);

        $this->assertEquals(array('VendorX', 'VendorY'), $this->fileHandler->getRegisteredNamespaces());
        $this->assertEquals(array($plugin1, $plugin2, $plugin3), $this->fileHandler->getRegisteredBundles());
        $expectedRouting = array(
            'VendorXFirstPluginBundle_0' => array(
                'resource' => '@VendorXFirstPluginBundle/Resources/config/routing.yml'
                ),
            'VendorXSecondPluginBundle_0' => array(
                'resource' => '@VendorXSecondPluginBundle/Resources/config/routing.yml'
                )
            );
        $this->assertEquals($expectedRouting, $this->fileHandler->getRoutingResources());
        $this->assertEquals(true, $this->manager->isInstalled($plugin1));
        $this->assertEquals(true, $this->manager->isInstalled($plugin2));
        $this->assertEquals(true, $this->manager->isInstalled($plugin3));
    }

    public function testRemovePluginRemovesItFromConfig()
    {
        $plugin = 'VendorX\FirstPluginBundle\VendorXFirstPluginBundle';
        $this->manager->install($plugin);
        $this->manager->remove($plugin);

        $this->assertEquals(array(), $this->fileHandler->getRegisteredNamespaces());
        $this->assertEquals(array(), $this->fileHandler->getRegisteredBundles());
        $this->assertEquals(array(), $this->fileHandler->getRoutingResources());
        $this->assertEquals(false, $this->manager->isInstalled($plugin));
    }

    public function testRemovePluginPreservesSharedVendorNamespaces()
    {
        $this->manager->install('VendorX\FirstPluginBundle\VendorXFirstPluginBundle');
        $this->manager->install('VendorX\SecondPluginBundle\VendorXSecondPluginBundle');
        $this->manager->remove('VendorX\FirstPluginBundle\VendorXFirstPluginBundle');

        $this->assertEquals(array('VendorX'), $this->fileHandler->getRegisteredNamespaces());
    }

    public function testRemoveInexistentPluginThrowsAConfigurationException()
    {
        $this->setExpectedException('Claroline\PluginBundle\Service\PluginManager\Exception\ConfigurationException');
        $this->manager->remove('VendorY\InexistentPluginBundle\VendorYInexistentPluginBundle');
    }
    
    public function testIsInstalledReturnsCorrectValue()
    {
        $plugin = 'VendorX\FirstPluginBundle\VendorXFirstPluginBundle';
        $this->assertEquals(false, $this->manager->isInstalled($plugin));

        $this->manager->install($plugin);
        $this->assertEquals(true, $this->manager->isInstalled($plugin));
    }
}