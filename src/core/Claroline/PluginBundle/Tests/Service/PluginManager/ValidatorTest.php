<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Yaml\Parser;
use Claroline\PluginBundle\Service\PluginManager\Exception\ValidationException;
use \vfsStream;
use \vfsStreamFile;

class ValidatorTest extends WebTestCase
{
    private $validator;

    public function setUp()
    {
        $client = self::createClient();

        vfsStream::setUp('plugin');

        $this->validator = $client->getContainer()->get('claroline.plugin.validator');
        $this->validator->setPluginDirectory(vfsStream::url('plugin'));
    }

    public function testConstructorThrowsAnExceptionOnInvalidPluginDirectoryPath()
    {
        try
        {
            new Validator('/inexistent/path', new Parser());
            $this->fail("No exception thrown.");
        }
        catch (ValidationException $ex)
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
        catch (ValidationException $ex)
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
        catch (ValidationException $ex)
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
        catch (ValidationException $ex)
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
        catch (ValidationException $ex)
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
        catch (ValidationException $ex)
        {
            $this->assertEquals(ValidationException::INVALID_PLUGIN_TYPE, $ex->getCode());
        }
    }

    public function testCheckThrowsAnExceptionOnInvalidRoutingResourcePath()
    {
        $this->buildValidPluginStructure('plugin', 'VendorXYZ', 'DummyPluginBundle');
        $class = "<?php namespace VendorXYZ\DummyPluginBundle; "
               . "class VendorXYZDummyPluginBundle extends "
               . "\Claroline\PluginBundle\AbstractType\ClarolinePlugin"
               . "{ public function getRoutingResourcesPaths()"
               . "{return 'wrong/path/file.yml';} }";
        file_put_contents(vfsStream::url('plugin/VendorXYZ/DummyPluginBundle/VendorXYZDummyPluginBundle.php'), $class);

        try
        {
            $this->validator->check('VendorXYZ\DummyPluginBundle\VendorXYZDummyPluginBundle');
            $this->fail("No exception thrown.");
        }
        catch (ValidationException $ex)
        {
            $this->assertEquals(ValidationException::INVALID_ROUTING_PATH, $ex->getCode());
        }
    }

    public function testCheckThrowsAnExceptionOnInvalidRoutingResourceLocation()
    {
        $dir = $this->buildValidPluginStructure('plugin', 'VendorXYZ123', 'DummyPluginBundle');
        $dir->addChild(new vfsStreamFile('routing.yml'));
        $url = vfsStream::url('plugin/routing.yml');
        $class = "<?php namespace VendorXYZ123\DummyPluginBundle; "
               . "class VendorXYZ123DummyPluginBundle extends "
               . "\Claroline\PluginBundle\AbstractType\ClarolinePlugin"
               . "{ public function getRoutingResourcesPaths()"
               . "{return '{$url}';} }";
        file_put_contents(vfsStream::url('plugin/VendorXYZ123/DummyPluginBundle/VendorXYZ123DummyPluginBundle.php'), $class);

        try
        {
            $this->validator->check('VendorXYZ123\DummyPluginBundle\VendorXYZ123DummyPluginBundle');
            $this->fail("No exception thrown.");
        }
        catch (ValidationException $ex)
        {
            $this->assertEquals(ValidationException::INVALID_ROUTING_LOCATION, $ex->getCode());
        }
    }

    public function testCheckThrowsAnExceptionOnNonYamlRoutingFile()
    {
        $dir = $this->buildValidPluginStructure('plugin', 'VendorABC123', 'DummyPluginBundle');
        $dir->getChild('VendorABC123')
            ->getChild('DummyPluginBundle')
            ->addChild(new vfsStreamFile('routing.txt'));
        $url = vfsStream::url('plugin/VendorABC123/DummyPluginBundle/routing.txt');
        $class = "<?php namespace VendorABC123\DummyPluginBundle; "
               . "class VendorABC123DummyPluginBundle extends "
               . "\Claroline\PluginBundle\AbstractType\ClarolinePlugin"
               . "{ public function getRoutingResourcesPaths()"
               . "{return '{$url}';} }";
        file_put_contents(vfsStream::url('plugin/VendorABC123/DummyPluginBundle/VendorABC123DummyPluginBundle.php'), $class);
        
        try
        {
            $this->validator->check('VendorABC123\DummyPluginBundle\VendorABC123DummyPluginBundle');
            $this->fail("No exception thrown.");
        }
        catch (ValidationException $ex)
        {
            $this->assertEquals(ValidationException::INVALID_ROUTING_EXTENSION, $ex->getCode());
        }
    }

    public function testCheckThrowsAnExceptionOnUnloadableYamlRoutingFile()
    {
        $this->buildValidPluginStructure('plugin', 'Vendor1234', 'DummyPluginBundle');
        file_put_contents(vfsStream::url('plugin/Vendor1234/DummyPluginBundle/Resources/config/routing.yml'),
                          "\tInvalidYaml:Foo:\n:Bar\n  :");

        try
        {
            $this->validator->check('Vendor1234\DummyPluginBundle\Vendor1234DummyPluginBundle');
            $this->fail("No exception thrown.");
        }
        catch (ValidationException $ex)
        {
            $this->assertEquals(ValidationException::INVALID_YAML_RESOURCE, $ex->getCode());
        }
    }

    /**
     * @dataProvider wrongTranslationKeyProvider
     */
    public function testCheckThrowsAnExceptionOnInvalidTranslationKey($nameKey, $descriptionKey)
    {
        $this->buildValidPluginStructure('plugin', 'Vendor6789', 'DummyPluginBundle');
        $class = '<?php namespace Vendor6789\DummyPluginBundle; '
               . 'class Vendor6789DummyPluginBundle extends '
               . '\Claroline\PluginBundle\AbstractType\ClarolinePlugin {'
               . "public function getNameTranslationKey() {return {$nameKey};}"
               . "public function getDescriptionTranslationKey() {return {$descriptionKey};}}";
        file_put_contents(vfsStream::url('plugin/Vendor6789/DummyPluginBundle/Vendor6789DummyPluginBundle.php'),
                          $class);

        try
        {
            $this->validator->check('Vendor6789\DummyPluginBundle\Vendor6789DummyPluginBundle');
            $this->fail("No exception thrown.");
        }
        catch (ValidationException $ex)
        {
            $this->assertEquals(ValidationException::INVALID_TRANSLATION_KEY, $ex->getCode());
        }
    }

    public function testCheckThrowsAnExceptionOnInvalidApplicationLauncher()
    {
        $this->buildValidPluginStructure('plugin', 'VendorAAA', 'DummyPluginBundle');
        $class = '<?php namespace VendorAAA\DummyPluginBundle; '
               . 'class VendorAAADummyPluginBundle extends '
               . '\Claroline\PluginBundle\AbstractType\ClarolineApplication {'
               . "public function getLaunchers() {return array('test');}}";
        file_put_contents(vfsStream::url('plugin/VendorAAA/DummyPluginBundle/VendorAAADummyPluginBundle.php'),
                          $class);

        try
        {
            $this->validator->check('VendorAAA\DummyPluginBundle\VendorAAADummyPluginBundle');
            $this->fail("No exception thrown.");
        }
        catch (ValidationException $ex)
        {
            $this->assertEquals(ValidationException::INVALID_APPLICATION_LAUNCHER, $ex->getCode());
        }
    }

    public function testCheackThrowsAnExceptionIfAnApplicationDoesntProvideLaunchers()
    {
        $this->buildValidPluginStructure('plugin', 'VendorBBB', 'DummyPluginBundle');
        $class = '<?php namespace VendorBBB\DummyPluginBundle; '
               . 'class VendorBBBDummyPluginBundle extends '
               . '\Claroline\PluginBundle\AbstractType\ClarolineApplication {'
               . "public function getLaunchers() {return array();}}";
        file_put_contents(vfsStream::url('plugin/VendorBBB/DummyPluginBundle/VendorBBBDummyPluginBundle.php'),
                          $class);

        try
        {
            $this->validator->check('VendorBBB\DummyPluginBundle\VendorBBBDummyPluginBundle');
            $this->fail("No exception thrown.");
        }
        catch (ValidationException $ex)
        {
            $this->assertEquals(ValidationException::INVALID_APPLICATION_LAUNCHER, $ex->getCode());
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

    public function wrongTranslationKeyProvider()
    {
        return array(
            array('new \DOMDocument', '"OK"'),
            array('""', '"OK"'),
            array('"OK"', 'new \DOMDocument'),
            array('"OK"',  '""')
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
                    'Resources' => array(
                        'config' => array(
                            'routing.yml' => ''
                        )
                    ),
                    $vendorName . $pluginBundleName . '.php' => $pluginClass
                )
            )
        );

        return vfsStream::create($structure, $pluginDirectory);
    }
}