<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Doctrine\ORM\EntityManager;
use Claroline\SecurityBundle\Service\RoleManager;
use Claroline\PluginBundle\AbstractType\ClarolinePlugin;
use Claroline\PluginBundle\AbstractType\ClarolineApplication;
use Claroline\PluginBundle\AbstractType\ClarolineTool;
use Claroline\PluginBundle\Entity\BasePlugin;
use Claroline\PluginBundle\Entity\Application;
use Claroline\PluginBundle\Entity\Tool;
use Claroline\PluginBundle\Entity\ApplicationLauncher;
use Claroline\SecurityBundle\Entity\Role;

class DatabaseHandler
{
    private $pluginRepo;
    private $roleManager;

    public function __construct(EntityManager $em, RoleManager $roleManager)
    {
        $this->pluginRepo = $em->getRepository('Claroline\PluginBundle\Entity\AbstractPlugin');
        $this->roleManager = $roleManager;
    }

    public function isRegistered($pluginFQCN)
    {
        $plugins = $this->pluginRepo->findByBundleFQCN($pluginFQCN);

        if (count($plugins) === 0)
        {
            return false;
        }

        return true;
    }

    public function install(ClarolinePlugin $plugin)
    {
        $pluginEntity = null;

        if (is_a($plugin, 'Claroline\PluginBundle\AbstractType\ClarolineApplication'))
        {
            $pluginEntity = $this->prepareApplicationEntity($plugin);
        }
        elseif (is_a($plugin, 'Claroline\PluginBundle\AbstractType\ClarolineTool'))
        {
            $pluginEntity = $this->prepareToolEntity($plugin);
        }
        elseif (is_a($plugin, 'Claroline\PluginBundle\AbstractType\ClarolinePlugin'))
        {
            $pluginEntity = new BasePlugin();
        }
        else
        {
            throw new \Exception("Unknown plugin type '" . get_parent_class($plugin) . "'.");
        }

        $pluginEntity->setBundleFQCN(get_class($plugin));
        $pluginEntity->setType($plugin->getType());
        $pluginEntity->setVendorName($plugin->getVendorNamespace());
        $pluginEntity->setBundleName($plugin->getBundleName());
        $pluginEntity->setNameTranslationKey($plugin->getNameTranslationKey());
        $pluginEntity->setDescriptionTranslationKey($plugin->getDescriptionTranslationKey());

        $this->pluginRepo->createPlugin($pluginEntity);

        // TODO : add a method call for tables installation and fixtures loading
    }

    public function remove($pluginFQCN)
    {
        // Complete deletion of all plugin db dependencies is made via cascade mechanism
        $this->pluginRepo->deletePlugin($pluginFQCN);
    }

    private function prepareApplicationEntity(ClarolineApplication $application)
    {
        $applicationEntity = new Application();
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
}