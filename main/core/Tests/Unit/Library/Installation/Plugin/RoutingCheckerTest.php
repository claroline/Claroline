<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Library\Testing\StubPluginTrait;
use Symfony\Component\Yaml\Parser;

class RoutingCheckerTest extends MockeryTestCase
{
    use StubPluginTrait;

    private $router;
    private $checker;

    protected function setUp()
    {
        $this->router = $this->mock('Symfony\Bundle\FrameworkBundle\Routing\Router');
        $this->checker = new RoutingChecker($this->router, new Parser());
    }

    /**
     * @dataProvider invalidRoutingPrefixProvider
     */
    public function testCheckerReturnsAnErrorOnInvalidRoutingPrefix($pluginFqcn)
    {
        $errors = $this->checker->check($this->loadPlugin($pluginFqcn));
        $this->assertEquals(RoutingChecker::INVALID_ROUTING_PREFIX, $errors[0]->getCode());
    }

    /**
     * @dataProvider alreadyRegisteredPrefixProvider
     */
    public function testCheckerReturnsAnErrorIfRoutingPrefixIsAlreadyRegistered($pluginFqcn)
    {
        $this->markTestSkipped('Symfony 2.2 doesn\'t provide a way to retrieve the registered prefixes');
        $errors = $this->checker->check($this->loadPlugin($pluginFqcn));
        $this->assertEquals(RoutingChecker::ALREADY_REGISTERED_PREFIX, $errors[0]->getCode());
    }

    /**
     * @dataProvider nonExistentRoutingResourceProvider
     */
    public function testCheckerReturnsAnErrorOnNonExistentRoutingResource($pluginFqcn)
    {
        $errors = $this->checker->check($this->loadPlugin($pluginFqcn));
        $this->assertEquals(RoutingChecker::NON_EXISTENT_ROUTING_FILE, $errors[0]->getCode());
    }

    /**
     * @dataProvider unexpectedRoutingResourceLocationProvider
     */
    public function testCheckerReturnsAnErrorOnUnexpectedRoutingResourceLocation($pluginFqcn)
    {
        $errors = $this->checker->check($this->loadPlugin($pluginFqcn));
        $this->assertEquals(RoutingChecker::INVALID_ROUTING_LOCATION, $errors[0]->getCode());
    }

    /**
     * @dataProvider nonYamlRoutingResourceProvider
     */
    public function testCheckerReturnsAnErrorOnNonYamlRoutingFile($pluginFqcn)
    {
        $errors = $this->checker->check($this->loadPlugin($pluginFqcn));
        $this->assertEquals(RoutingChecker::INVALID_ROUTING_EXTENSION, $errors[0]->getCode());
    }

    /**
     * @dataProvider unloadableYamlRoutingResourceProvider
     */
    public function testCheckerReturnsAnErrorOnUnloadableYamlRoutingFile($pluginFqcn)
    {
        $errors = $this->checker->check($this->loadPlugin($pluginFqcn));
        $this->assertEquals(RoutingChecker::INVALID_YAML_ROUTING_FILE, $errors[0]->getCode());
    }

    /**
     * @dataProvider provideValidPlugins
     */
    public function testCheckerReturnsNoErrorOnValidPlugin($pluginFqcn)
    {
        $this->assertEquals(0, count($this->checker->check($this->loadPlugin($pluginFqcn))));
    }

    public function invalidRoutingPrefixProvider()
    {
        return array(
            array('Invalid\UnexpectedRoutingPrefix1\InvalidUnexpectedRoutingPrefix1'),
            array('Invalid\UnexpectedRoutingPrefix2\InvalidUnexpectedRoutingPrefix2'),
            array('Invalid\UnexpectedRoutingPrefix3\InvalidUnexpectedRoutingPrefix3'),
        );
    }

    public function alreadyRegisteredPrefixProvider()
    {
        return array(
            array('Invalid\AlreadyRegisteredRoutingPrefix\InvalidAlreadyRegisteredRoutingPrefix'),
        );
    }

    public function nonExistentRoutingResourceProvider()
    {
        return array(
            array('Invalid\NonExistentRoutingResource1\InvalidNonExistentRoutingResource1'),
            array('Invalid\NonExistentRoutingResource2\InvalidNonExistentRoutingResource2'),
        );
    }

    public function unexpectedRoutingResourceLocationProvider()
    {
        return array(
            array('Invalid\UnexpectedRoutingResourceLocation1\InvalidUnexpectedRoutingResourceLocation1'),
        );
    }

    public function nonYamlRoutingResourceProvider()
    {
        return array(
            array('Invalid\NonYamlRoutingResource1\InvalidNonYamlRoutingResource1'),
        );
    }

    public function unloadableYamlRoutingResourceProvider()
    {
        return array(
            array('Invalid\UnloadableRoutingResource1\InvalidUnloadableRoutingResource1'),
        );
    }
}
