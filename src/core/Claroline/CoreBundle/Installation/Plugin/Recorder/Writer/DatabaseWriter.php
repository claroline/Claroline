<?php

namespace Claroline\CoreBundle\Installation\Plugin\Recorder\Writer;

use Symfony\Component\Validator\Validator;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Plugin\ClarolinePlugin;
use Claroline\CoreBundle\Plugin\ClarolineTool;
use Claroline\CoreBundle\Plugin\ClarolineExtension;
use Claroline\CoreBundle\Entity\Tool;
use Claroline\CoreBundle\Entity\Extension;
use Claroline\CoreBundle\Exception\InstallationException;

class DatabaseWriter
{
    private $validator;
    private $em;
    
    public function __construct(Validator $validator, EntityManager $em)
    {
        $this->validator = $validator;
        $this->em = $em;
    }

    public function insert(ClarolinePlugin $plugin)
    {
        if ($plugin instanceof ClarolineTool)
        {
            $pluginEntity = $this->prepareToolEntity($plugin);
        }
        elseif ($plugin instanceof ClarolineExtension)
        {
            $pluginEntity = $this->prepareExtensionEntity($plugin);
        }
        
        $pluginEntity->setBundleFQCN(get_class($plugin));
        $pluginEntity->setType($plugin->getType());
        $pluginEntity->setVendorName($plugin->getVendorName());
        $pluginEntity->setBundleName($plugin->getBundleName());
        $pluginEntity->setNameTranslationKey($plugin->getNameTranslationKey());
        $pluginEntity->setDescriptionTranslationKey($plugin->getDescriptionTranslationKey());
        
        $errors = $this->validator->validate($pluginEntity);

        if (count($errors) > 0) 
        {
            $pluginFQCN = get_class($plugin);
            
            throw new InstallationException(
                "The plugin entity for '{$pluginFQCN}' cannot be validated. " 
                . "Validation errors : {$errors->__toString()}.",
                InstallationException::ENTIY_VALIDATION_ERROR
            );
        }

        $this->em->persist($pluginEntity);
        $this->em->flush();
    }

    public function delete($pluginFQCN)
    {
        $plugin = $this->getPluginEntity($pluginFQCN);
        // Complete deletion of all plugin db dependencies 
        // is made via cascade mechanism
        $this->em->remove($plugin);
        $this->em->flush();
    }
    
    public function isSaved($pluginFQCN)
    {        
        if ($this->getPluginEntity($pluginFQCN) !== null)
        {
            return true;
        }
        
        return false;
    }
    
    private function prepareToolEntity(ClarolineTool $tool)
    {
        return new Tool();
    }

    private function prepareExtensionEntity(ClarolineExtension $extension)
    {
        return new Extension();
    }
    
    private function getPluginEntity($pluginFQCN)
    {
        return $this->em
            ->getRepository('Claroline\CoreBundle\Entity\Plugin')
            ->findOneByBundleFQCN($pluginFQCN);
    }
}