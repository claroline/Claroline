<?php

namespace Claroline\MigrationBundle\Library;

use Mockery as m;
use Claroline\MigrationBundle\Tests\MockeryTestCase;
use Claroline\MigrationBundle\Twig\SqlFormatterExtension;

class WriterTest extends MockeryTestCase
{
    private $twigEnvironment;
    private $twigEngine;
    private $fileSystem;

    protected function setUp()
    {
        parent::setUp();
        $this->twigEnvironment = m::mock('Twig_Environment');
        $this->twigEngine = m::mock('Symfony\Bundle\TwigBundle\TwigEngine');
        $this->fileSystem = m::mock('Symfony\Component\Filesystem\Filesystem');
    }

    public function testWriterAddsTheSqlFormatterExtension()
    {
        $this->twigEnvironment->shouldReceive('addExtension')->once()->with(
            m::on(
                function ($argument) {
                    return $argument instanceof SqlFormatterExtension;
                }
            )
        );
        $writer = new Writer($this->fileSystem, $this->twigEnvironment, $this->twigEngine);
    }

    public function testWriteMigrationClasses()
    {
        $this->markTestSkipped('vfsstream should be used here');

        $bundle = m::mock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $this->twigEnvironment->shouldReceive('addExtension');

        $writer = new Writer($this->fileSystem, $this->twigEnvironment, $this->twigEngine);
        $writer->writeMigrationClass($bundle, 'some_driver', 'some_version', array());
    }
}
