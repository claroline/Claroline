<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use \vfsStream;

class ManagerTest extends WebTestCase
{
    private $client;
    private $writer;
    private $manager;

    public function setUp()
    {
        $this->buildPluginFiles();
        $validator = new Validator(vfsStream::url('virtual/plugin'));
        $this->writer = new FileWriter(vfsStream::url('virtual/config/namespaces'),
                                       vfsStream::url('virtual/config/bundles'));
        $this->client = self::createClient();
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->manager = new Manager($validator, $this->writer, $em);
    }

    public function testInstallAValidPluginRegistersItInConfigFiles()
    {
        $this->manager->install('VendorX\FirstPluginBundle\VendorXFirstPluginBundle');

        $this->assertEquals(array('VendorX'), $this->writer->getRegisteredNamespaces());
        $this->assertEquals(array('VendorX\FirstPluginBundle\VendorXFirstPluginBundle'), 
                            $this->writer->getRegisteredBundles());
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

        $this->assertEquals(array('VendorX'), $this->writer->getRegisteredNamespaces());
        $this->assertEquals(array('VendorX\SecondPluginBundle\VendorXSecondPluginBundle'), 
                            $this->writer->getRegisteredBundles());
    }

    public function testInstallSeveralValidPlugins()
    {
        $this->manager->install('VendorX\FirstPluginBundle\VendorXFirstPluginBundle');
        $this->manager->install('VendorX\SecondPluginBundle\VendorXSecondPluginBundle');
        $this->manager->install('VendorY\ThirdPluginBundle\VendorYThirdPluginBundle');

        $this->assertEquals(array('VendorX', 'VendorY'), $this->writer->getRegisteredNamespaces());
        $this->assertEquals(array('VendorX\FirstPluginBundle\VendorXFirstPluginBundle',
                                  'VendorX\SecondPluginBundle\VendorXSecondPluginBundle',
                                  'VendorY\ThirdPluginBundle\VendorYThirdPluginBundle'),
                            $this->writer->getRegisteredBundles());
    }

    public function testRemovePluginRemovesItFromConfigFiles()
    {
        $this->manager->install('VendorX\FirstPluginBundle\VendorXFirstPluginBundle');
        $this->manager->remove('VendorX\FirstPluginBundle\VendorXFirstPluginBundle');

        $this->assertEquals(array(), $this->writer->getRegisteredNamespaces());
        $this->assertEquals(array(), $this->writer->getRegisteredBundles());
    }

    public function testRemovePluginPreservesSharedVendorNamespaces()
    {
        $this->manager->install('VendorX\FirstPluginBundle\VendorXFirstPluginBundle');
        $this->manager->install('VendorX\SecondPluginBundle\VendorXSecondPluginBundle');
        $this->manager->remove('VendorX\FirstPluginBundle\VendorXFirstPluginBundle');

        $this->assertEquals(array('VendorX'), $this->writer->getRegisteredNamespaces());
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
                                                   {}'
                )
            );
        $secondPlugin = array(
            'SecondPluginBundle' => array(
                'VendorXSecondPluginBundle.php' => '<?php namespace VendorX\SecondPluginBundle;
                                                    class VendorXSecondPluginBundle extends
                                                    \Claroline\PluginBundle\AbstractType\ClarolinePlugin
                                                    {}'
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
                'bundles' => ''
                )
            );

        vfsStream::create($structure, 'virtual');
    }
}