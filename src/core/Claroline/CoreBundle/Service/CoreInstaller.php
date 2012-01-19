<?php

namespace Claroline\CoreBundle\Service;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CoreInstaller
{
    /** @var Kernel */
    private $kernel;
    
    /** @var BundleMigrator */
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
        $bundles = $this->getRegisteredCoreBundles();
        
        foreach ($bundles as $bundle)
        {
            $this->migrator->createSchemaForBundle($bundle);
        }
    }
    
    private function dropCoreSchema()
    {
        $bundles = $this->getRegisteredCoreBundles();
        $bundlesReversed = array_reverse($bundles);
        
        foreach ($bundlesReversed as $bundle)
        {            
            $this->migrator->dropSchemaForBundle($bundle);
        }
    }
    
    private function getRegisteredCoreBundles()
    {
        $allBundles = $this->kernel->getBundles();
        $indexedCoreBundles = array();
        
        foreach ($allBundles as $bundle)
        {
            if (strpos($bundle->getPath(), 'core') !== false)
            {
                $indexedCoreBundles[$bundle->getInstallationIndex()] = $bundle;
            }
        }
        
        ksort($indexedCoreBundles);
        
        return $indexedCoreBundles;
    }
}