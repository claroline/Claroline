<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Claroline\PluginBundle\Service\PluginManager\Exception\ConfigurationException;

class Manager
{
    private $validator;
    private $fileHandler;
    private $databaseHandler;
    private $migrationsHandler;

    public function __construct(Validator $validator, 
                                FileHandler $fileHandler,
                                DatabaseHandler $databaseHandler,
                                MigrationsHandler $migrationsHandler)
    {
        $this->validator = $validator;
        $this->fileHandler = $fileHandler;
        $this->databaseHandler = $databaseHandler;
        $this->migrationsHandler = $migrationsHandler;
    }
    
    public function install($pluginFQCN)
    {
        if ($this->isInstalled($pluginFQCN))
        {
            throw new ConfigurationException("The plugin '{$pluginFQCN}' is already installed.");
        }

        $this->validator->check($pluginFQCN);

        $plugin = new $pluginFQCN;
        
                
        $this->fileHandler->registerNamespace($plugin->getVendorNamespace());
        $this->fileHandler->addInstantiableBundle($pluginFQCN);
        $this->fileHandler->importRoutingResources(
            $pluginFQCN, 
            $plugin->getRoutingResourcesPaths(),
            $plugin->getRoutingPrefix()
        );
        
        $this->migrationsHandler->install($plugin);
        $this->databaseHandler->install($plugin);
        $this->validator->setRegisteredRoutingPrefixes(
            $this->fileHandler->getRegisteredRoutingPrefixes()
        );
    }

    public function remove($pluginFQCN)
    {
        if (! $this->isInstalled($pluginFQCN))
        {
            throw new ConfigurationException("There is no '{$pluginFQCN}' plugin installed.");
        }

        $plugin = new $pluginFQCN;

        $this->fileHandler->removeNamespace($plugin->getVendorNamespace());
        $this->fileHandler->removeInstantiableBundle($pluginFQCN);
        $this->fileHandler->removeRoutingResources($pluginFQCN);
        $this->migrationsHandler->remove($plugin);
        $this->databaseHandler->remove($pluginFQCN);
    }

    // TODO : turn this method into a complete installation check
    public function isInstalled($pluginFQCN)
    {
        return $this->databaseHandler->isRegistered($pluginFQCN);
    }
}