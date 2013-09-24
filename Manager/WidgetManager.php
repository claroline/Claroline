<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
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
}
