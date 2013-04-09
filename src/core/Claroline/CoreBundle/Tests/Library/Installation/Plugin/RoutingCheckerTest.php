<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RoutingCheckerTest extends WebTestCase
{
    /** @var CommonChecker */
    private $checker;

    /** @var Loader */
    private $loader;

    protected function setUp()
    {
        $container = static::createClient()->getContainer();
        $this->checker = $container->get('claroline.plugin.routing_checker');
        $pluginDirectory = $container->getParameter('claroline.param.stub_plugin_directory');
        $this->loader = new Loader($pluginDirectory);
    }

    /**
     * @dataProvider invalidRoutingPrefixProvider
     */
    public function testCheckerReturnsAnErrorOnInvalidRoutingPrefix($pluginFqcn)
    {
        $errors = $this->checker->check($this->loader->load($pluginFqcn));
        $this->assertEquals(RoutingChecker::INVALID_ROUTING_PREFIX, $errors[0]->getCode());
    }

    /**
     * @dataProvider alreadyRegisteredPrefixProvider
     */
    public function testCheckerReturnsAnErrorIfRoutingPrefixIsAlreadyRegistered($pluginFqcn)
    {
        $this->markTestSkipped('Symfony 2.2 doesn\'t provide a way to retrieve the registered prefixes');
        $errors = $this->checker->check($this->loader->load($pluginFqcn));
        $this->assertEquals(RoutingChecker::ALREADY_REGISTERED_PREFIX, $errors[0]->getCode());
    }

    /**
     * @dataProvider nonExistentRoutingResourceProvider
     */
    public function testCheckerReturnsAnErrorOnNonExistentRoutingResource($pluginFqcn)
    {
        $errors = $this->checker->check($this->loader->load($pluginFqcn));
        $this->assertEquals(RoutingChecker::NON_EXISTENT_ROUTING_FILE, $errors[0]->getCode());
    }

    /**
     * @dataProvider unexpectedRoutingResourceLocationProvider
     */
    public function testCheckerReturnsAnErrorOnUnexpectedRoutingResourceLocation($pluginFqcn)
    {
        $errors = $this->checker->check($this->loader->load($pluginFqcn));
        $this->assertEquals(RoutingChecker::INVALID_ROUTING_LOCATION, $errors[0]->getCode());
    }

    /**
     * @dataProvider nonYamlRoutingResourceProvider
     */
    public function testCheckerReturnsAnErrorOnNonYamlRoutingFile($pluginFqcn)
    {
        $errors = $this->checker->check($this->loader->load($pluginFqcn));
        $this->assertEquals(RoutingChecker::INVALID_ROUTING_EXTENSION, $errors[0]->getCode());
    }

    /**
     * @dataProvider unloadableYamlRoutingResourceProvider
     */
    public function testCheckerReturnsAnErrorOnUnloadableYamlRoutingFile($pluginFqcn)
    {
        $errors = $this->checker->check($this->loader->load($pluginFqcn));
        $this->assertEquals(RoutingChecker::INVALID_YAML_ROUTING_FILE, $errors[0]->getCode());
    }

    public function invalidRoutingPrefixProvider()
    {
        return array(
            array('Invalid\UnexpectedRoutingPrefix1\InvalidUnexpectedRoutingPrefix1'),
            array('Invalid\UnexpectedRoutingPrefix2\InvalidUnexpectedRoutingPrefix2'),
            array('Invalid\UnexpectedRoutingPrefix3\InvalidUnexpectedRoutingPrefix3')
        );
    }

    public function alreadyRegisteredPrefixProvider()
    {
        return array(
            array('Invalid\AlreadyRegisteredRoutingPrefix\InvalidAlreadyRegisteredRoutingPrefix')
        );
    }

    public function nonExistentRoutingResourceProvider()
    {
        return array(
            array('Invalid\NonExistentRoutingResource1\InvalidNonExistentRoutingResource1'),
            array('Invalid\NonExistentRoutingResource2\InvalidNonExistentRoutingResource2')
        );
    }

    public function unexpectedRoutingResourceLocationProvider()
    {
        return array(
            array('Invalid\UnexpectedRoutingResourceLocation1\InvalidUnexpectedRoutingResourceLocation1')
        );
    }

    public function nonYamlRoutingResourceProvider()
    {
        return array(
            array('Invalid\NonYamlRoutingResource1\InvalidNonYamlRoutingResource1')
        );
    }

    public function unloadableYamlRoutingResourceProvider()
    {
        return array(
            array('Invalid\UnloadableRoutingResource1\InvalidUnloadableRoutingResource1')
        );
    }
}