<?php
namespace Claroline\InstallBundle\Library\Migration;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MigrationHelper
{
    public function getTablePrefixForBundle(Bundle $bundle)
    {        
        
        $namespace = $bundle->getNamespace();
        
        $namespaceParts = explode('\\', $namespace);
        $bundleName = array_pop($namespaceParts); //remove bundle name
        $vendor = array_pop($namespaceParts);
        
        return "{$vendor}_{$bundleName}";
    }
    
    public function getTablePrefixForMigration(BundleMigration $migration)
    {        
        $klass = new \ReflectionClass($migration);
        $namespace = $klass->getNamespaceName();
        $namespaceParts = explode("\\", $namespace);
        
        array_pop($namespaceParts); // remove the Migrations part
        $bundle = array_pop($namespaceParts);
        $vendor = array_pop($namespaceParts);
        
        $bundleClass = "{$vendor}\\{$bundle}\\{$vendor}{$bundle}";
        
        $bundle = new $bundleClass();
        return $this->getTablePrefixForBundle($bundle);
    }
}
