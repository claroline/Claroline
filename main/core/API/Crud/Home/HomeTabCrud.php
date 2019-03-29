<?php

namespace Claroline\CoreBundle\API\Crud\Home;

use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.crud.home_tab")
 * @DI\Tag("claroline.crud")
 */
class HomeTabCrud
{
    /**
     * @DI\InjectParams({
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "containerCrud"  = @DI\Inject("claroline.crud.widget_container")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(
      ObjectManager $om,
      WidgetContainerCrud $containerCrud
    ) {
        $this->om = $om;
        $this->containerCrud = $containerCrud;
    }

    /**
     * @DI\Observe("crud_pre_delete_object_claroline_corebundle_entity_tab_hometab")
     *
     * @param DeleteEvent $event
     */
    public function preDelete(DeleteEvent $event)
    {
        $homeTab = $event->getObject();

        foreach ($homeTab->getWidgetContainers() as $container) {
            $this->containerCrud->delete($container);
            $this->om->remove($container);
        }

        foreach ($homeTab->getHomeTabConfigs() as $config) {
            $this->om->remove($config);
        }

        $this->om->flush();
    }
}
