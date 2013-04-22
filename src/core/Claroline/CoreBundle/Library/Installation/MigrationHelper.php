<?php

namespace Claroline\CoreBundle\Library\Installation;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.install.migration_helper", public=false)
 */
class MigrationHelper
{
    public function getTablePrefixForBundle(Bundle $bundle)
    {
        $namespace = $bundle->getNamespace();

        $namespaceParts = explode('\\', $namespace);
        $bundleName = array_pop($namespaceParts); //remove bundle name
        $vendor = array_pop($namespaceParts);

        return strtolower("{$vendor}_{$bundleName}");
    }

    public function getTablePrefixForMigration(BundleMigration $migration)
    {
        $class = new \ReflectionClass($migration);
        $namespace = $class->getNamespaceName();
        $namespaceParts = explode("\\", $namespace);

        array_pop($namespaceParts); // remove the Migrations part
        $bundle = array_pop($namespaceParts);
        $vendor = array_pop($namespaceParts);

        $bundleClass = "{$vendor}\\{$bundle}\\{$vendor}{$bundle}";
        $bundle = new $bundleClass();

        return $this->getTablePrefixForBundle($bundle);
    }
}