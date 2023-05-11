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
    private Filesystem $fileSystem;

    private Environment $twigEnvironment;

    private bool $hasSqlExtension = false;

    public function __construct(
        Filesystem $fileSystem,
        Environment $environment
    ) {
        $this->fileSystem = $fileSystem;
        $this->twigEnvironment = $environment;
    }

    /**
     * Writes a bundle migration class for a given driver.
     */
    public function writeMigrationClass(BundleInterface $bundle, string $driverName, string $className, array $queries): void
    {
        if (!$this->hasSqlExtension) {
            $this->twigEnvironment->addExtension(new SqlFormatterExtension());
            $this->hasSqlExtension = true;
        }

        $versionParts = explode('\\', $className);

        $class = array_pop($versionParts);
        $namespace = implode('\\', $versionParts);
        $targetDir = implode(DIRECTORY_SEPARATOR, [$bundle->getPath(), 'Installation', 'Migrations', $driverName]);

        $classFile = implode(DIRECTORY_SEPARATOR, [$targetDir, $class.'.php']);

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
     */
    public function deleteUpperMigrationClasses(BundleInterface $bundle, string $driverName, string $referenceVersion): array
    {
        $versionParts = explode('\\', $referenceVersion);
        $currentVersion = array_pop($versionParts);

        $migrations = new \DirectoryIterator(implode(DIRECTORY_SEPARATOR, [$bundle->getPath(), 'Installation', 'Migrations', $driverName]));

        $deletedVersions = [];
        foreach ($migrations as $migration) {
            $matches = [];
            if (preg_match('#(.+)\.php#', $migration->getFilename(), $matches)) {
                if ($matches[1] > $currentVersion) {
                    $this->fileSystem->remove([$migration->getPathname()]);
                    $deletedVersions[] = $migration->getFilename();
                }
            }
        }

        return $deletedVersions;
    }
}
