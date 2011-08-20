<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Symfony\Component\Yaml\Yaml;
use \vfsStream;

class ConfigurationHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $config;
    private $namespacesFile;
    private $bundlesFile;
    private $routingFile;

    public function setUp()
    {
        vfsStream::setup('VirtualDir');
        vfsStream::create(array('namespaces' => '', 'bundles' => '', 'routing.yml' => ''),
                         'VirtualDir');
        $this->namespacesFile = vfsStream::url('VirtualDir/namespaces');
        $this->bundlesFile = vfsStream::url('VirtualDir/bundles');
        $this->routingFile = vfsStream::url('VirtualDir/routing.yml');
        $this->config = new ConfigurationHandler($this->namespacesFile, 
                                                 $this->bundlesFile,
                                                 $this->routingFile,
                                                 new Yaml());
    }

    public function testGetRegisteredNamespacesReturnsExpectedArray()
    {
        file_put_contents($this->namespacesFile, "VendorX\nVendorY\nVendorZ");
        $this->assertEquals(array('VendorX', 'VendorY', 'VendorZ'),
                            $this->config->getRegisteredNamespaces());
    }

    public function testGetRegisteredBundlesReturnsExpectedArray()
    {
        file_put_contents($this->bundlesFile, "VendorX\ABC\FirstBundle\nVendorY\DEF\SecondBundle");
        $this->assertEquals(array('VendorX\ABC\FirstBundle', 'VendorY\DEF\SecondBundle'),
                            $this->config->getRegisteredBundles());
    }

    public function testGetSharedVendorNamespacesReturnsExpectedArray()
    {
        file_put_contents($this->bundlesFile, "VendorX\A\Bundle\nVendorY\B\Bundle\nVendorX\C\Bundle");
        $this->assertEquals(array('VendorX'), $this->config->getSharedVendorNamespaces());
    }

    public function testGetRoutingResourcesReturnsExpectedArray()
    {
        file_put_contents($this->routingFile, "VendorXBundle:\n    TestResource");
        $resources = $this->config->getRoutingResources();        
        $this->assertEquals(1, count($resources));
        $this->assertEquals(array('VendorXBundle'), array_keys($resources));
        $this->assertEquals('TestResource', $resources['VendorXBundle']);
    }

    public function testRegisterNamespaceThrowsExceptionOnEmptyNamespace()
    {
        $this->setExpectedException('\Exception');
        $this->config->registerNamespace('');
    }

    public function testRegisterNamespaceWritesNewEntryInNamespacesFile()
    {
        $this->config->registerNamespace('Foo');
        $this->assertTrue(in_array('Foo', $this->config->getRegisteredNamespaces()));
    }

    public function testRegisterNamespacePreservesOtherEntries()
    {
        file_put_contents($this->namespacesFile, "VendorX\nVendorY\nVendorZ");
        $this->config->registerNamespace('Foo');
        $this->assertEquals(array("VendorX", "VendorY", "VendorZ", 'Foo'), 
                            $this->config->getRegisteredNamespaces());
    }

    public function testRegisterNamespaceDoesntDuplicateNamespace()
    {
        file_put_contents($this->namespacesFile, 'Bar');
        $this->config->registerNamespace('Bar');
        $this->assertTrue(count($this->config->getRegisteredNamespaces()) == 1);
    }

    public function testRegisterNamespaceCalledSeveralTimes()
    {
        file_put_contents($this->namespacesFile, 'VendorX');

        $this->config->registerNamespace('ABC');
        $this->config->registerNamespace('DEF');
        $this->config->registerNamespace('HIJ');

        $this->assertEquals(array('VendorX', 'ABC', 'DEF', 'HIJ'), 
                            $this->config->getRegisteredNamespaces());
    }

    public function testRemoveNamespaceDeletesEntry()
    {
        file_put_contents($this->namespacesFile, "VendorX\nVendorY\nVendorZ");
        $this->config->removeNamespace("VendorZ");
        $this->assertEquals(array('VendorX', 'VendorY'),
                            $this->config->getRegisteredNamespaces());
    }

    public function testRemoveUnregisteredNamespaceDoesntProduceError()
    {
        file_put_contents($this->namespacesFile, "VendorX\nVendorY");
        $this->config->removeNamespace("UnregisteredVendor");
        $this->assertEquals(array('VendorX', 'VendorY'), 
                            $this->config->getRegisteredNamespaces());
    }

    public function testRemoveNamespaceCalledSeveralTimes()
    {
        file_put_contents($this->namespacesFile, "VendorX\nVendorY\nVendorZ");

        $this->config->removeNamespace("VendorX");
        $this->config->removeNamespace("VendorZ");

        $namespaces = file($this->namespacesFile, FILE_IGNORE_NEW_LINES);
        $this->assertEquals(array('VendorY'), $this->config->getRegisteredNamespaces());
    }

    public function testRegisterThenRemoveNamespaceLeftsConfigFileUnchanged()
    {
        file_put_contents($this->namespacesFile, 'VendorX');

        $this->config->registerNamespace('VendorY');
        $this->config->removeNamespace('VendorY');

        $this->assertEquals(array('VendorX'), $this->config->getRegisteredNamespaces());
    }

    public function testAddInstantiableBundleThrowsExceptionOnEmptyBundleFQCN()
    {
        $this->setExpectedException('\Exception');
        $this->config->addInstantiableBundle('');
    }

    public function testAddInstantiableBundleWritesNewEntryInBundlesFile()
    {
        $this->config->addInstantiableBundle('Foo\\Bar');
        $this->assertTrue(in_array('Foo\\Bar', $this->config->getRegisteredBundles()));
    }

    public function testAddInstantiableBundlePreservesOtherEntries()
    {
        file_put_contents($this->bundlesFile, "VendorX\\Foo\nVendorY\\Bar");
        $this->config->addInstantiableBundle('VendorZ\\Test');
        $this->assertEquals(array("VendorX\\Foo", "VendorY\\Bar", "VendorZ\\Test"), 
                            $this->config->getRegisteredBundles());
    }

    public function testAddInstantiableBundleDoesntDuplicateBundle()
    {
        file_put_contents($this->bundlesFile, 'Foo\\Bar');
        $this->config->addInstantiableBundle('Foo\\Bar');
        $this->assertTrue(count($this->config->getRegisteredBundles()) == 1);
    }

    public function testAddInstantiableBundleCalledSeveralTimes()
    {
        file_put_contents($this->bundlesFile, 'VendorX\\Foo');

        $this->config->addInstantiableBundle('VendorX\\Bar');
        $this->config->addInstantiableBundle('VendorY\\Foo');

        $this->assertEquals(array('VendorX\\Foo', 'VendorX\\Bar', 'VendorY\\Foo'), 
                            $this->config->getRegisteredBundles());
    }
    
    public function testRemoveInstantiableBundleDeletesEntry()
    {
        file_put_contents($this->bundlesFile, "VendorX\\Foo\nVendorY\\Bar");
        $this->config->removeInstantiableBundle('VendorY\\Bar');
        $this->assertEquals(array('VendorX\\Foo'), $this->config->getRegisteredBundles());
    }

    public function testRemoveUnregisteredBundleDoesntProduceError()
    {
        file_put_contents($this->bundlesFile, "VendorX\\Foo\nVendorY\\Bar");
        $this->config->removeInstantiableBundle('UnregisteredVendor');
        $this->assertEquals(array('VendorX\\Foo', 'VendorY\\Bar'), 
                            $this->config->getRegisteredBundles());
    }   

    public function testRemoveBundleCalledSeveralTimes()
    {
        file_put_contents($this->bundlesFile, "VendorX\\Foo\nVendorY\\Bar\nVendorZ\\Test");

        $this->config->removeInstantiableBundle('VendorX\\Foo');
        $this->config->removeInstantiableBundle('VendorZ\\Test');

        $this->assertEquals(array('VendorY\\Bar'), $this->config->getRegisteredBundles());
    }

    public function testAddThenRemoveInstantiableBundle()
    {
        file_put_contents($this->bundlesFile, 'VendorX\\Foo');

        $this->config->addInstantiableBundle('VendorY\\Bar');
        $this->config->removeInstantiableBundle('VendorY\\Bar');

        $this->assertEquals(array('VendorX\\Foo'), $this->config->getRegisteredBundles());
    }

    public function testImportRoutingResourcesAddsEntriesInRoutingFile()
    {
        $paths = array(
            'plugin/VendorX/DummyPluginBundle/Resources/routing.yml',
            'plugin/VendorX/DummyPluginBundle/Resources/routing2.yml',
            'special' => 'plugin/VendorX/DummyPluginBundle/Resources/More/routing.yml');

        $this->config->importRoutingResources('VendorX\DummyPluginBundle\VendorXDummyPluginBundle', $paths);

        $expectedResources = array(
            'VendorXDummyPluginBundle_0' => array(
                'resource' => '@VendorXDummyPluginBundle/Resources/routing.yml'
                ),
            'VendorXDummyPluginBundle_1' => array(
                'resource' => '@VendorXDummyPluginBundle/Resources/routing2.yml'
                ),
            'VendorXDummyPluginBundle_special' => array(
                'resource' => '@VendorXDummyPluginBundle/Resources/More/routing.yml'
                )
            );
        $this->assertEquals($expectedResources, $this->config->getRoutingResources());
    }

    public function testImportRoutingResourcesPreservesExistingEntriesInRoutingFile()
    {
        $entry = "VendorXDummyPluginBundle_0:\n    "
               . "resource: '@VendorXDummyPluginBundle/Resources/routing.yml'";
        file_put_contents($this->routingFile, $entry);

        $newPath = 'plugin/VendorY/DummyPluginBundle/Resources/routing.yml';
        $this->config->importRoutingResources('VendorY\DummyPluginBundle\VendorYDummyPluginBundle', array($newPath));

        $expectedResources = array(
            'VendorXDummyPluginBundle_0' => array(
                'resource' => '@VendorXDummyPluginBundle/Resources/routing.yml'
                ),
            'VendorYDummyPluginBundle_0' => array(
                'resource' => '@VendorYDummyPluginBundle/Resources/routing.yml'
                )
            );
        $this->assertEquals($expectedResources, $this->config->getRoutingResources());
    }

    public function testImportRoutingResourcesDoesntDuplicateEntry()
    {
        $path = 'plugin/VendorY/DummyPluginBundle/Resources/routing.yml';
        $this->config->importRoutingResources('VendorY\DummyPluginBundle\VendorYDummyPluginBundle', array($path));
        $this->config->importRoutingResources('VendorY\DummyPluginBundle\VendorYDummyPluginBundle', array($path));

        $expectedResources = array(
            'VendorYDummyPluginBundle_0' => array(
                'resource' => '@VendorYDummyPluginBundle/Resources/routing.yml'
                )
            );
        $this->assertEquals($expectedResources, $this->config->getRoutingResources());
    }

    public function testRemoveRoutingResourcesDeletesAllResourcesRelatedToAPlugin()
    {
        $entries = "VendorXDummyPluginBundle_1:\n    "
                 . "resource: '@VendorXDummyPluginBundle/Resources/routing1.yml'\n"
                 . "VendorXDummyPluginBundle_2:\n    "
                 . "resource: '@VendorXDummyPluginBundle/Resources/routing2.yml'\n"
                 . "VendorYDummyPluginBundle_0:\n    "
                 . "resource: '@VendorYDummyPluginBundle/Resources/routing.yml'\n";
        file_put_contents($this->routingFile, $entries);

        $this->config->removeRoutingResources('VendorX\DummyPluginBundle\VendorXDummyPluginBundle');

        $expectedResources = array(
            'VendorYDummyPluginBundle_0' => array(
                'resource' => '@VendorYDummyPluginBundle/Resources/routing.yml'
                )
            );
        $this->assertEquals($expectedResources, $this->config->getRoutingResources());
    }

    public function testImportThenRemoveRoutingResourcesLeftsConfigFileUnchanged()
    {
        $paths = array(
            'plugin/VendorX/DummyPluginBundle/Resources/routing.yml',
            'plugin/VendorX/DummyPluginBundle/Resources/routing2.yml');

        $this->config->importRoutingResources('VendorX\DummyPluginBundle\VendorXDummyPluginBundle', $paths);
        $this->config->removeRoutingResources('VendorX\DummyPluginBundle\VendorXDummyPluginBundle');

        $this->assertEquals(array(), $this->config->getRoutingResources());
    }
}