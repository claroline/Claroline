<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Claroline\PluginBundle\Service\PluginManager\Validator;
use Claroline\PluginBundle\Service\PluginManager\Exception\ValidationException;
use \vfsStream;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    private $validator;

    public function setUp()
    {
        vfsStream::setUp('plugin');
        $this->validator = new Validator(vfsStream::url('plugin'));
    }

    public function testConstructorThrowsAnExceptionOnInvalidPluginDirectoryPath()
    {
        try
        {
            new Validator('/inexistent/path');
            $this->fail("No exception thrown.");
        }
        catch(ValidationException $ex)
        {
            $this->assertEquals(ValidationException::INVALID_PLUGIN_DIR, $ex->getCode());
        }
    }

    /**
     * @dataProvider badFQCNProvider
     */
    public function testCheckThrowsAnExceptionOnInvalidPluginFQCN($FQCN)
    {
        try
        {
            $this->validator->check($FQCN);
            $this->fail('No exception thrown.');
        }
        catch(ValidationException $ex)
        {
            $this->assertEquals(ValidationException::INVALID_FQCN, $ex->getCode());
        }
    }

    /**
     * @dataProvider invalidDirectoryStructureProvider
     */
    public function testCheckThrowsAnExceptionOnInvalidPluginDirectoryStructure($FQCN)
    {
        try
        {
            $this->validator->check($FQCN);
            $this->fail("No exception thrown.");
        }
        catch(ValidationException $ex)
        {
            $this->assertEquals(ValidationException::INVALID_DIRECTORY_STRUCTURE, $ex->getCode());
        }
    }

    public function testCheckThrowsAnExceptionOnNonExistentPluginClassFile()
    {
        vfsStream::create(array('VendorY' => array('TestBundle' => array())), 'plugin');

        try
        {
            $this->validator->check('VendorY\TestBundle\VendorYTestBundle');
            $this->fail("No exception thrown.");
        }
        catch(ValidationException $ex)
        {
            $this->assertEquals(ValidationException::INVALID_PLUGIN_CLASS_FILE, $ex->getCode());
        }
    }

    /**
     * @dataProvider unloadablePluginClassProvider
     */
    public function testCheckThrowsAnExceptionOnUnloadablePluginClass($class)
    {
        $this->buildValidPluginStructure('plugin', 'VendorZ', 'DummyPluginBundle');
        file_put_contents(vfsStream::url('plugin/VendorZ/DummyPluginBundle/VendorZDummyPluginBundle.php'), $class);

        try
        {
            $this->validator->check('VendorZ\DummyPluginBundle\VendorZDummyPluginBundle');
            $this->fail("No exception thrown.");
        }
        catch(ValidationException $ex)
        {
            $this->assertEquals(ValidationException::INVALID_PLUGIN_CLASS, $ex->getCode());
        }
    }

    /**
     * @dataProvider wrongTypePluginClassProvider
     */
    public function testCheckThrowsAnExceptionIfPluginClassDoesntExtendClarolinePlugin($class)
    {
        $this->buildValidPluginStructure('plugin', 'Vendor123', 'DummyPluginBundle');
        file_put_contents(vfsStream::url('plugin/Vendor123/DummyPluginBundle/Vendor123DummyPluginBundle.php'), $class);

        try
        {
            $this->validator->check('Vendor123\DummyPluginBundle\Vendor123DummyPluginBundle');
            $this->fail("No exception thrown.");
        }
        catch(ValidationException $ex)
        {
            $this->assertEquals(ValidationException::INVALID_PLUGIN_TYPE, $ex->getCode());
        }
    }

    public function testCheckThrowsAnExceptionIfGetRoutingResourcesPathsReturnsInvalidPaths()
    {
        $this->buildValidPluginStructure('plugin', 'VendorXYZ', 'DummyPluginBundle');
        $class = "<?php namespace VendorXYZ\DummyPluginBundle; "
               . "class VendorXYZDummyPluginBundle extends "
               . "\Claroline\PluginBundle\AbstractType\ClarolinePlugin"
               . "{ public function getRoutingResourcesPaths()"
               . "{return 'wrong/path/file.foo';} }";
        file_put_contents(vfsStream::url('plugin/VendorXYZ/DummyPluginBundle/VendorXYZDummyPluginBundle.php'), $class);

        try
        {
            $this->validator->check('VendorXYZ\DummyPluginBundle\VendorXYZDummyPluginBundle');
            $this->fail("No exception thrown.");
        }
        catch(ValidationException $ex)
        {
            $this->assertEquals(ValidationException::INVALID_ROUTING_RESOURCES, $ex->getCode());
        }
    }

    public function testCheckDoesntThrowAnyExceptionOnValidPluginArgument()
    {
        $this->buildValidPluginStructure('plugin', 'Vendor456', 'DummyPluginBundle');
        $this->validator->check('Vendor456\DummyPluginBundle\Vendor456DummyPluginBundle');
    }

    public function badFQCNProvider()
    {
        return array(
          array('VendorX\DummyPluginBundle\BadNamespace\VendorXDummyPluginBundle'),
          array('VendorX\DummyPluginBundle\BadNamePluginBundle'),
          array('VendorX\VendorXDummyPluginBundle')
        );
    }

    public function invalidDirectoryStructureProvider()
    {
        return array(
          array('NonExistentVendor\TestBundle\NonExistentVendorTestBundle'),
          array('VendorX\NonExistentBundle\VendorXNonExistentBundle'),
        );
    }

    public function unloadablePluginClassProvider()
    {
        return array(
          array('<?php '),
          array('<?php namespace WrongNamespace; class VendorXDummyPluginBundle {}'),
          array('<?php namespace VendorX\DummyPluginBundle; class WrongClassName {}')
        );
    }

    public function wrongTypePluginClassProvider()
    {
        return array(
          array('<?php namespace Vendor123\DummyPluginBundle; class Vendor123DummyPluginBundle {}'),
          array('<?php namespace Vendor123\DummyPluginBundle; class Vendor123DummyPluginBundle extends \DOMDocument{}')
        );
    }

    private function buildValidPluginStructure($pluginDirectory, $vendorName, $pluginBundleName)
    {
        $pluginClass = "<?php namespace {$vendorName}\\{$pluginBundleName}; "
                     . "class {$vendorName}{$pluginBundleName} extends "
                     . "\Claroline\PluginBundle\AbstractType\ClarolinePlugin {}";

        $structure = array(
            $vendorName => array(
                $pluginBundleName => array(
                    array(
                        'Resources' => array(
                            'config' => array(
                                'routing.yml' => ''
                            )
                        )
                    ),
                    $vendorName . $pluginBundleName . '.php' => $pluginClass
                )
            )
        );

        return vfsStream::create($structure, $pluginDirectory);
    }
}