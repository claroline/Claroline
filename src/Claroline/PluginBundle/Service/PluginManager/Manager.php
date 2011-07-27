<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Doctrine\ORM\EntityManager;
use Claroline\PluginBundle\Entity\Plugin;

class Manager
{
    protected $validator;
    protected $writer;
    protected $em;

    public function __construct(Validator $validator, FileWriter $writer, EntityManager $em)
    {
        $this->validator = $validator;
        $this->writer = $writer;
        $this->em = $em;
    }

    public function install($pluginFQCN)
    {
        $this->validator->check($pluginFQCN);

        $plugin = new $pluginFQCN;

        $this->writer->registerNamespace($plugin->getVendorNamespace());
        $this->writer->addInstantiableBundle($pluginFQCN);

        /*
        $this->writer->importRoutingResource();

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

        if (! in_array($plugin->getVendorNamespace(), $this->writer->getSharedVendorNamespaces()))
        {
            $this->writer->removeNamespace($plugin->getVendorNamespace());
        }
        
        $this->writer->removeInstantiableBundle($pluginFQCN);
    }

    public function isInstalled($pluginFQCN)
    {
        throw new \Exception('Not implemented yet.');
    }
}