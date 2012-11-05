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

    public function generateWorkspaceDisplayConfig($workspaceId)
    {
        $workspace = $this->em->getRepository('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace')->find($workspaceId);

        $workspaceConfigs = $this->setEntitiesArrayKeysAsIds($this->em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findBy(array('workspace' => $workspace)));
        $adminConfigs = $this->setEntitiesArrayKeysAsIds($this->em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findBy(array('parent' => null)));

        foreach ($workspaceConfigs as $workspaceConfig) {
            if (!$workspaceConfig->getParent()->isLocked()) {
                unset($adminConfigs[$workspaceConfig->getParent()->getId()]);
            } else {
                unset($workspaceConfigs[$workspaceConfig->getId()]);
            }
        }

        $childConfigs = array();

        foreach ($adminConfigs as $adminConfig) {
            $childConfig = new DisplayConfig();
            $childConfig->setParent($adminConfig);
            $childConfig->setVisible($adminConfig->isVisible());
            $childConfig->setWidget($adminConfig->getWidget());
            $childConfigs[] = $childConfig;
        }

        $configs = array_merge($workspaceConfigs, $childConfigs);

        return $configs;
    }

    private function setEntitiesArrayKeysAsIds($array)
    {
        $tmpArray = array();
        foreach ($array as $item){
            $tmpArray[$item->getId()] = $item;
        }

        return $tmpArray;
    }

}

