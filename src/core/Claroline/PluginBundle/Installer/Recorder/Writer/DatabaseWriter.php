<?php

namespace Claroline\PluginBundle\Installer\Recorder\Writer;

use Symfony\Component\Validator\Validator;
use Doctrine\ORM\EntityManager;
use Claroline\SecurityBundle\Service\RoleManager;
use Claroline\PluginBundle\AbstractType\ClarolinePlugin;
use Claroline\PluginBundle\AbstractType\ClarolineExtension;
use Claroline\PluginBundle\AbstractType\ClarolineApplication;
use Claroline\PluginBundle\AbstractType\ClarolineTool;
use Claroline\PluginBundle\Entity\Extension;
use Claroline\PluginBundle\Entity\Application;
use Claroline\PluginBundle\Entity\Tool;
use Claroline\PluginBundle\Entity\ApplicationLauncher;
use Claroline\SecurityBundle\Entity\Role;
use Claroline\PluginBundle\Exception\InstallationException;

class DatabaseWriter
{
    private $validator;
    private $em;
    private $roleManager;    
    
    public function __construct(Validator $validator, EntityManager $em, RoleManager $roleManager)
    {
        $this->validator = $validator;
        $this->em = $em;
        $this->roleManager = $roleManager;
    }

    public function insert(ClarolinePlugin $plugin)
    {
        if (is_a($plugin, 'Claroline\PluginBundle\AbstractType\ClarolineExtension'))
        {
            $pluginEntity = $this->prepareExtensionEntity($plugin);
        }
        elseif (is_a($plugin, 'Claroline\PluginBundle\AbstractType\ClarolineApplication'))
        {
            $pluginEntity = $this->prepareApplicationEntity($plugin);
        }
        elseif (is_a($plugin, 'Claroline\PluginBundle\AbstractType\ClarolineTool'))
        {
            $pluginEntity = $this->prepareToolEntity($plugin);
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
                . "Validation errors : {$errors->__toString()}." 
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

    private function prepareExtensionEntity(ClarolineExtension $extension)
    {
        return new Extension();
    }
    
    private function prepareApplicationEntity(ClarolineApplication $application)
    {
        $applicationEntity = new Application();
        $applicationEntity->setIndexRoute($application->getIndexRoute());
        $applicationEntity->setEligibleForPlatformIndex(
            $application->isEligibleForPlatformIndex()
        );
        $applicationEntity->setEligibleForConnectionTarget(
            $application->isEligibleForConnectionTarget()
        );
        $launchers = $application->getLaunchers();

        foreach ($launchers as $launcherWidget)
        {
            $launcher = new ApplicationLauncher();
            $launcher->setApplication($applicationEntity);
            $launcher->setRouteId($launcherWidget->getRouteId());
            $launcher->setTranslationKey($launcherWidget->getTranslationKey());

            foreach ($launcherWidget->getAccessRoles() as $roleName)
            {
                $role = $this->roleManager->getRole($roleName, RoleManager::CREATE_IF_NOT_EXISTS);
                $launcher->addAccessRole($role);
            }

            $applicationEntity->addLauncher($launcher);
        }

        return $applicationEntity;
    }

    private function prepareToolEntity(ClarolineTool $tool)
    {
        return new Tool();
    }
    
    private function getPluginEntity($pluginFQCN)
    {
        return $this->em
            ->getRepository('Claroline\PluginBundle\Entity\Plugin')
            ->findOneByBundleFQCN($pluginFQCN);
    }
}