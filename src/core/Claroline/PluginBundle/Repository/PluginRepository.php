<?php

namespace Claroline\PluginBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\PluginBundle\Repository\Exception\ModelException;
use Claroline\PluginBundle\Entity\Plugin;

class PluginRepository extends EntityRepository
{
    public function createPlugin($bundleFQCN,
                                 $type,
                                 $vendorName,
                                 $bundleName,
                                 $nameTranslationKey,
                                 $descriptionTranslationKey)
    {
        $em = $this->getEntityManager();       
        $plugins = $this->findByBundleFQCN($bundleFQCN);
        
        if (count($plugins) > 0)
        {
            throw new ModelException("There's already a registered '{$bundleFQCN}' plugin.");
        }

        $plugin = new Plugin();
        $plugin->setType($type);
        $plugin->setBundleFQCN($bundleFQCN);
        $plugin->setVendorName($vendorName);
        $plugin->setBundleName($bundleName);
        $plugin->setNameTranslationKey($nameTranslationKey);
        $plugin->setDescriptionTranslationKey($descriptionTranslationKey);

        $em->persist($plugin);
        $em->flush();
    }

    public function deletePlugin($bundleFQCN)
    {
        $em = $this->getEntityManager();
        $plugin = $this->findOneByBundleFQCN($bundleFQCN);

        $em->remove($plugin);
        $em->flush();
    }
}