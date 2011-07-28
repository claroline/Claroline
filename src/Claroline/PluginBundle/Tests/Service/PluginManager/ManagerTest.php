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
    }

    public function testInstallAValidPluginRegistersItInConfigFiles()
    {
        $this->manager->install('VendorX\FirstPluginBundle\VendorXFirstPluginBundle');

        $this->assertEquals(array('VendorX'), $this->config->getRegisteredNamespaces());
        $this->assertEquals(array('VendorX\FirstPluginBundle\VendorXFirstPluginBundle'), 
                            $this->config->getRegisteredBundles());
        $expectedRouting = array(
            'VendorXFirstPluginBundle_0' => array(
                'resource' => '@VendorXFirstPluginBundle/Resources/config/routing.yml'
                )
            );
        $this->assertEquals($expectedRouting, $this->config->getRoutingResources());
    }

    public function testInstallAnInvalidPluginThrowsAValidationException()
    {
        $this->setExpectedException('Claroline\PluginBundle\Service\PluginManager\Exception\ValidationException');
        $this->manager->install('VendorY\FourthPluginBundle\VendorYFourthPluginBundle');
    }

    public function testInstallValidPluginCalledSeveralTimesDoesntDuplicateEntryInConfig()
    {
        $this->manager->install('VendorX\SecondPluginBundle\VendorXSecondPluginBundle');
        $this->manager->install('VendorX\SecondPluginBundle\VendorXSecondPluginBundle');
        $this->manager->install('VendorX\SecondPluginBundle\VendorXSecondPluginBundle');

        $this->assertEquals(array('VendorX'), $this->config->getRegisteredNamespaces());
        $this->assertEquals(array('VendorX\SecondPluginBundle\VendorXSecondPluginBundle'), 
                            $this->config->getRegisteredBundles());
        $expectedRouting = array(
            'VendorXSecondPluginBundle_0' => array(
                'resource' => '@VendorXSecondPluginBundle/Resources/config/routing.yml'
                )
            );
        $this->assertEquals($expectedRouting, $this->config->getRoutingResources());
    }

    public function testInstallSeveralValidPlugins()
    {
        $this->manager->install('VendorX\FirstPluginBundle\VendorXFirstPluginBundle');
        $this->manager->install('VendorX\SecondPluginBundle\VendorXSecondPluginBundle');
        $this->manager->install('VendorY\ThirdPluginBundle\VendorYThirdPluginBundle');

        $this->assertEquals(array('VendorX', 'VendorY'), $this->config->getRegisteredNamespaces());
        $this->assertEquals(array('VendorX\FirstPluginBundle\VendorXFirstPluginBundle',
                                  'VendorX\SecondPluginBundle\VendorXSecondPluginBundle',
                                  'VendorY\ThirdPluginBundle\VendorYThirdPluginBundle'),
                            $this->config->getRegisteredBundles());
        $expectedRouting = array(
            'VendorXFirstPluginBundle_0' => array(
                'resource' => '@VendorXFirstPluginBundle/Resources/config/routing.yml'
                ),
            'VendorXSecondPluginBundle_0' => array(
                'resource' => '@VendorXSecondPluginBundle/Resources/config/routing.yml'
                )
            );
        $this->assertEquals($expectedRouting, $this->config->getRoutingResources());
    }

    public function testRemovePluginRemovesItFromConfigFiles()
    {
        $this->manager->install('VendorX\FirstPluginBundle\VendorXFirstPluginBundle');
        $this->manager->remove('VendorX\FirstPluginBundle\VendorXFirstPluginBundle');

        $this->assertEquals(array(), $this->config->getRegisteredNamespaces());
        $this->assertEquals(array(), $this->config->getRegisteredBundles());
        $this->assertEquals(array(), $this->config->getRoutingResources());
    }

    public function testRemovePluginPreservesSharedVendorNamespaces()
    {
        $this->manager->install('VendorX\FirstPluginBundle\VendorXFirstPluginBundle');
        $this->manager->install('VendorX\SecondPluginBundle\VendorXSecondPluginBundle');
        $this->manager->remove('VendorX\FirstPluginBundle\VendorXFirstPluginBundle');

        $this->assertEquals(array('VendorX'), $this->config->getRegisteredNamespaces());
    }

    public function testRemoveInexistentPluginThrowsAnException()
    {
        $this->setExpectedException('Claroline\PluginBundle\Service\PluginManager\Exception\ValidationException');
        $this->manager->install('VendorY\InexistentPluginBundle\VendorYInexistentPluginBundle');
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