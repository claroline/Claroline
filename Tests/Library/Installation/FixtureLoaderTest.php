<?php

namespace Claroline\CoreBundle\Library\Installation;

use Mockery as m;
use org\bovigo\vfs\vfsStream;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class FixtureLoaderTest extends MockeryTestCase
{
    private $bundle;
    private $baseLoader;
    private $executor;
    private $loader;

    protected function setUp()
    {
        $this->markTestSkipped('This test case should be moved to install bundle');
        parent::setUp();
        $this->bundle = $this->mock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $this->baseLoader = $this->mock('Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader');
        $this->executor = $this->mock('Doctrine\Common\DataFixtures\Executor\ORMExecutor');
        $this->loader = new FixtureLoader($this->baseLoader, $this->executor);
    }

    public function testLoadReturnsFalseIfNoFixtureDirectory()
    {
        $this->bundle->shouldReceive('getPath')->once()->andReturn('/non/existent');
        $this->assertFalse($this->loader->load($this->bundle));
    }

    public function testLoadReturnsFalseIfNoFixtureToLoad()
    {
        vfsStream::setup('bundleDir', null, array('DataFixtures' => array()));
        $this->bundle->shouldReceive('getPath')
            ->once()
            ->andReturn(vfsStream::url('bundleDir'));
        $this->baseLoader->shouldReceive('loadFromDirectory')
            ->once()
            ->with(vfsStream::url('bundleDir/DataFixtures'));
        $this->baseLoader->shouldReceive('getFixtures')->once()->andReturn(false);
        $this->assertFalse($this->loader->load($this->bundle));
    }

    /**
     * @dataProvider fixtureProvider
     */
    public function testLoad($structure, $fixtureDirectory)
    {
        vfsStream::setup('bundleDir', null, $structure);
        $this->bundle->shouldReceive('getPath')
            ->once()
            ->andReturn(vfsStream::url('bundleDir'));
        $this->baseLoader->shouldReceive('loadFromDirectory')
            ->once()
            ->with(vfsStream::url($fixtureDirectory));
        $this->baseLoader->shouldReceive('getFixtures')
            ->once()
            ->andReturn(array('some fixtures'));
        $this->executor->shouldReceive('execute')
            ->once()
            ->with(array('some fixtures'), true);
        $this->assertTrue($this->loader->load($this->bundle));
    }

    public function fixtureProvider()
    {
        return array(
            array(array('DataFixtures' => array()), 'bundleDir/DataFixtures'),
            array(array('DataFixtures' => array('ORM' => array())), 'bundleDir/DataFixtures/ORM')
        );
    }
}
