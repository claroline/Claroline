<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Claroline\PluginBundle\Service\PluginManager\config;
use \vfsStream;

class ConfigurationHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $config;
    private $namespacesFile;
    private $bundlesFile;

    public function setUp()
    {
        vfsStream::setup('VirtualDir');
        vfsStream::create(array('namespaces' => '', 'bundles' => ''), 'VirtualDir');
        $this->namespacesFile = vfsStream::url('VirtualDir/namespaces');
        $this->bundlesFile = vfsStream::url('VirtualDir/bundles');
        $this->config = new ConfigurationHandler($this->namespacesFile, $this->bundlesFile);
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

    public function testRegisterThenRemoveNamespace()
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
}