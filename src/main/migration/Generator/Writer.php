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

use Claroline\MigrationBundle\Twig\SqlFormatterExtension;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Twig\Environment;

/**
 * Class responsible for writing bundle migration queries in a migration class file.
 */
class Writer
{
    /** @var Filesystem */
    private $fileSystem;
    /** @var Environment */
    private $twigEnvironment;
    /** @var bool */
    private $hasSqlExtension = false;

    /**
     * Writer constructor.
     */
    public function __construct(
        Filesystem $fileSystem,
        Environment $environment
    ) {
        $this->fileSystem = $fileSystem;
        $this->twigEnvironment = $environment;
    }

    /**
     * Writes a bundle migration class for a given driver.
     *
     * @param string $driverName
     * @param string $version
     */
    public function writeMigrationClass(BundleInterface $bundle, $driverName, $version, array $queries)
    {
        if (!$this->hasSqlExtension) {
            $this->twigEnvironment->addExtension(new SqlFormatterExtension());
            $this->hasSqlExtension = true;
        }

        $targetDir = implode(DIRECTORY_SEPARATOR, [$bundle->getPath(), 'Installation', 'Migrations', $driverName]);
        $class = "Version{$version}";
        $namespace = "{$bundle->getNamespace()}\\Installation\\Migrations\\{$driverName}";
        $classFile = "{$targetDir}/{$class}.php";

        if (!$this->fileSystem->exists($targetDir)) {
            $this->fileSystem->mkdir($targetDir);
        }

        $content = $this->twigEnvironment->render(
            '@ClarolineMigration/migration_class.html.twig',
            [
                'namespace' => $namespace,
                'class' => $class,
                'upQueries' => $queries[Generator::QUERIES_UP],
                'downQueries' => $queries[Generator::QUERIES_DOWN],
            ]
        );

        $this->fileSystem->touch($classFile);
        file_put_contents($classFile, $content);
    }

    /**
     * Deletes bundle migration classes for a given driver which are above a
     * reference version.
     *
     * @param string $driverName
     * @param string $referenceVersion
     *
     * @return array The migration files that were deleted
     */
    public function deleteUpperMigrationClasses(BundleInterface $bundle, $driverName, $referenceVersion)
    {
        $migrations = new \DirectoryIterator(implode(DIRECTORY_SEPARATOR, [$bundle->getPath(), 'Installation', 'Migrations', $driverName]));
        $deletedVersions = [];

        foreach ($migrations as $migration) {
            $matches = [];
            if (preg_match('#Version(\d+)\.php#', $migration->getFilename(), $matches)) {
                if ($matches[1] > $referenceVersion) {
                    $this->fileSystem->remove([$migration->getPathname()]);
                    $deletedVersions[] = $migration->getFilename();
                }
            }
        }

        return $deletedVersions;
    }
}
