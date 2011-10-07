<?php
namespace Claroline\InstallBundle\Service;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CoreInstaller
{
    /* @var Kernel */
    private $kernel;
    
    /* @var BundleMigrator */
    private $migrator;
    
    
    
    public function __construct(Kernel $kernel, BundleMigrator $migrator)
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
        $bundles = $this->getRegisteredBundles();
        foreach($bundles as $bundle)
        {
            $this->migrator->createSchemaForBundle($bundle);
        }
    }
    
    private function dropCoreSchema()
    {
        $bundles = $this->getRegisteredBundles();
        $bundles_reversed = array_reverse($bundles);
        foreach($bundles_reversed as $bundle)
        {
            $this->migrator->dropSchemaForBundle($bundle);
        }
    }
    
    private function getRegisteredBundles()
    {
        return $this->kernel->getBundles();
    }
    
    
}
