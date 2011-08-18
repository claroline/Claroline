<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Doctrine\ORM\EntityManager;
use Claroline\PluginBundle\Entity\Plugin;
use Claroline\PluginBundle\Service\PluginManager\Exception\ConfigurationException;

class Manager
{
    protected $validator;
    protected $config;
    protected $repository;

    public function __construct(Validator $validator, ConfigurationHandler $configHandler, EntityManager $em)
    {
        $this->validator = $validator;
        $this->config = $configHandler;
        $this->repository = $em->getRepository('Claroline\PluginBundle\Entity\Plugin');
    }

    public function install($pluginFQCN)
    {
        if ($this->isInstalled($pluginFQCN))
        {
            throw new ConfigurationException("The plugin '{$pluginFQCN}' is already installed.");
        }

        $this->validator->check($pluginFQCN);

        $plugin = new $pluginFQCN;

        $this->config->registerNamespace($plugin->getVendorNamespace());
        $this->config->addInstantiableBundle($pluginFQCN);
        $this->config->importRoutingResources($pluginFQCN, $plugin->getRoutingResourcesPaths());

        $this->repository->createPlugin($pluginFQCN,
                                        $plugin->getType(),
                                        $plugin->getVendorNamespace(),
                                        $plugin->getBundleName(),
                                        $plugin->getNameTranslationKey(),
                                        $plugin->getDescriptionTranslationKey());

        // install plugin tables
        // load plugin fixtures*/
    }

    public function remove($pluginFQCN)
    {
        if (! $this->isInstalled($pluginFQCN))
        {
            throw new ConfigurationException("There is no '{$pluginFQCN}' plugin installed.");
        }

        $plugin = new $pluginFQCN;

        if (! in_array($plugin->getVendorNamespace(), $this->config->getSharedVendorNamespaces()))
        {
            $this->config->removeNamespace($plugin->getVendorNamespace());
        }
        
        $this->config->removeInstantiableBundle($pluginFQCN);
        $this->config->removeRoutingResources($pluginFQCN);
        $this->repository->deletePlugin($pluginFQCN);
    }

    public function isInstalled($pluginFQCN)
    {
        $plugins = $this->repository->findByBundleFQCN($pluginFQCN);

        if (count($plugins) === 0)
        {
            return false;
        }
      
        return true;
    }
}