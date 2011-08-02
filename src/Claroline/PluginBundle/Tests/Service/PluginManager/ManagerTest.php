<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Parser;
use \vfsStream;

class ManagerTest extends WebTestCase
{
    private $client;
    private $config;
    private $manager;

    public function setUp()
    {
        $this->buildPluginFiles();
        $validator = new Validator(vfsStream::url('virtual/plugin'), new Parser());
        $this->config = new ConfigurationHandler(vfsStream::url('virtual/config/namespaces'),
                                                 vfsStream::url('virtual/config/bundles'),
                                                 vfsStream::url('virtual/config/routing.yml'),
                                                 new Yaml());
        $this->client = self::createClient();
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->manager = new Manager($validator, $this->config, $em);
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

        $this->assertEquals(array('VendorX'), $this->config->getRegisteredNamespaces());
        $this->assertEquals(array($plugin), $this->config->getRegisteredBundles());
        $expectedRouting = array(
            'VendorXFirstPluginBundle_0' => array(
                'resource' => '@VendorXFirstPluginBundle/Resources/config/routing.yml'
                )
            );
        $this->assertEquals($expectedRouting, $this->config->getRoutingResources());
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

        $this->assertEquals(array('VendorX'), $this->config->getRegisteredNamespaces());
        $this->assertEquals(array($plugin), $this->config->getRegisteredBundles());
        $expectedRouting = array(
            'VendorXSecondPluginBundle_0' => array(
                'resource' => '@VendorXSecondPluginBundle/Resources/config/routing.yml'
                )
            );
        $this->assertEquals($expectedRouting, $this->config->getRoutingResources());
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

        $this->assertEquals(array('VendorX', 'VendorY'), $this->config->getRegisteredNamespaces());
        $this->assertEquals(array($plugin1, $plugin2, $plugin3), $this->config->getRegisteredBundles());
        $expectedRouting = array(
            'VendorXFirstPluginBundle_0' => array(
                'resource' => '@VendorXFirstPluginBundle/Resources/config/routing.yml'
                ),
            'VendorXSecondPluginBundle_0' => array(
                'resource' => '@VendorXSecondPluginBundle/Resources/config/routing.yml'
                )
            );
        $this->assertEquals($expectedRouting, $this->config->getRoutingResources());
        $this->assertEquals(true, $this->manager->isInstalled($plugin1));
        $this->assertEquals(true, $this->manager->isInstalled($plugin2));
        $this->assertEquals(true, $this->manager->isInstalled($plugin3));
    }

    public function testRemovePluginRemovesItFromConfig()
    {
        $plugin = 'VendorX\FirstPluginBundle\VendorXFirstPluginBundle';
        $this->manager->install($plugin);
        $this->manager->remove($plugin);

        $this->assertEquals(array(), $this->config->getRegisteredNamespaces());
        $this->assertEquals(array(), $this->config->getRegisteredBundles());
        $this->assertEquals(array(), $this->config->getRoutingResources());
        $this->assertEquals(false, $this->manager->isInstalled($plugin));
    }

    public function testRemovePluginPreservesSharedVendorNamespaces()
    {
        $this->manager->install('VendorX\FirstPluginBundle\VendorXFirstPluginBundle');
        $this->manager->install('VendorX\SecondPluginBundle\VendorXSecondPluginBundle');
        $this->manager->remove('VendorX\FirstPluginBundle\VendorXFirstPluginBundle');

        $this->assertEquals(array('VendorX'), $this->config->getRegisteredNamespaces());
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

    private function buildPluginFiles()
    {
        vfsStream::setUp('virtual');

        $firstPlugin = array(
            'FirstPluginBundle' => array(
                'VendorXFirstPluginBundle.php' => '<?php namespace VendorX\FirstPluginBundle;
                                                   class VendorXFirstPluginBundle extends
                                                   \Claroline\PluginBundle\AbstractType\ClarolinePlugin
                                                   {}',
                'Resources' => array(
                    'config' => array(
                        'routing.yml' => ''
                        )
                    )
                )
            );
        $secondPlugin = array(
            'SecondPluginBundle' => array(
                'VendorXSecondPluginBundle.php' => '<?php namespace VendorX\SecondPluginBundle;
                                                    class VendorXSecondPluginBundle extends
                                                    \Claroline\PluginBundle\AbstractType\ClarolinePlugin
                                                    {}',
                'Resources' => array(
                    'config' => array(
                        'routing.yml' => ''
                        )
                    )
                )
            );
        $thirdPlugin = array(
            'ThirdPluginBundle' => array(
                'VendorYThirdPluginBundle.php' => '<?php namespace VendorY\ThirdPluginBundle;
                                                   class VendorYThirdPluginBundle extends
                                                   \Claroline\PluginBundle\AbstractType\ClarolinePlugin
                                                   {}'
                )
            );
        $fourthPlugin = array(
            'FourthPluginBundle' => array(
                'VendorYFourthPluginBundle.php' => '<?php namespace VendorY\FourthPluginBundle;
                                                   class VendorYFourthPluginBundle extends
                                                   \Claroline\PluginBundle\AbstractType\ClarolinePlugin
                                                   { public function getRoutingResourcesPaths()
                                                   {return "wrong/path/file.foo";}}'
                )
            );

        $structure = array(
            'plugin' => array(
                'VendorX' => array_merge($firstPlugin, $secondPlugin),
                'VendorY' => array_merge($thirdPlugin, $fourthPlugin)
                ),
            'config' => array(
                'namespaces' => '',
                'bundles' => '',
                'routing.yml' => ''
                )
            );

        vfsStream::create($structure, 'virtual');
    }
}