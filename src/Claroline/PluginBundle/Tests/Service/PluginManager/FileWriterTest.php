<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Claroline\PluginBundle\Service\PluginManager\FileWriter;
use \vfsStream;

class FileWriterTest extends \PHPUnit_Framework_TestCase
{
    private $fileWriter;
    private $namespacesFile;
    private $bundlesFile;

    public function setUp()
    {
        vfsStream::setup('VirtualDir');
        vfsStream::create(array('namespaces' => '', 'bundles' => ''), 'VirtualDir');
        $this->namespacesFile = vfsStream::url('VirtualDir/namespaces');
        $this->bundlesFile = vfsStream::url('VirtualDir/bundles');
        $this->fileWriter = new FileWriter($this->namespacesFile, $this->bundlesFile);
    }

    public function testGetRegisteredNamespacesReturnsExpectedArray()
    {
        file_put_contents($this->namespacesFile, "VendorX\nVendorY\nVendorZ");
        $this->assertEquals(array('VendorX', 'VendorY', 'VendorZ'),
                            $this->fileWriter->getRegisteredNamespaces());
    }

    public function testGetRegisteredBundlesReturnsExpectedArray()
    {
        file_put_contents($this->bundlesFile, "VendorX\ABC\FirstBundle\nVendorY\DEF\SecondBundle");
        $this->assertEquals(array('VendorX\ABC\FirstBundle', 'VendorY\DEF\SecondBundle'),
                            $this->fileWriter->getRegisteredBundles());
    }

    public function testGetSharedVendorNamespacesReturnsExpectedArray()
    {
        file_put_contents($this->bundlesFile, "VendorX\A\Bundle\nVendorY\B\Bundle\nVendorX\C\Bundle");
        $this->assertEquals(array('VendorX'), $this->fileWriter->getSharedVendorNamespaces());
    }

    public function testRegisterNamespaceThrowsExceptionOnEmptyNamespace()
    {
        $this->setExpectedException('\Exception');
        $this->fileWriter->registerNamespace('');
    }

    public function testRegisterNamespaceWritesNewEntryInNamespacesFile()
    {
        $this->fileWriter->registerNamespace('Foo');
        $this->assertTrue(in_array('Foo', $this->fileWriter->getRegisteredNamespaces()));
    }

    public function testRegisterNamespacePreservesOtherEntries()
    {
        file_put_contents($this->namespacesFile, "VendorX\nVendorY\nVendorZ");
        $this->fileWriter->registerNamespace('Foo');
        $this->assertEquals(array("VendorX", "VendorY", "VendorZ", 'Foo'), 
                            $this->fileWriter->getRegisteredNamespaces());
    }

    public function testRegisterNamespaceDoesntDuplicateNamespace()
    {
        file_put_contents($this->namespacesFile, 'Bar');
        $this->fileWriter->registerNamespace('Bar');
        $this->assertTrue(count($this->fileWriter->getRegisteredNamespaces()) == 1);
    }

    public function testRegisterNamespaceCalledSeveralTimes()
    {
        file_put_contents($this->namespacesFile, 'VendorX');

        $this->fileWriter->registerNamespace('ABC');
        $this->fileWriter->registerNamespace('DEF');
        $this->fileWriter->registerNamespace('HIJ');

        $this->assertEquals(array('VendorX', 'ABC', 'DEF', 'HIJ'), 
                            $this->fileWriter->getRegisteredNamespaces());
    }

    public function testRemoveNamespaceDeletesEntry()
    {
        file_put_contents($this->namespacesFile, "VendorX\nVendorY\nVendorZ");
        $this->fileWriter->removeNamespace("VendorZ");
        $this->assertEquals(array('VendorX', 'VendorY'),
                            $this->fileWriter->getRegisteredNamespaces());
    }

    public function testRemoveUnregisteredNamespaceDoesntProduceError()
    {
        file_put_contents($this->namespacesFile, "VendorX\nVendorY");
        $this->fileWriter->removeNamespace("UnregisteredVendor");
        $this->assertEquals(array('VendorX', 'VendorY'), 
                            $this->fileWriter->getRegisteredNamespaces());
    }

    public function testRemoveNamespaceCalledSeveralTimes()
    {
        file_put_contents($this->namespacesFile, "VendorX\nVendorY\nVendorZ");

        $this->fileWriter->removeNamespace("VendorX");
        $this->fileWriter->removeNamespace("VendorZ");

        $namespaces = file($this->namespacesFile, FILE_IGNORE_NEW_LINES);
        $this->assertEquals(array('VendorY'), $this->fileWriter->getRegisteredNamespaces());
    }

    public function testRegisterThenRemoveNamespace()
    {
        file_put_contents($this->namespacesFile, 'VendorX');

        $this->fileWriter->registerNamespace('VendorY');
        $this->fileWriter->removeNamespace('VendorY');

        $this->assertEquals(array('VendorX'), $this->fileWriter->getRegisteredNamespaces());
    }

    public function testAddInstantiableBundleThrowsExceptionOnEmptyBundleFQCN()
    {
        $this->setExpectedException('\Exception');
        $this->fileWriter->addInstantiableBundle('');
    }

    public function testAddInstantiableBundleWritesNewEntryInBundlesFile()
    {
        $this->fileWriter->addInstantiableBundle('Foo\\Bar');
        $this->assertTrue(in_array('Foo\\Bar', $this->fileWriter->getRegisteredBundles()));
    }

    public function testAddInstantiableBundlePreservesOtherEntries()
    {
        file_put_contents($this->bundlesFile, "VendorX\\Foo\nVendorY\\Bar");
        $this->fileWriter->addInstantiableBundle('VendorZ\\Test');
        $this->assertEquals(array("VendorX\\Foo", "VendorY\\Bar", "VendorZ\\Test"), 
                            $this->fileWriter->getRegisteredBundles());
    }

    public function testAddInstantiableBundleDoesntDuplicateBundle()
    {
        file_put_contents($this->bundlesFile, 'Foo\\Bar');
        $this->fileWriter->addInstantiableBundle('Foo\\Bar');
        $this->assertTrue(count($this->fileWriter->getRegisteredBundles()) == 1);
    }

    public function testAddInstantiableBundleCalledSeveralTimes()
    {
        file_put_contents($this->bundlesFile, 'VendorX\\Foo');

        $this->fileWriter->addInstantiableBundle('VendorX\\Bar');
        $this->fileWriter->addInstantiableBundle('VendorY\\Foo');

        $this->assertEquals(array('VendorX\\Foo', 'VendorX\\Bar', 'VendorY\\Foo'), 
                            $this->fileWriter->getRegisteredBundles());
    }
    
    public function testRemoveInstantiableBundleDeletesEntry()
    {
        file_put_contents($this->bundlesFile, "VendorX\\Foo\nVendorY\\Bar");
        $this->fileWriter->removeInstantiableBundle('VendorY\\Bar');
        $this->assertEquals(array('VendorX\\Foo'), $this->fileWriter->getRegisteredBundles());
    }

    public function testRemoveUnregisteredBundleDoesntProduceError()
    {
        file_put_contents($this->bundlesFile, "VendorX\\Foo\nVendorY\\Bar");
        $this->fileWriter->removeInstantiableBundle('UnregisteredVendor');
        $this->assertEquals(array('VendorX\\Foo', 'VendorY\\Bar'), 
                            $this->fileWriter->getRegisteredBundles());
    }   

    public function testRemoveBundleCalledSeveralTimes()
    {
        file_put_contents($this->bundlesFile, "VendorX\\Foo\nVendorY\\Bar\nVendorZ\\Test");

        $this->fileWriter->removeInstantiableBundle('VendorX\\Foo');
        $this->fileWriter->removeInstantiableBundle('VendorZ\\Test');

        $this->assertEquals(array('VendorY\\Bar'), $this->fileWriter->getRegisteredBundles());
    }

    public function testAddThenRemoveInstantiableBundle()
    {
        file_put_contents($this->bundlesFile, 'VendorX\\Foo');

        $this->fileWriter->addInstantiableBundle('VendorY\\Bar');
        $this->fileWriter->removeInstantiableBundle('VendorY\\Bar');

        $this->assertEquals(array('VendorX\\Foo'), $this->fileWriter->getRegisteredBundles());
    }
}