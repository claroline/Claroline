<?php

namespace Claroline\MigrationBundle\Generator;

use Mockery as m;
use org\bovigo\vfs\vfsStream;
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
        vfsStream::setup(
            'root',
            null,
            array(
                'bundle' => array(
                    'path' => array(
                        'Migrations' => array(
                            'some_driver' => array(
                                'Versionsome_version.php' => ''
                            )
                        )
                    )
                )
            )
        );
        $this->twigEnvironment->shouldReceive('addExtension');
        $bundle = m::mock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $bundle->shouldReceive('getPath')->once()->andReturn(vfsStream::url('root') . '/bundle/path');
        $bundle->shouldReceive('getNamespace')->once()->andReturn('Bundle\Namespace');
        $this->fileSystem->shouldReceive('exists')
            ->once()
            ->with(vfsStream::url('root') . '/bundle/path/Migrations/some_driver')
            ->andReturn(false);
        $this->fileSystem->shouldReceive('mkdir')
            ->once()
            ->with(vfsStream::url('root') . '/bundle/path/Migrations/some_driver');
        $this->twigEngine->shouldReceive('render')
            ->once()
            ->with(
                'ClarolineMigrationBundle::migration_class.html.twig',
                array(
                    'namespace' => 'Bundle\Namespace\Migrations\some_driver',
                    'class' => 'Versionsome_version',
                    'upQueries' => 'queries up',
                    'downQueries' => 'queries down'
                )
            )
            ->andReturn('migration class content');
        $this->fileSystem->shouldReceive('touch')
            ->once()
            ->with(vfsStream::url('root') . '/bundle/path/Migrations/some_driver/Versionsome_version.php');

        $writer = new Writer($this->fileSystem, $this->twigEnvironment, $this->twigEngine);
        $writer->writeMigrationClass(
            $bundle,
            'some_driver',
            'some_version',
            array(Generator::QUERIES_UP => 'queries up', Generator::QUERIES_DOWN => 'queries down')
        );
        $this->assertEquals(
            'migration class content',
            file_get_contents(vfsStream::url('root') . '/bundle/path/Migrations/some_driver/Versionsome_version.php')
        );
    }
}
