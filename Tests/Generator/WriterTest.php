<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    public function testDeleteUpperMigrations()
    {
        vfsStream::setup(
            'root',
            null,
            array(
                'bundle' => array(
                    'path' => array(
                        'Migrations' => array(
                            'some_driver' => array(
                                'Version1.php' => '',
                                'Version2.php' => '',
                                'Version3.php' => ''
                            )
                        )
                    )
                )
            )
        );
        $this->twigEnvironment->shouldReceive('addExtension');
        $bundle = m::mock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $bundlePath = vfsStream::url('root') . '/bundle/path';
        $bundle->shouldReceive('getPath')->once()->andReturn($bundlePath);
        $this->fileSystem->shouldReceive('remove')
            ->once()
            ->with(array("{$bundlePath}/Migrations/some_driver/Version2.php"));
        $this->fileSystem->shouldReceive('remove')
            ->once()
            ->with(array("{$bundlePath}/Migrations/some_driver/Version3.php"));
        $writer = new Writer($this->fileSystem, $this->twigEnvironment, $this->twigEngine);
        $deletedVersions = $writer->deleteUpperMigrationClasses($bundle, 'some_driver', '1');
        $this->assertEquals(2, count($deletedVersions));
    }
}
