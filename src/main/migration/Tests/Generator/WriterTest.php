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

use Claroline\MigrationBundle\Tests\MockeryTestCase;
use Claroline\MigrationBundle\Twig\SqlFormatterExtension;
use Mockery as m;
use org\bovigo\vfs\vfsStream;

class WriterTest extends MockeryTestCase
{
    private $twigEnvironment;
    private $fileSystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->twigEnvironment = m::mock('Twig\Environment');
        $this->fileSystem = m::mock('Symfony\Component\Filesystem\Filesystem');
    }

    public function testWriteMigrationClasses()
    {
        vfsStream::setup(
            'root',
            null,
            [
                'bundle' => [
                    'path' => [
                        'Installation' => [
                            'Migrations' => [
                                'some_driver' => [
                                    'Versionsome_version.php' => '',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->twigEnvironment->shouldReceive('addExtension')->once()->with(
            m::on(
                function ($argument) {
                    return $argument instanceof SqlFormatterExtension;
                }
            )
        );

        $bundlePath = vfsStream::url('root').'/bundle/path';

        $bundle = m::mock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $bundle->shouldReceive('getPath')->once()->andReturn($bundlePath);
        $this->fileSystem->shouldReceive('exists')
            ->once()
            ->with(implode(DIRECTORY_SEPARATOR, [$bundlePath, 'Installation', 'Migrations', 'some_driver']))
            ->andReturn(false);
        $this->fileSystem->shouldReceive('mkdir')
            ->once()
            ->with(implode(DIRECTORY_SEPARATOR, [$bundlePath, 'Installation', 'Migrations', 'some_driver']));
        $this->twigEnvironment->shouldReceive('render')
            ->once()
            ->with(
                '@ClarolineMigration/migration_class.html.twig',
                [
                    'namespace' => 'Bundle\Namespace\Installation\Migrations\some_driver',
                    'class' => 'Versionsome_version',
                    'upQueries' => 'queries up',
                    'downQueries' => 'queries down',
                ]
            )
            ->andReturn('migration class content');
        $this->fileSystem->shouldReceive('touch')
            ->once()
            ->with(implode(DIRECTORY_SEPARATOR, [$bundlePath, 'Installation', 'Migrations', 'some_driver', 'Versionsome_version.php']));

        $writer = new Writer($this->fileSystem, $this->twigEnvironment);
        $writer->writeMigrationClass(
            $bundle,
            'some_driver',
            'Bundle\Namespace\Installation\Migrations\some_driver\Versionsome_version',
            [Generator::QUERIES_UP => 'queries up', Generator::QUERIES_DOWN => 'queries down']
        );
        $this->assertEquals(
            'migration class content',
            file_get_contents(vfsStream::url('root').'/bundle/path/Installation/Migrations/some_driver/Versionsome_version.php')
        );
    }

    public function testDeleteUpperMigrations()
    {
        vfsStream::setup(
            'root',
            null,
            [
                'bundle' => [
                    'path' => [
                        'Installation' => [
                            'Migrations' => [
                                'some_driver' => [
                                    'Version1.php' => '',
                                    'Version2.php' => '',
                                    'Version3.php' => '',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->twigEnvironment->shouldReceive('addExtension');
        $bundle = m::mock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $bundlePath = vfsStream::url('root').'/bundle/path';

        $bundle->shouldReceive('getPath')->once()->andReturn($bundlePath);

        $this->fileSystem->shouldReceive('remove')
            ->once()
            ->with([implode(DIRECTORY_SEPARATOR, [$bundlePath, 'Installation', 'Migrations', 'some_driver', 'Version2.php'])]);

        $this->fileSystem->shouldReceive('remove')
            ->once()
            ->with([implode(DIRECTORY_SEPARATOR, [$bundlePath, 'Installation', 'Migrations', 'some_driver', 'Version3.php'])]);

        $writer = new Writer($this->fileSystem, $this->twigEnvironment);
        $deletedVersions = $writer->deleteUpperMigrationClasses($bundle, 'some_driver', 'Version1');
        $this->assertEquals(2, count($deletedVersions));
    }
}
