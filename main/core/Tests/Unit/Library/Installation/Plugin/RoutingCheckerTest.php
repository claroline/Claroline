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

    protected function setUp(): void
    {
        $this->router = $this->mock('Symfony\Bundle\FrameworkBundle\Routing\Router');
        $this->checker = new RoutingChecker($this->router, new Parser());
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

    public function nonExistentRoutingResourceProvider()
    {
        return [
            ['Invalid\NonExistentRoutingResource1\InvalidNonExistentRoutingResource1'],
            ['Invalid\NonExistentRoutingResource2\InvalidNonExistentRoutingResource2'],
        ];
    }

    public function unexpectedRoutingResourceLocationProvider()
    {
        return [
            ['Invalid\UnexpectedRoutingResourceLocation1\InvalidUnexpectedRoutingResourceLocation1'],
        ];
    }

    public function nonYamlRoutingResourceProvider()
    {
        return [
            ['Invalid\NonYamlRoutingResource1\InvalidNonYamlRoutingResource1'],
        ];
    }

    public function unloadableYamlRoutingResourceProvider()
    {
        return [
            ['Invalid\UnloadableRoutingResource1\InvalidUnloadableRoutingResource1'],
        ];
    }
}
