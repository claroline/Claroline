<?php

namespace Claroline\PluginBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\PluginBundle\Entity\Plugin;
use Claroline\PluginBundle\Repository\Exception\ModelException;

class PluginRepository extends EntityRepository
{
    public function createPlugin(Plugin $plugin)
    {     
        $bundleFQCN = $plugin->getBundleFQCN();
        $plugins = $this->findByBundleFQCN($bundleFQCN);
        
        if (count($plugins) > 0)
        {
            throw new ModelException("There's already a registered '{$bundleFQCN}' plugin.");
        }

        $this->_em->persist($plugin);
        $this->_em->flush();
    }

    public function deletePlugin($bundleFQCN)
    {
        $plugin = $this->findOneByBundleFQCN($bundleFQCN);

        $this->_em->remove($plugin);
        $this->_em->flush();
    }
}