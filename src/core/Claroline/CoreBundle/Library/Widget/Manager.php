<?php

namespace Claroline\CoreBundle\Library\Widget;

use Claroline\CoreBundle\Entity\Widget\DisplayConfig;

class Manager
{
    private $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    /**
     * Generate the the configuration of every widget of the current workspace.
     * If the configuration was never defined before, a "fake" one is created (lvl1)
     * wich can be persisted if you want to create it.
     *
     * @param type $workspaceId
     * @return type
     */
    public function generateWorkspaceDisplayConfig($workspaceId)
    {
        $workspace = $this->em->getRepository('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace')->find($workspaceId);

        $workspaceConfigs = $this->setEntitiesArrayKeysAsIds($this->em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findBy(array('workspace' => $workspace)));
        $adminConfigs = $this->setEntitiesArrayKeysAsIds($this->em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findBy(array('parent' => null, 'isDesktop' => false)));
/*
        foreach ($workspaceConfigs as $workspaceConfig) {
            if (!$workspaceConfig->getParent()->isLocked()) {
                unset($adminConfigs[$workspaceConfig->getParent()->getId()]);
            } else {
                unset($workspaceConfigs[$workspaceConfig->getId()]);
            }
        }

        $childConfigs = array();

        foreach ($adminConfigs as $adminConfig) {
            $childConfigs[] = $this->generateChild($adminConfig);
        }

        $configs = array_merge($workspaceConfigs, $childConfigs);
*/
        return $this->mergeConfigs($adminConfigs, $workspaceConfigs);
    }

    public function generateDesktopDisplayConfig($userId)
    {
        $user = $this->em->getRepository('Claroline\CoreBundle\Entity\User')->find($userId);

        $userConfigs = $this->setEntitiesArrayKeysAsIds($this->em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findBy(array('user' => $user)));
        $adminConfigs = $this->setEntitiesArrayKeysAsIds($this->em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findBy(array('parent' => null, 'isDesktop' => true)));

        return $this->mergeConfigs($adminConfigs, $userConfigs);
    }

    public function generateDisplayConfig($widgetId, $workspaceId)
    {
        $wsConfig = $this->em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findOneBy(array('workspace' => $workspaceId, 'widget' => $widgetId));
        $adminConfig = $this->em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findOneBy(array('parent' => null, 'widget' => $widgetId, 'isDesktop' => false));

        if($wsConfig != null){
            if($wsConfig->getParent()->isLocked()){
                return $adminConfig;
            } else {
                return $wsConfig;
            }
        } else {
            return $adminConfig;
        }
    }

    public function isDefaultConfig($widgetId, $workspaceId)
    {
        $dconfig = $this->generateDisplayConfig($widgetId, $workspaceId);
        $bool = true;
        ($dconfig->getLvl() == DisplayConfig::ADMIN_LEVEL && $dconfig->isLocked()) ? $bool = true: $bool = false;

        return $bool;
    }

    private function setEntitiesArrayKeysAsIds($array)
    {
        $tmpArray = array();
        foreach ($array as $item){
            $tmpArray[$item->getId()] = $item;
        }

        return $tmpArray;
    }

    private function generateChild($config)
    {
        $childConfig = new DisplayConfig();
        $childConfig->setParent($config);
        $childConfig->setVisible($config->isVisible());
        $childConfig->setWidget($config->getWidget());
        $childConfig->setLock($config->isLocked());
        $lvl = $config->getLvl();
        $lvl++;
        $childConfig->setLvl($lvl);

        return $childConfig;
    }

    private function mergeConfigs($adminConfigs, $childConfigs)
    {
        foreach ($childConfigs as $childConfig) {
            if (!$childConfig->getParent()->isLocked()) {
                unset($adminConfigs[$childConfig->getParent()->getId()]);
            } else {
                unset($childConfigs[$childConfig->getId()]);
            }
        }

        $generatedConfigs = array();

        foreach ($adminConfigs as $adminConfig) {
            $generatedConfigs[] = $this->generateChild($adminConfig);
        }

        $configs = array_merge($childConfigs, $generatedConfigs);

        return $configs;
    }
}

