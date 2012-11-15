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
     * If the configuration was never defined before, a temporary one is created (lvl1).
     * Temporaries config have their id set to NULL.
     *
     * @param type $workspaceId
     *
     * @return array
     */
    public function generateWorkspaceDisplayConfig($workspaceId)
    {
        $workspace = $this->em->getRepository('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace')->find($workspaceId);
        $workspaceConfigs = $this->setEntitiesArrayKeysAsIds($this->em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findBy(array('workspace' => $workspace)));
        $adminConfigs = $this->setEntitiesArrayKeysAsIds($this->em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findBy(array('parent' => null, 'isDesktop' => false)));

        return $this->mergeConfigs($adminConfigs, $workspaceConfigs);
    }

    /**
     * Generate the the configuration of every widget of the current user.
     * If the configuration was never defined before, a temporary one is created (lvl1).
     * Temporaries config have their id set to NULL.
     *
     * @param type $userId
     *
     * @return array
     */
    public function generateDesktopDisplayConfig($userId)
    {
        $user = $this->em->getRepository('Claroline\CoreBundle\Entity\User')->find($userId);

        $userConfigs = $this->setEntitiesArrayKeysAsIds($this->em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findBy(array('user' => $user)));
        $adminConfigs = $this->setEntitiesArrayKeysAsIds($this->em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findBy(array('parent' => null, 'isDesktop' => true)));

        return $this->mergeConfigs($adminConfigs, $userConfigs);
    }

    /**
     * Tells if the default config must be used (ie locked by the admin)
     * in a workspace for these parameters:
     * widgetId & workspaceId
     *
     * @param integer $widgetId
     * @param integer $workspaceId
     *
     * @return boolean
     */
    public function isWorkspaceDefaultConfig($widgetId, $workspaceId)
    {
        $dconfig = $this->getWorkspaceForcedConfig($widgetId, $workspaceId);
        $bool = true;
        ($dconfig->getLvl() == DisplayConfig::ADMIN_LEVEL && $dconfig->isLocked()) ? $bool = true: $bool = false;

        return $bool;
    }

    /**
     * Tells if the default config must be used (ie locked by the admin)
     * for a user for these parameters:
     * widgetId & workspaceId
     *
     * @param integer $widgetId
     * @param integer $userId
     *
     * @return boolean
     */
    public function isDesktopDefaultConfig($widgetId, $userId)
    {
        $dconfig = $this->getDesktopForcedConfig($widgetId, $userId);
        $bool = true;
        ($dconfig->getLvl() == DisplayConfig::ADMIN_LEVEL && $dconfig->isLocked()) ? $bool = true: $bool = false;

        return $bool;
    }

    private function getWorkspaceForcedConfig($widgetId, $workspaceId)
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

    private function getDesktopForcedConfig($widgetId, $userId)
    {
        $user = $this->em->getRepository('Claroline\CoreBundle\Entity\User')->find($userId);
        $userConfig = $this->em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findOneBy(array('user' => $userId, 'widget' => $widgetId));
        $adminConfig = $this->em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findOneBy(array('parent' => null, 'widget' => $widgetId, 'isDesktop' => true));

        if($userConfig != null){
            if($userConfig->getParent()->isLocked()){
                return $adminConfig;
            } else {
                return $userConfig;
            }
        } else {
            return $adminConfig;
        }
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

