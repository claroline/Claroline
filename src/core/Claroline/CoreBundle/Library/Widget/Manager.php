<?php

namespace Claroline\CoreBundle\Library\Widget;

use Claroline\CoreBundle\Entity\Widget\DisplayConfig;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.widget.manager")
 */
class Manager
{
    private $em;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Generates the the configuration of every widget of the current workspace.
     * Widgets configuration works as a nested tree.
     * If the configuration was never defined before, a temporary one is created (lvl1)
     * with the parameters wich were defined by the admin .
     * Temporaries config have their id set to NULL.
     *
     * @param integer $workspaceId
     *
     * @return array
     */
    public function generateWorkspaceDisplayConfig($workspaceId)
    {
        $workspace = $this->em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
            ->find($workspaceId);
        $configRepo = $this->em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig');
        $workspaceConfigs = $this->setEntitiesArrayKeysAsIds(
            $configRepo->findBy(array('workspace' => $workspace))
        );
        $adminConfigs = $this->setEntitiesArrayKeysAsIds(
            $configRepo->findBy(array('parent' => null, 'isDesktop' => false))
        );

        return $this->mergeConfigs($adminConfigs, $workspaceConfigs);
    }

    /**
     * Generate the the configuration of every widget of the current user desktop.
     * If the configuration was never defined before, a temporary one is created (lvl1).
     * with the parameters wich were defined by the admin .
     * Temporaries config have their id set to NULL.
     *
     * @param type $userId
     *
     * @return array
     */
    public function generateDesktopDisplayConfig($userId)
    {
        $user = $this->em->getRepository('ClarolineCoreBundle:User')->find($userId);
        $configRepo = $this->em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig');
        $userConfigs = $this->setEntitiesArrayKeysAsIds(
            $configRepo->findBy(array('user' => $user))
        );
        $adminConfigs = $this->setEntitiesArrayKeysAsIds(
            $configRepo->findBy(array('parent' => null, 'isDesktop' => true))
        );

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

        return $dconfig->getParent() == null && $dconfig->isLocked();
    }

    /**
     * Tells if the default config must be used (ie locked by the admin)
     * for a user deskop for these parameters:
     * widgetId & userId
     *
     * @param integer $widgetId
     * @param integer $userId
     *
     * @return boolean
     */
    public function isDesktopDefaultConfig($widgetId, $userId)
    {
        $dconfig = $this->getDesktopForcedConfig($widgetId, $userId);

        return $dconfig->getParent() == null && $dconfig->isLocked();
    }

    /**
     * Gets the config in use for a widget in a workspace. If the admin locked
     * his choice or no workspace config were defined, the admin one will be returned.
     *
     * @param integer $widgetId
     * @param integer $workspaceId
     *
     * @return DisplayConfig
     */
    private function getWorkspaceForcedConfig($widgetId, $workspaceId)
    {
        $wsConfig = $this->em
            ->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
            ->findOneBy(array('workspace' => $workspaceId, 'widget' => $widgetId));
        $adminConfig = $this->em
            ->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
            ->findOneBy(array('parent' => null, 'widget' => $widgetId, 'isDesktop' => false));

        if ($wsConfig != null) {
            if ($wsConfig->getParent()->isLocked()) {
                return $adminConfig;
            }

            return $wsConfig;
        }

        return $adminConfig;
    }

    /**
     * Gets the config in use for a widget in a uyser desktop. If the admin locked
     * his choice or no desktop config were defined, the admin one will be returned.
     *
     * @param integer $widgetId
     * @param integer $userId
     *
     * @return DisplayConfig
     */
    private function getDesktopForcedConfig($widgetId, $userId)
    {
        $userConfig = $this->em
            ->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
            ->findOneBy(array('user' => $userId, 'widget' => $widgetId));
        $adminConfig = $this->em
            ->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
            ->findOneBy(array('parent' => null, 'widget' => $widgetId, 'isDesktop' => true));

        if ($userConfig != null) {
            if ($userConfig->getParent()->isLocked()) {
                return $adminConfig;
            }

            return $userConfig;
        }

        return $adminConfig;
    }

    /**
     * Given a Collection of entities, this method will return an array
     * of entities whose array keys are the entities ids.
     *
     * @param type $array
     *
     * @return array
     */
    private function setEntitiesArrayKeysAsIds($array)
    {
        $tmpArray = array();

        foreach ($array as $item) {
            $tmpArray[$item->getId()] = $item;
        }

        return $tmpArray;
    }

    /**
     * Generate a child similar to its parent.
     *
     * @param \Claroline\CoreBundle\Entity\Widget\DisplayConfig $config
     *
     * @return \Claroline\CoreBundle\Entity\Widget\DisplayConfig
     */
    private function generateChild($config)
    {
        $childConfig = new DisplayConfig();
        $childConfig->setParent($config);
        $childConfig->setVisible($config->isVisible());
        $childConfig->setWidget($config->getWidget());
        $childConfig->setLock($config->isLocked());

        return $childConfig;
    }

    /**
     * Merge the configs defined by the platform administrator and the configs
     * defined by a user. If the user didn't specicfy a config, the administrator
     * one will be copied.
     *
     * @param type $adminConfigs
     * @param type $childConfigs
     *
     * @return array
     */
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

