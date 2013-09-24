<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.widget_manager")
 */
class WidgetManager
{
    private $om;
    private $repo;
    private $widgetRepo;
    
    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        ObjectManager $om
    )
    {
        $this->om = $om;
        $this->repo = $om->getRepository('ClarolineCoreBundle:Widget\WidgetInstance');
        $this->widgetRepo = $om->getRepository('ClarolineCoreBundle:Widget\Widget');
    } 
    
    public function getDesktopInstances(User $user)
    {
        return  $this->repo->findBy(array('user' => $user));
    }
    
    public function getWorkspaceInstances(AbstractWorkspace $workspace)
    {
        return  $this->repo->findBy(array('workspace' => $workspace));
    }
    
    public function getAll()
    {
        return  $this->widgetRepo->findAll();
    }
    
    public function createInstance(Widget $widget, $isAdmin, $isDesktop, User $user = null, AbstractWorkspace $ws = null)
    {
        if (!$widget->isDisplayableInDesktop()) {
            if ($isDesktop || $user) {
                throw new \Exception("This widget doesn't support the desktop");
            }
        }
        
        if (!$widget->isDisplayableInWorkspace()) {
            if (!$isDesktop || $ws) {
                throw new \Exception("This widget doesn't support the workspace");
            }
        }
        
        $instance = new WidgetInstance($widget);
        $instance->setName($widget->getName());
        $instance->setIsAdmin($isAdmin);
        $instance->setIsDesktop($isDesktop);
        $instance->setWidget($widget);
        $instance->setUser($user);
        $instance->setWorkspace($ws);
        $this->om->persist($instance);
        $this->om->flush();
        
        return $instance;
    }
    
    public function removeInstance(WidgetInstance $widgetInstance)
    {
        $this->om->remove($widgetInstance);
        $this->om->flush();
    }
    
    public function getDesktopWidgets()
    {
        return $this->widgetRepo->findBy(array('isDisplayableInDesktop' => true));
    }
    
    public function getWorkspaceWidgets()
    {
        return $this->widgetRepo->findBy(array('isDisplayableInWorkspace' => true));
    }
}
