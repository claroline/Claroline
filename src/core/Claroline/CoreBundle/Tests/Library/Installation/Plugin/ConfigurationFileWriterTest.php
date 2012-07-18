<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Yaml\Yaml;
use org\bovigo\vfs\vfsStream;

class ConfigurationFileWriterTest extends WebTestCase
{
    /** @var ConfigurationFileWriter */
    private $configWriter;

    /** @var string */
    private $namespacesFile;

    /** @var string */
    private $bundlesFile;

    /** @var string */
    private $routingFile;

    protected function setUp()
    {
        $container = self::createClient()->getContainer();
        $this->configWriter = $container->get('claroline.plugin.recorder_configuration_file_writer');

        $structure = array('namespaces' => '', 'bundles' => '', 'routing.yml' => '');
        vfsStream::setup('virtual', null, $structure);

        $this->namespacesFile = vfsStream::url('virtual/namespaces');
        $this->bundlesFile = vfsStream::url('virtual/bundles');
        $this->routingFile = vfsStream::url('virtual/routing.yml');

        $this->configWriter->setPluginNamespacesFile($this->namespacesFile);
        $this->configWriter->setPluginBundlesFile($this->bundlesFile);
        $this->configWriter->setPluginRoutingFile($this->routingFile);
    }

    public function testRegisterNamespaceThrowsExceptionOnEmptyNamespaceArgument()
    {
        $this->setExpectedException('InvalidArgumentException');

        $this->configWriter->registerNamespace('');
    }

    public function testRegisterNamespaceWritesNewEntryInNamespacesFile()
    {
        $this->configWriter->registerNamespace('Foo');

        $this->assertTrue(in_array('Foo', $this->getRegisteredNamespaces()));
    }

    public function testRegisterNamespacePreservesOtherEntries()
    {
        file_put_contents($this->namespacesFile, "VendorX\nVendorY\nVendorZ");

        $this->configWriter->registerNamespace('Foo');

        $this->assertEquals(
            array("VendorX", "VendorY", "VendorZ", 'Foo'), $this->getRegisteredNamespaces()
        );
    }

    public function testRegisterNamespaceDoesntDuplicateNamespace()
    {
        file_put_contents($this->namespacesFile, 'Bar');

        $this->configWriter->registerNamespace('Bar');

        $this->assertEquals(1, count($this->getRegisteredNamespaces()));
    }

    public function testRegisterNamespaceCalledSeveralTimes()
    {
        $this->configWriter->registerNamespace('ABC');
        $this->configWriter->registerNamespace('DEF');
        $this->configWriter->registerNamespace('HIJ');

        $this->assertEquals(array('ABC', 'DEF', 'HIJ'), $this->getRegisteredNamespaces());
    }

    public function testRemoveNamespaceDeletesEntry()
    {
        file_put_contents($this->namespacesFile, "VendorX\nVendorY\nVendorZ");

        $this->configWriter->removeNamespace("VendorZ");

        $this->assertEquals(array('VendorX', 'VendorY'), $this->getRegisteredNamespaces());
    }

    public function testRemoveUnregisteredNamespaceDoesntProduceError()
    {
        file_put_contents($this->namespacesFile, "VendorX\nVendorY");

        $this->configWriter->removeNamespace("UnregisteredVendor");

        $this->assertEquals(array('VendorX', 'VendorY'), $this->getRegisteredNamespaces());
    }

    public function testRemoveNamespaceCalledSeveralTimes()
    {
        file_put_contents($this->namespacesFile, "VendorX\nVendorY\nVendorZ");

        $this->configWriter->removeNamespace("VendorX");
        $this->configWriter->removeNamespace("VendorZ");

        $this->assertEquals(array('VendorY'), $this->getRegisteredNamespaces());
    }

    public function testRegisterThenRemoveNamespaceLeftsConfigFileUnchanged()
    {
        file_put_contents($this->namespacesFile, 'VendorX');

        $this->configWriter->registerNamespace('VendorY');
        $this->configWriter->removeNamespace('VendorY');

        $this->assertEquals(array('VendorX'), $this->getRegisteredNamespaces());
    }

    public function testAddInstantiableBundleThrowsExceptionOnEmptyBundleFQCN()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->configWriter->addInstantiableBundle('');
    }

    public function testAddInstantiableBundleWritesNewEntryInBundlesFile()
    {
        $this->configWriter->addInstantiableBundle('Foo\\Bar');

        $this->assertTrue(in_array('Foo\\Bar', $this->getRegisteredBundles()));
    }

    public function testAddInstantiableBundlePreservesOtherEntries()
    {
        file_put_contents($this->bundlesFile, "VendorX\\Foo\nVendorY\\Bar");

        $this->configWriter->addInstantiableBundle('VendorZ\\Test');

        $this->assertEquals(
            array('VendorX\\Foo', 'VendorY\\Bar', 'VendorZ\\Test'), $this->getRegisteredBundles()
        );
    }

    public function testAddInstantiableBundleDoesntDuplicateBundle()
    {
        file_put_contents($this->bundlesFile, 'Foo\\Bar');

        $this->configWriter->addInstantiableBundle('Foo\\Bar');

        $this->assertEquals(1, count($this->getRegisteredBundles()));
    }

    public function testAddInstantiableBundleCalledSeveralTimes()
    {
        file_put_contents($this->bundlesFile, 'VendorX\\Foo');

        $this->configWriter->addInstantiableBundle('VendorX\\Bar');
        $this->configWriter->addInstantiableBundle('VendorY\\Foo');

        $this->assertEquals(
            array('VendorX\\Foo', 'VendorX\\Bar', 'VendorY\\Foo'), $this->getRegisteredBundles()
        );
    }

    public function testRemoveInstantiableBundleDeletesEntry()
    {
        file_put_contents($this->bundlesFile, "VendorX\\Foo\nVendorY\\Bar");

        $this->configWriter->removeInstantiableBundle('VendorY\\Bar');

        $this->assertEquals(array('VendorX\\Foo'), $this->getRegisteredBundles());
    }

    public function testRemoveUnregisteredBundleDoesntProduceError()
    {
        file_put_contents($this->bundlesFile, "VendorX\\Foo\nVendorY\\Bar");

        $this->configWriter->removeInstantiableBundle('UnregisteredVendor');

        $this->assertEquals(array('VendorX\\Foo', 'VendorY\\Bar'), $this->getRegisteredBundles());
    }

    public function testRemoveBundleCalledSeveralTimes()
    {
        file_put_contents($this->bundlesFile, "VendorX\\Foo\nVendorY\\Bar\nVendorZ\\Test");

        $this->configWriter->removeInstantiableBundle('VendorX\\Foo');
        $this->configWriter->removeInstantiableBundle('VendorZ\\Test');

        $this->assertEquals(array('VendorY\\Bar'), $this->getRegisteredBundles());
    }

    public function testAddThenRemoveInstantiableBundle()
    {
        file_put_contents($this->bundlesFile, 'VendorX\\Foo');

        $this->configWriter->addInstantiableBundle('VendorY\\Bar');
        $this->configWriter->removeInstantiableBundle('VendorY\\Bar');

        $this->assertEquals(array('VendorX\\Foo'), $this->getRegisteredBundles());
    }

    public function testImportRoutingResourcesAddsEntriesInRoutingFile()
    {
        $ds = DIRECTORY_SEPARATOR;
        $paths = array(
            "plugin{$ds}VendorX{$ds}DummyPluginBundle{$ds}Resources{$ds}routing.yml",
            "plugin{$ds}VendorX{$ds}DummyPluginBundle{$ds}Resources{$ds}routing2.yml",
            'special' => "plugin{$ds}VendorX{$ds}DummyPluginBundle{$ds}Resources{$ds}More{$ds}routing.yml"
        );

        $this->configWriter->importRoutingResources(
            'VendorX\DummyPluginBundle\VendorXDummyPluginBundle', $paths, 'dummy_prefix'
        );

        $expectedResources = array(
            'VendorXDummyPluginBundle_0' => array(
                'resource' => '@VendorXDummyPluginBundle/Resources/routing.yml',
                'prefix' => 'dummy_prefix'
            ),
            'VendorXDummyPluginBundle_1' => array(
                'resource' => '@VendorXDummyPluginBundle/Resources/routing2.yml',
                'prefix' => 'dummy_prefix'
            ),
            'VendorXDummyPluginBundle_special' => array(
                'resource' => '@VendorXDummyPluginBundle/Resources/More/routing.yml',
                'prefix' => 'dummy_prefix'
            )
        );

        $this->assertEquals($expectedResources, $this->getRoutingResources());
    }

    public function testImportRoutingResourcesPreservesExistingEntriesInRoutingFile()
    {
        $entry = "VendorXDummyPluginBundle_0:\n"
            . "    resource: '@VendorXDummyPluginBundle/Resources/routing.yml'\n"
            . "    prefix: dummy_prefix";
        file_put_contents($this->routingFile, $entry);

        $ds = DIRECTORY_SEPARATOR;
        $newPath = "plugin{$ds}VendorY{$ds}DummyPluginBundle{$ds}Resources{$ds}routing.yml";
        $this->configWriter->importRoutingResources(
            'VendorY\DummyPluginBundle\VendorYDummyPluginBundle', array($newPath), 'dummy_prefix'
        );

        $expectedResources = array(
            'VendorXDummyPluginBundle_0' => array(
                'resource' => '@VendorXDummyPluginBundle/Resources/routing.yml',
                'prefix' => 'dummy_prefix'
            ),
            'VendorYDummyPluginBundle_0' => array(
                'resource' => '@VendorYDummyPluginBundle/Resources/routing.yml',
                'prefix' => 'dummy_prefix'
            )
        );

        $this->assertEquals($expectedResources, $this->getRoutingResources());
    }

    public function testImportRoutingResourcesDoesntDuplicateEntry()
    {
        $ds = DIRECTORY_SEPARATOR;
        $path = "plugin{$ds}VendorY{$ds}DummyPluginBundle{$ds}Resources{$ds}routing.yml";
        $this->configWriter->importRoutingResources(
            'VendorY\DummyPluginBundle\VendorYDummyPluginBundle', array($path), 'dummy_prefix'
        );
        $this->configWriter->importRoutingResources(
            'VendorY\DummyPluginBundle\VendorYDummyPluginBundle', array($path), 'dummy_prefix'
        );

        $expectedResources = array(
            'VendorYDummyPluginBundle_0' => array(
                'resource' => '@VendorYDummyPluginBundle/Resources/routing.yml',
                'prefix' => 'dummy_prefix'
            )
        );

        $this->assertEquals($expectedResources, $this->getRoutingResources());
    }

    public function testRemoveRoutingResourcesDeletesAllResourcesRelatedToAPlugin()
    {
        $entries = "VendorXDummyPluginBundle_1:\n"
            . "    resource: '@VendorXDummyPluginBundle/Resources/routing1.yml'\n"
            . "    prefix: dummy_prefix\n"
            . "VendorXDummyPluginBundle_2:\n"
            . "    resource: '@VendorXDummyPluginBundle/Resources/routing2.yml'\n"
            . "    prefix: dummy_prefix\n"
            . "VendorYDummyPluginBundle_0:\n"
            . "    resource: '@VendorYDummyPluginBundle/Resources/routing.yml'\n"
            . "    prefix: y_dummy_prefix";
        file_put_contents($this->routingFile, $entries);

        $this->configWriter->removeRoutingResources('VendorX\DummyPluginBundle\VendorXDummyPluginBundle');

        $expectedResources = array(
            'VendorYDummyPluginBundle_0' => array(
                'resource' => '@VendorYDummyPluginBundle/Resources/routing.yml',
                'prefix' => 'y_dummy_prefix'
            )
        );

        $this->assertEquals($expectedResources, $this->getRoutingResources());
    }

    public function testImportThenRemoveRoutingResourcesKeepsConfigFileUnchanged()
    {
        $ds = DIRECTORY_SEPARATOR;
        $paths = array(
            "plugin{$ds}VendorX{$ds}DummyPluginBundle{$ds}Resources{$ds}routing.yml",
            "plugin{$ds}VendorX{$ds}DummyPluginBundle{$ds}Resources{$ds}routing2.yml"
        );

        $this->configWriter->importRoutingResources(
            'VendorX\DummyPluginBundle\VendorXDummyPluginBundle', $paths, 'dummy_prefix'
        );
        $this->configWriter->removeRoutingResources(
            'VendorX\DummyPluginBundle\VendorXDummyPluginBundle'
        );

        $this->assertEquals(array(), $this->getRoutingResources());
    }

    public function testIsRecordedReturnsExpectedValues()
    {
        $this->assertFalse($this->configWriter->isRecorded('VendorX\Foo'));

        file_put_contents($this->namespacesFile, 'VendorX');
        file_put_contents($this->bundlesFile, 'VendorX\\Foo');

        $this->assertTrue($this->configWriter->isRecorded('VendorX\Foo'));
    }

    private function getRegisteredNamespaces()
    {
        return file($this->namespacesFile, FILE_IGNORE_NEW_LINES);
    }

    private function getRegisteredBundles()
    {
        return file($this->bundlesFile, FILE_IGNORE_NEW_LINES);
    }

    private function getRoutingResources()
    {
        $resources = Yaml::parse($this->routingFile);

        return (array) $resources;
    }
}