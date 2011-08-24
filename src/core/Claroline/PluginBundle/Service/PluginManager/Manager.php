<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Doctrine\ORM\EntityManager;
use Claroline\PluginBundle\AbstractType\ClarolinePlugin;
use Claroline\PluginBundle\Entity\AbstractPlugin;
use Claroline\PluginBundle\Entity\BasePlugin;
use Claroline\PluginBundle\Entity\Application;
use Claroline\PluginBundle\Entity\Tool;
use Claroline\PluginBundle\Entity\ApplicationLauncher;
use Claroline\SecurityBundle\Entity\Role;
use Claroline\PluginBundle\Service\PluginManager\Exception\ConfigurationException;

// TODO : put the database registration/unregistration logic into a dedicated object,
//        injected via the constructor

class Manager
{
    protected $validator;
    protected $config;
    protected $pluginRepo;
    protected $roleRepo;

    public function __construct(Validator $validator, ConfigurationHandler $configHandler, EntityManager $em)
    {
        $this->validator = $validator;
        $this->config = $configHandler;
        $this->pluginRepo = $em->getRepository('Claroline\PluginBundle\Entity\AbstractPlugin');
        $this->roleRepo = $em->getRepository('Claroline\SecurityBundle\Entity\Role');
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
        $this->doDatabaseRegistration($plugin);

        // install plugin tables
        // load plugin fixtures
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
        $this->pluginRepo->deletePlugin($pluginFQCN);
    }

    public function isInstalled($pluginFQCN)
    {
        $plugins = $this->pluginRepo->findByBundleFQCN($pluginFQCN);

        if (count($plugins) === 0)
        {
            return false;
        }
      
        return true;
    }

    private function doDatabaseRegistration(ClarolinePlugin $plugin)
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
    }

    private function prepareApplicationEntity($application)
    {
        $applicationEntity = new Application();
        $launchers = $application->getLaunchers();

        foreach ($launchers as $launcher)
        {
            $launcherEntity = new ApplicationLauncher();
            $launcherEntity->setApplication($applicationEntity);
            $launcherEntity->setRouteId($launcher->getRouteId());
            $launcherEntity->setTranslationKey($launcher->getTranslationKey());

            foreach ($launcher->getAccessRoles() as $role)
            {
                $roleEntity = null;
                $existingRole = $this->roleRepo->findOneByName($role);

                if ($existingRole == null)
                {
                    $roleEntity = new Role();
                    $roleEntity->setName($role);
                    $this->roleRepo->create($roleEntity);
                }
                else
                {
                    $roleEntity = $existingRole;
                }

                $launcherEntity->addAccessRole($roleEntity);
            }

            $applicationEntity->addLauncher($launcherEntity);
        }

        return $applicationEntity;
    }

    private function prepareToolEntity($tool)
    {
        return new Tool();
    }
}