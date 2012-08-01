<?php

namespace Claroline\CoreBundle\Library\Configuration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Yaml\Yaml;

class PlatformConfigurationHandlerTest extends WebTestCase
{
    /** @var PlatformConfigurationHandler */
    private $handler;

    /** @var string */
    private $stubConfigFile;

    protected function setUp()
    {
        parent::setUp();
        $this->stubConfigFile = __DIR__ . '/../../Stub/Misc/platform_options.yml';
        $this->initStubConfiguration();
        $this->handler = new PlatformConfigurationHandler(
            array('prod' => $this->stubConfigFile)
        );
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->eraseStubConfiguration();
    }

    /**
     * @dataProvider parameterAccessorProvider
     */
    public function testHandlerThrowsAnExceptionOnNonExistentParameterAccessAttempt($accessor)
    {
        $this->setExpectedException('RuntimeException');
        $this->handler->{$accessor}('non_existent_parameter', 'some_value');
    }

    public function testExistentParameterCanBeAccessed()
    {
        $this->assertEquals('bar', $this->handler->getParameter('foo'));
        $this->handler->setParameter('foo', 'new_value');
        $this->assertEquals('new_value', $this->handler->getParameter('foo'));
    }

    public function parameterAccessorProvider()
    {
        return array(
            array('getParameter'),
            array('setParameter')
        );
    }

    private function initStubConfiguration()
    {
        file_put_contents($this->stubConfigFile, Yaml::dump(array('foo' => 'bar')));
    }

    private function eraseStubConfiguration()
    {
        file_put_contents($this->stubConfigFile, Yaml::dump(array()));
    }
}