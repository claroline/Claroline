<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Claroline\PluginBundle\Tests\PluginBundleTestCase;

/**
 * Note : All the references to plugin's FQNCs in this test case are arbitrary
 *        strings (as no validation/existence check is involved)
 */
class FileHandlerTest extends PluginBundleTestCase
{
    public function testGetRegisteredNamespacesReturnsExpectedArray()
    {
        file_put_contents($this->namespacesFile, "VendorX\nVendorY\nVendorZ");
        $this->assertEquals(array('VendorX', 'VendorY', 'VendorZ'),
                            $this->fileHandler->getRegisteredNamespaces());
    }

    public function testGetRegisteredBundlesReturnsExpectedArray()
    {
        file_put_contents($this->bundlesFile, "VendorX\ABC\FirstBundle\nVendorY\DEF\SecondBundle");
        $this->assertEquals(array('VendorX\ABC\FirstBundle', 'VendorY\DEF\SecondBundle'),
                            $this->fileHandler->getRegisteredBundles());
    }

    public function testGetSharedVendorNamespacesReturnsExpectedArray()
    {
        file_put_contents($this->bundlesFile, "VendorX\A\Bundle\nVendorY\B\Bundle\nVendorX\C\Bundle");
        $this->assertEquals(array('VendorX'), $this->fileHandler->getSharedVendorNamespaces());
    }

    public function testGetRoutingResourcesReturnsExpectedArray()
    {
        file_put_contents($this->routingFile, "VendorXBundle:\n    TestResource");
        $resources = $this->fileHandler->getRoutingResources();
        $this->assertEquals(1, count($resources));
        $this->assertEquals(array('VendorXBundle'), array_keys($resources));
        $this->assertEquals('TestResource', $resources['VendorXBundle']);
    }

    public function testRegisterNamespaceThrowsExceptionOnEmptyNamespace()
    {
        $this->setExpectedException('\Exception');
        $this->fileHandler->registerNamespace('');
    }

    public function testRegisterNamespaceWritesNewEntryInNamespacesFile()
    {
        $this->fileHandler->registerNamespace('Foo');
        $this->assertTrue(in_array('Foo', $this->fileHandler->getRegisteredNamespaces()));
    }

    public function testRegisterNamespacePreservesOtherEntries()
    {
        file_put_contents($this->namespacesFile, "VendorX\nVendorY\nVendorZ");
        $this->fileHandler->registerNamespace('Foo');
        $this->assertEquals(array("VendorX", "VendorY", "VendorZ", 'Foo'),
                            $this->fileHandler->getRegisteredNamespaces());
    }

    public function testRegisterNamespaceDoesntDuplicateNamespace()
    {
        file_put_contents($this->namespacesFile, 'Bar');
        $this->fileHandler->registerNamespace('Bar');
        $this->assertEquals(1, count($this->fileHandler->getRegisteredNamespaces()));
    }

    public function testRegisterNamespaceCalledSeveralTimes()
    {
        file_put_contents($this->namespacesFile, 'VendorX');

        $this->fileHandler->registerNamespace('ABC');
        $this->fileHandler->registerNamespace('DEF');
        $this->fileHandler->registerNamespace('HIJ');

        $this->assertEquals(array('VendorX', 'ABC', 'DEF', 'HIJ'),
                            $this->fileHandler->getRegisteredNamespaces());
    }

    public function testRemoveNamespaceDeletesEntry()
    {
        file_put_contents($this->namespacesFile, "VendorX\nVendorY\nVendorZ");
        $this->fileHandler->removeNamespace("VendorZ");
        $this->assertEquals(array('VendorX', 'VendorY'),
                            $this->fileHandler->getRegisteredNamespaces());
    }

    public function testRemoveUnregisteredNamespaceDoesntProduceError()
    {
        file_put_contents($this->namespacesFile, "VendorX\nVendorY");
        $this->fileHandler->removeNamespace("UnregisteredVendor");
        $this->assertEquals(array('VendorX', 'VendorY'),
                            $this->fileHandler->getRegisteredNamespaces());
    }

    public function testRemoveNamespaceCalledSeveralTimes()
    {
        file_put_contents($this->namespacesFile, "VendorX\nVendorY\nVendorZ");

        $this->fileHandler->removeNamespace("VendorX");
        $this->fileHandler->removeNamespace("VendorZ");

        $namespaces = file($this->namespacesFile, FILE_IGNORE_NEW_LINES);
        $this->assertEquals(array('VendorY'), $this->fileHandler->getRegisteredNamespaces());
    }

    public function testRegisterThenRemoveNamespaceLeftsConfigFileUnchanged()
    {
        file_put_contents($this->namespacesFile, 'VendorX');

        $this->fileHandler->registerNamespace('VendorY');
        $this->fileHandler->removeNamespace('VendorY');

        $this->assertEquals(array('VendorX'), $this->fileHandler->getRegisteredNamespaces());
    }

    public function testAddInstantiableBundleThrowsExceptionOnEmptyBundleFQCN()
    {
        $this->setExpectedException('\Exception');
        $this->fileHandler->addInstantiableBundle('');
    }

    public function testAddInstantiableBundleWritesNewEntryInBundlesFile()
    {
        $this->fileHandler->addInstantiableBundle('Foo\\Bar');
        $this->assertTrue(in_array('Foo\\Bar', $this->fileHandler->getRegisteredBundles()));
    }

    public function testAddInstantiableBundlePreservesOtherEntries()
    {
        file_put_contents($this->bundlesFile, "VendorX\\Foo\nVendorY\\Bar");
        $this->fileHandler->addInstantiableBundle('VendorZ\\Test');
        $this->assertEquals(array("VendorX\\Foo", "VendorY\\Bar", "VendorZ\\Test"),
                            $this->fileHandler->getRegisteredBundles());
    }

    public function testAddInstantiableBundleDoesntDuplicateBundle()
    {
        file_put_contents($this->bundlesFile, 'Foo\\Bar');
        $this->fileHandler->addInstantiableBundle('Foo\\Bar');
        $this->assertTrue(count($this->fileHandler->getRegisteredBundles()) == 1);
    }

    public function testAddInstantiableBundleCalledSeveralTimes()
    {
        file_put_contents($this->bundlesFile, 'VendorX\\Foo');

        $this->fileHandler->addInstantiableBundle('VendorX\\Bar');
        $this->fileHandler->addInstantiableBundle('VendorY\\Foo');

        $this->assertEquals(array('VendorX\\Foo', 'VendorX\\Bar', 'VendorY\\Foo'),
                            $this->fileHandler->getRegisteredBundles());
    }

    public function testRemoveInstantiableBundleDeletesEntry()
    {
        file_put_contents($this->bundlesFile, "VendorX\\Foo\nVendorY\\Bar");
        $this->fileHandler->removeInstantiableBundle('VendorY\\Bar');
        $this->assertEquals(array('VendorX\\Foo'), $this->fileHandler->getRegisteredBundles());
    }

    public function testRemoveUnregisteredBundleDoesntProduceError()
    {
        file_put_contents($this->bundlesFile, "VendorX\\Foo\nVendorY\\Bar");
        $this->fileHandler->removeInstantiableBundle('UnregisteredVendor');
        $this->assertEquals(array('VendorX\\Foo', 'VendorY\\Bar'),
                            $this->fileHandler->getRegisteredBundles());
    }

    public function testRemoveBundleCalledSeveralTimes()
    {
        file_put_contents($this->bundlesFile, "VendorX\\Foo\nVendorY\\Bar\nVendorZ\\Test");

        $this->fileHandler->removeInstantiableBundle('VendorX\\Foo');
        $this->fileHandler->removeInstantiableBundle('VendorZ\\Test');

        $this->assertEquals(array('VendorY\\Bar'), $this->fileHandler->getRegisteredBundles());
    }

    public function testAddThenRemoveInstantiableBundle()
    {
        file_put_contents($this->bundlesFile, 'VendorX\\Foo');

        $this->fileHandler->addInstantiableBundle('VendorY\\Bar');
        $this->fileHandler->removeInstantiableBundle('VendorY\\Bar');

        $this->assertEquals(array('VendorX\\Foo'), $this->fileHandler->getRegisteredBundles());
    }

    public function testImportRoutingResourcesAddsEntriesInRoutingFile()
    {
        $paths = array(
            'plugin/VendorX/DummyPluginBundle/Resources/routing.yml',
            'plugin/VendorX/DummyPluginBundle/Resources/routing2.yml',
            'special' => 'plugin/VendorX/DummyPluginBundle/Resources/More/routing.yml');

        $this->fileHandler->importRoutingResources('VendorX\DummyPluginBundle\VendorXDummyPluginBundle', $paths);

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
        $this->assertEquals($expectedResources, $this->fileHandler->getRoutingResources());
    }

    public function testImportRoutingResourcesPreservesExistingEntriesInRoutingFile()
    {
        $entry = "VendorXDummyPluginBundle_0:\n    "
               . "resource: '@VendorXDummyPluginBundle/Resources/routing.yml'";
        file_put_contents($this->routingFile, $entry);

        $newPath = 'plugin/VendorY/DummyPluginBundle/Resources/routing.yml';
        $this->fileHandler->importRoutingResources('VendorY\DummyPluginBundle\VendorYDummyPluginBundle', array($newPath));

        $expectedResources = array(
            'VendorXDummyPluginBundle_0' => array(
                'resource' => '@VendorXDummyPluginBundle/Resources/routing.yml'
                ),
            'VendorYDummyPluginBundle_0' => array(
                'resource' => '@VendorYDummyPluginBundle/Resources/routing.yml'
                )
            );
        $this->assertEquals($expectedResources, $this->fileHandler->getRoutingResources());
    }

    public function testImportRoutingResourcesDoesntDuplicateEntry()
    {
        $path = 'plugin/VendorY/DummyPluginBundle/Resources/routing.yml';
        $this->fileHandler->importRoutingResources('VendorY\DummyPluginBundle\VendorYDummyPluginBundle', array($path));
        $this->fileHandler->importRoutingResources('VendorY\DummyPluginBundle\VendorYDummyPluginBundle', array($path));

        $expectedResources = array(
            'VendorYDummyPluginBundle_0' => array(
                'resource' => '@VendorYDummyPluginBundle/Resources/routing.yml'
                )
            );
        $this->assertEquals($expectedResources, $this->fileHandler->getRoutingResources());
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

        $this->fileHandler->removeRoutingResources('VendorX\DummyPluginBundle\VendorXDummyPluginBundle');

        $expectedResources = array(
            'VendorYDummyPluginBundle_0' => array(
                'resource' => '@VendorYDummyPluginBundle/Resources/routing.yml'
                )
            );
        $this->assertEquals($expectedResources, $this->fileHandler->getRoutingResources());
    }

    public function testImportThenRemoveRoutingResourcesKeepsConfigFileUnchanged()
    {
        $paths = array(
            'plugin/VendorX/DummyPluginBundle/Resources/routing.yml',
            'plugin/VendorX/DummyPluginBundle/Resources/routing2.yml');

        $this->fileHandler->importRoutingResources('VendorX\DummyPluginBundle\VendorXDummyPluginBundle', $paths);
        $this->fileHandler->removeRoutingResources('VendorX\DummyPluginBundle\VendorXDummyPluginBundle');

        $this->assertEquals(array(), $this->fileHandler->getRoutingResources());
    }
}