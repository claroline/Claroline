<?php

namespace Claroline\CoreBundle\Library\Installation\Core;

use Symfony\Component\HttpKernel\Kernel;
use Claroline\MigrationBundle\Migrator\Migrator;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.install.core_installer")
 */
class Installer
{
    private $kernel;
    private $migrator;

    /**
     * @DI\InjectParams({
     *     "kernel" = @DI\Inject("kernel"),
     *     "migrator" = @DI\Inject("claroline.migration.migrator")
     * })
     */
    public function __construct(Kernel $kernel, Migrator $migrator)
    {
        $this->kernel = $kernel;
        $this->migrator = $migrator;
    }

    public function install()
    {
        $this->createCoreSchema();
    }

    public function uninstall()
    {
        $this->dropCoreSchema();
    }

    private function createCoreSchema()
    {
        $bundles = $this->getRegisteredCoreBundles();

        foreach ($bundles as $bundle) {
            $this->migrator->migrate($bundle, Migrator::VERSION_FARTHEST, Migrator::DIRECTION_UP);
        }
    }

    private function dropCoreSchema()
    {
        $bundles = $this->getRegisteredCoreBundles();

        foreach ($bundles as $bundle) {
            $this->migrator->migrate($bundle, Migrator::VERSION_FARTHEST, Migrator::DIRECTION_DOWN);
        }
    }

    private function getRegisteredCoreBundles()
    {
        $bundles = $this->kernel->getBundles();
        $coreBundles = array();

        foreach ($bundles as $bundle) {
            if (strpos($bundle->getPath(), 'core') !== false) {
                $coreBundles[] = $bundle;
            }
        }

        return $coreBundles;
    }
}
