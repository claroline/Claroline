<?php

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\WidgetManager;

/**
 * @DI\Service("claroline.manager.simple_text_manager")
 */
class SimpleTextManager
{
    private $om;
    private $widgetManager;

   /**
    * @DI\InjectParams({
    *       "om"            = @DI\Inject("claroline.persistence.object_manager"),
    *       "widgetManager" = @DI\Inject("claroline.manager.widget_manager")
    * })
    */
    public function __construct(ObjectManager $om, WidgetManager $widgetManager)
    {
        $this->om = $om;
        $this->widgetManager = $widgetManager;
    }

    public function getWorkspaceWidgetConfig(AbstractWorkspace $workspace)
    {
        return $this->om
            ->getRepository('ClarolineCoreBundle:Widget\SimpleTextWorkspaceConfig')
            ->findOneBy(array('workspace' => $workspace->getId(), 'isDefault' => false));
    }

    public function getDefaultWorkspaceWidgetConfig()
    {
        return $this->om
            ->getRepository('ClarolineCoreBundle:Widget\SimpleTextWorkspaceConfig')
            ->findOneBy(array('workspace' => null, 'isDefault' => true));
    }

    public function getDesktopWidgetConfig($user)
    {
        return $this->om
            ->getRepository('ClarolineCoreBundle:Widget\SimpleTextDesktopConfig')
            ->findOneBy(array('user' => $user, 'isDefault' => false));
    }

    public function getDefaultDesktopWidgetConfig()
    {
        return $this->om
            ->getRepository('ClarolineCoreBundle:Widget\SimpleTextDesktopConfig')
            ->findOneBy(array('isDefault' => true));
    }
    public function getDisplayedConfigForWorkspace(AbstractWorkspace $workspace)
    {
        $repo = $this->om->getRepository('ClarolineCoreBundle:Widget\SimpleTextWorkspaceConfig');
        $widget = $this->om->getRepository('ClarolineCoreBundle:Widget\Widget')
            ->findOneBy(array('name' => 'simple_text'));
        $config = $repo->findOneBy(array('workspace' => $workspace->getId()));

        $isDefaultConfig = $this->widgetManager->isWorkspaceDefaultConfig($widget->getId(), $workspace->getId());

        if ($isDefaultConfig || $config == null) {
            $config = $repo->findOneBy(array('isDefault' => true));
        }

        return $config;
    }

    public function getDisplayedConfigForDekstop(User $user)
    {
        $repo = $this->om->getRepository('ClarolineCoreBundle:Widget\SimpleTextDesktopConfig');
        $widget = $this->om->getRepository('ClarolineCoreBundle:Widget\Widget')
            ->findOneBy(array('name' => 'simple_text'));
        $config = $repo->findOneBy(array('user' => $user->getId()));

        $isDefaultConfig = $this->widgetManager->isDesktopDefaultConfig($widget->getId(), $user->getId());

        if ($isDefaultConfig || $config == null) {
            $config = $repo->findOneBy(array('isDefault' => true));
        }

        return $config;
    }

}