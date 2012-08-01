<?php

namespace Claroline\CoreBundle\Library\Testing;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Yaml\Yaml;

class PlatformTestConfigurationHandlerTest extends WebTestCase
{
    /** @var PlatformTestConfigurationHandler */
    private $handler;

    /** @var string */
    private $stubProdFile;

    /** @var string */
    private $stubTestFile;

    protected function setUp()
    {
        $this->stubProdFile = __DIR__ . '/../../Stub/Misc/platform_options.yml';
        $this->stubTestFile = __DIR__ . '/../../Stub/Misc/platform_test_options.yml';
        $this->initStubConfiguration();
        $configFiles = array(
            'prod' => $this->stubProdFile,
            'test' => $this->stubTestFile
        );
        $this->handler = new PlatformTestConfigurationHandler($configFiles);
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->eraseStubConfiguration();
    }

    public function testHandlerCalledAsServiceInTestEnvironmentIsATestConfigHandler()
    {
        $handler = self::createClient()
            ->getContainer()
            ->get('claroline.config.platform_config_handler');
        $this->assertInstanceOf(
            'Claroline\CoreBundle\Library\Testing\PlatformTestConfigurationHandler', $handler
        );
    }

    /**
     * @dataProvider parameterAccessorProvider
     */
    public function testHandlerThrowsAnExceptionOnNonExistentProdParameterAccessAttempt($accessor)
    {
        $this->setExpectedException('RuntimeException');
        $this->handler->{$accessor}('non_existent_parameter', 'some_value');
    }

    public function testSettingATestParameterValueDoesntAffectProdConfiguration()
    {
        $this->handler->setParameter('foo', 'some_value');
        $this->assertEquals('bar', $this->getProdConfigParameter('foo'));
    }

    public function testParameterIsReadFromTestConfigIfSetAndFromProdConfigOtherwise()
    {
        $this->assertEquals(
            $this->getProdConfigParameter('foo'), $this->handler->getParameter('foo')
        );

        $this->handler->setParameter('foo', 'some_value');

        $this->assertEquals('some_value', $this->handler->getParameter('foo'));
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
        file_put_contents($this->stubProdFile, Yaml::dump(array('foo' => 'bar')));
        file_put_contents($this->stubTestFile, Yaml::dump(array()));
    }

    private function eraseStubConfiguration()
    {
        file_put_contents($this->stubProdFile, Yaml::dump(array()));
        $this->handler->eraseTestConfiguration();
    }

    private function getProdConfigParameter($parameter)
    {
        $prodConfig = Yaml::parse($this->stubProdFile);

        return $prodConfig[$parameter];
    }
}