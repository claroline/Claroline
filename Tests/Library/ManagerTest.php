<?php

namespace Claroline\MigrationBundle\Library;

use Mockery as m;
use Claroline\MigrationBundle\Tests\MockeryTestCase;

require_once __DIR__ . '/../../vendor/twig/twig/lib/Twig/Environment.php';

class ManagerTest extends MockeryTestCase
{
    private $kernel;
    private $generator;
    private $writer;
    private $manager;

    protected function setUp()
    {
        parent::setUp();
        $this->kernel = m::mock('Symfony\Component\HttpKernel\Kernel');
        $this->writer = m::mock('Claroline\MigrationBundle\Library\Writer');
        $this->generator = m::mock('Claroline\MigrationBundle\Library\Generator');
        $this->manager = new Manager($this->kernel, $this->generator, $this->writer);
    }

    public function testGenerateBundleMigration()
    {
        $manager = m::mock(
            'Claroline\MigrationBundle\Library\Manager[getAvailablePlatforms]',
            array($this->kernel, $this->generator, $this->writer)
        );
        $bundle = m::mock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $platform = m::mock('Doctrine\DBAL\Platforms\AbstractPlatform');

        $this->kernel->shouldReceive('getBundle')
            ->once()
            ->with('FooBundle')
            ->andReturn($bundle);
        $manager->shouldReceive('getAvailablePlatforms')
            ->once()
            ->andReturn(array('driver' => $platform));
        $this->generator->shouldReceive('generateMigrationQueries')
            ->once()
            ->with($bundle, $platform)
            ->andReturn(array('queries'));
        $this->writer->shouldReceive('writeMigrationClass')
            ->once()
            ->with($bundle, 'driver', '123', array('queries'));

        $manager->generateBundleMigration('FooBundle');
    }

    public function testGetAvailablePlatforms()
    {
        $platforms = $this->manager->getAvailablePlatforms();
        $this->assertGreaterThan(1, count($platforms));
        $this->assertContains('pdo_mysql', array_keys($platforms));
        $this->assertInstanceOf('Doctrine\DBAL\Platforms\AbstractPlatform', $platforms['pdo_mysql']);
    }
}
