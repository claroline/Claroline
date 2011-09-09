<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Claroline\PluginBundle\Service\PluginManager\Exception\ConfigurationException;

class Manager
{
    private $validator;
    private $fileHandler;
    private $databaseHandler;

    public function __construct(Validator $validator, 
                                FileHandler $fileHandler,
                                DatabaseHandler $databaseHandler)
    {
        $this->validator = $validator;
        $this->fileHandler = $fileHandler;
        $this->databaseHandler = $databaseHandler;
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
        $this->databaseHandler->remove($pluginFQCN);
    }

    // TODO : turn this method into a complete installation check
    public function isInstalled($pluginFQCN)
    {
        return $this->databaseHandler->isRegistered($pluginFQCN);
    }
}