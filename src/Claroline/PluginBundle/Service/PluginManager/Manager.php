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

    public function install($pluginDirectory, $pluginFQCN)
    {
        $this->validator->check($pluginFQCN, $pluginDirectory);

        $plugin = new $pluginFQCN;

        $this->writer->registerNamespace($plugin->getNamespace());
        $this->writer->registerBundle($plugin->getFQCN());
        $this->writer->importRoutingResource();

        $pluginEntity = new Plugin();
        $pluginEntity->setName($xyz);
        // ...
        $this->em->persist($pluginEntity);
        $this->em->flush;

        // install plugin tables
        // load plugin fixtures
    }

    public function remove($pluginFQCN)
    {

    }

    public function isInstalled($pluginFQCN)
    {

    }
}