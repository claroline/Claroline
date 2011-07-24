<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\PluginBundle\Tests\Service\PluginManager\VirtualPluginFactory;

class ValidatorTest extends WebTestCase
{
    const VALIDATION_EXCEPTION = 'Claroline\PluginBundle\Service\PluginManager\Exception\ValidationException';
    
    /** @var Symfony\Bundle\FrameworkBundle\Client */
    private $client;

    /** @var Validator */
    private $validator;

    /** @var VirtualPluginFactory*/
    private $factory;

    /** @var string */
    private $pluginDir;

    public function setUp()
    {
        $this->client = self::createClient();
        $this->validator = $this->client->getContainer()->get('claroline.plugin.validator');
        $this->factory = new VirtualPluginFactory('plugin');
        $this->pluginDir = $this->factory->getPluginDirectoryPath();
    }

    public function testCheckThrowsAnExceptionOnInvalidPluginDirectory()
    {
        $this->setExpectedException(self::VALIDATION_EXCEPTION);
        $this->validator->check('Plugin', '/jljc%.:pkq');
    }

    public function testCheckThrowsAnExceptionOnInvalidPluginFQCN()
    {
        $this->setExpectedException(self::VALIDATION_EXCEPTION);
        $this->validator->check('xvz~sù:µ$fv', __DIR__);
    }

    public function testCheckThrowsAnExceptionOnUnloadablePluginClass()
    {
        $this->setExpectedException(self::VALIDATION_EXCEPTION);
        $this->factory->buildUnloadablePlugin('VendorA', 'PluginZBundle');
        $this->validator->check('VendorA\PluginZBundle\VendorAPluginZBundle', $this->pluginDir);
    }

    public function testCheckThrowsAnExceptionIfPluginClassDoesntInheritFromClarolinePlugin()
    {
        $this->setExpectedException(self::VALIDATION_EXCEPTION);
        $this->factory->buildNotClarolinePlugin('VendorH', 'Plugin123Bundle');
        $this->validator->check('VendorH\Plugin123Bundle\VendorHPlugin123Bundle', $this->pluginDir);
    }

    public function testCheckThrowsAnExceptionIfGetRoutingResourcesPathsReturnsInvalidPaths()
    {
        $this->setExpectedException(self::VALIDATION_EXCEPTION);
        $this->factory->buildInconsistentRoutingResourcesPlugin('VendorC', 'PluginDBundle');
        $this->validator->check('VendorC\PluginDBundle\VendorCPluginDBundle', $this->pluginDir);
    }

    public function testCheckDoesntThrowAnyExceptionOnValidArguments()
    {
        $this->factory->buildCompleteValidPlugin('VendorX', 'PluginYBundle');
        $this->validator->check('VendorX\PluginYBundle\VendorXPluginYBundle', $this->pluginDir);
    }
}