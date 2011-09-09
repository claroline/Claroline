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
        $this->assertEquals(
            array('VendorX', 'VendorY', 'VendorZ'),
            $this->fileHandler->getRegisteredNamespaces()
        );
    }

    public function testGetRegisteredBundlesReturnsExpectedArray()
    {
        file_put_contents($this->bundlesFile, "VendorX\ABC\FirstBundle\nVendorY\DEF\SecondBundle");
        $this->assertEquals(
            array('VendorX\ABC\FirstBundle', 'VendorY\DEF\SecondBundle'),
            $this->fileHandler->getRegisteredBundles()
        );
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
        $this->assertEquals(
            array("VendorX", "VendorY", "VendorZ", 'Foo'),
            $this->fileHandler->getRegisteredNamespaces()
        );
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

        $this->assertEquals(
            array('VendorX', 'ABC', 'DEF', 'HIJ'),
            $this->fileHandler->getRegisteredNamespaces()
        );
    }

    public function testRemoveNamespaceDeletesEntry()
    {
        file_put_contents($this->namespacesFile, "VendorX\nVendorY\nVendorZ");
        $this->fileHandler->removeNamespace("VendorZ");
        $this->assertEquals(
            array('VendorX', 'VendorY'),
            $this->fileHandler->getRegisteredNamespaces()
        );
    }

    public function testRemoveUnregisteredNamespaceDoesntProduceError()
    {
        file_put_contents($this->namespacesFile, "VendorX\nVendorY");
        $this->fileHandler->removeNamespace("UnregisteredVendor");
        $this->assertEquals(
            array('VendorX', 'VendorY'),
            $this->fileHandler->getRegisteredNamespaces()
        );
    }

    public function testRemoveNamespaceCalledSeveralTimes()
    {
        file_put_contents($this->namespacesFile, "VendorX\nVendorY\nVendorZ");

        $this->fileHandler->removeNamespace("VendorX");
        $this->fileHandler->removeNamespace("VendorZ");

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
        $this->assertEquals(
            array("VendorX\\Foo", "VendorY\\Bar", "VendorZ\\Test"),
            $this->fileHandler->getRegisteredBundles()
        );
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

        $this->assertEquals(
            array('VendorX\\Foo', 'VendorX\\Bar', 'VendorY\\Foo'),
            $this->fileHandler->getRegisteredBundles()
        );
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
        $this->assertEquals(
            array('VendorX\\Foo', 'VendorY\\Bar'),
            $this->fileHandler->getRegisteredBundles()
        );
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
        $ds = DIRECTORY_SEPARATOR;
        $paths = array(
            'plugin'.$ds.'VendorX'.$ds.'DummyPluginBundle'.$ds.'Resources'.$ds.'routing.yml',
            'plugin'.$ds.'VendorX'.$ds.'DummyPluginBundle'.$ds.'Resources'.$ds.'routing2.yml',
            'special' => 'plugin'.$ds.'VendorX'.$ds.'DummyPluginBundle'.$ds.'Resources'.$ds.'More'.$ds.'routing.yml'
        );

        $this->fileHandler->importRoutingResources(
            'VendorX\DummyPluginBundle\VendorXDummyPluginBundle', 
            $paths,
            'dummy_prefix'
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
        $this->assertEquals($expectedResources, $this->fileHandler->getRoutingResources());
    }

    public function testImportRoutingResourcesPreservesExistingEntriesInRoutingFile()
    {
        $ds = DIRECTORY_SEPARATOR;
        $entry = "VendorXDummyPluginBundle_0:\n    "
            . "resource: '@VendorXDummyPluginBundle/Resources/routing.yml'\n    "
            . "prefix: dummy_prefix";
        file_put_contents($this->routingFile, $entry);

        $newPath = 'plugin'.$ds.'VendorY'.$ds.'DummyPluginBundle'.$ds.'Resources'.$ds.'routing.yml';
        $this->fileHandler->importRoutingResources(
            'VendorY\DummyPluginBundle\VendorYDummyPluginBundle', 
            array($newPath),
            'dummy_prefix');

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
        $this->assertEquals($expectedResources, $this->fileHandler->getRoutingResources());
    }

    public function testImportRoutingResourcesDoesntDuplicateEntry()
    {
        $ds = DIRECTORY_SEPARATOR;
        $path = 'plugin'.$ds.'VendorY'.$ds.'DummyPluginBundle'.$ds.'Resources'.$ds.'routing.yml';
        $this->fileHandler->importRoutingResources(
            'VendorY\DummyPluginBundle\VendorYDummyPluginBundle', 
            array($path),
            'dummy_prefix'
        );
        $this->fileHandler->importRoutingResources(
            'VendorY\DummyPluginBundle\VendorYDummyPluginBundle', 
            array($path),
            'dummy_prefix'
        );

        $expectedResources = array(
            'VendorYDummyPluginBundle_0' => array(
                'resource' => '@VendorYDummyPluginBundle/Resources/routing.yml',
                'prefix' => 'dummy_prefix'
                )
            );
        $this->assertEquals($expectedResources, $this->fileHandler->getRoutingResources());
    }

    public function testRemoveRoutingResourcesDeletesAllResourcesRelatedToAPlugin()
    {
        $entries = "VendorXDummyPluginBundle_1:\n    "
            . "resource: '@VendorXDummyPluginBundle/Resources/routing1.yml'\n    "
            . "prefix: dummy_prefix\n"
            . "VendorXDummyPluginBundle_2:\n    "
            . "resource: '@VendorXDummyPluginBundle/Resources/routing2.yml'\n    "
            . "prefix: dummy_prefix\n"
            . "VendorYDummyPluginBundle_0:\n    "
            . "resource: '@VendorYDummyPluginBundle/Resources/routing.yml'\n    "
            . "prefix: y_dummy_prefix";
        file_put_contents($this->routingFile, $entries);

        $this->fileHandler->removeRoutingResources('VendorX\DummyPluginBundle\VendorXDummyPluginBundle');

        $expectedResources = array(
            'VendorYDummyPluginBundle_0' => array(
                'resource' => '@VendorYDummyPluginBundle/Resources/routing.yml',
                'prefix' => 'y_dummy_prefix'
            )
        );
        
        $this->assertEquals($expectedResources, $this->fileHandler->getRoutingResources());
    }

    public function testImportThenRemoveRoutingResourcesKeepsConfigFileUnchanged()
    {
        $ds = DIRECTORY_SEPARATOR;
        $paths = array(
            'plugin'.$ds.'VendorX'.$ds.'DummyPluginBundle'.$ds.'Resources'.$ds.'routing.yml',
            'plugin'.$ds.'VendorX'.$ds.'DummyPluginBundle'.$ds.'Resources'.$ds.'routing2.yml');

        $this->fileHandler->importRoutingResources(
            'VendorX\DummyPluginBundle\VendorXDummyPluginBundle', 
            $paths,
            'dummy_prefix'
        );
        $this->fileHandler->removeRoutingResources(
            'VendorX\DummyPluginBundle\VendorXDummyPluginBundle'
        );

        $this->assertEquals(array(), $this->fileHandler->getRoutingResources());
    }
}