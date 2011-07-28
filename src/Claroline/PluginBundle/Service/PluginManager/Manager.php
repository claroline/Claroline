<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Doctrine\ORM\EntityManager;
use Claroline\PluginBundle\Entity\Plugin;

class Manager
{
    protected $validator;
    protected $config;
    protected $em;

    public function __construct(Validator $validator, ConfigurationHandler $configHandler, EntityManager $em)
    {
        $this->validator = $validator;
        $this->config = $configHandler;
        $this->em = $em;
    }

    public function install($pluginFQCN)
    {
        $this->validator->check($pluginFQCN);

        $plugin = new $pluginFQCN;

        $this->config->registerNamespace($plugin->getVendorNamespace());
        $this->config->addInstantiableBundle($pluginFQCN);
        $this->config->importRoutingResources($pluginFQCN, $plugin->getRoutingResourcesPaths());

        /*
        $pluginEntity = new Plugin();
        $pluginEntity->setName($xyz);
        // ...
        $this->em->persist($pluginEntity);
        $this->em->flush;

        // install plugin tables
        // load plugin fixtures*/
    }

    public function remove($pluginFQCN)
    {
        $this->validator->check($pluginFQCN);

        $plugin = new $pluginFQCN;

        if (! in_array($plugin->getVendorNamespace(), $this->config->getSharedVendorNamespaces()))
        {
            $this->config->removeNamespace($plugin->getVendorNamespace());
        }
        
        $this->config->removeInstantiableBundle($pluginFQCN);
        $this->config->removeRoutingResources($pluginFQCN);
    }

    public function isInstalled($pluginFQCN)
    {
        throw new \Exception('Not implemented yet.');
    }
}