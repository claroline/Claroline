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
        $this->fileHandler->importRoutingResources($pluginFQCN, $plugin->getRoutingResourcesPaths());
        $this->databaseHandler->install($plugin);
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

    /**
     * Getter used in automated tests.
     */
    public function getFileHandler()
    {
        return $this->fileHandler;
    }

    /**
     * Setter used in automated tests, overwriting directory/file paths
     * injected by the DIC in the validator and the file handler.
     *
     * @param string $pluginDirectory Path of the directory plugins are living in.
     * @param string $pluginNamespacesFile Path of the file in which plugins namespaces are listed.
     * @param string $pluginBundlesFile Path of the file in which plugins FQCNs are listed.
     * @param string $pluginRoutingFile Path of the yaml file in which plugins routing
     *                                  resources informations are stored.
     */
    public function setFileSystemDependencies($pluginDirectory,
                                              $pluginNamespacesFile,
                                              $pluginBundlesFile,
                                              $pluginRoutingFile)
    {
        $this->validator->setPluginDirectory($pluginDirectory);
        $this->fileHandler->setPluginNamespacesFile($pluginNamespacesFile);
        $this->fileHandler->setPluginBundlesFile($pluginBundlesFile);
        $this->fileHandler->setPluginRoutingFile($pluginRoutingFile);
    }
}