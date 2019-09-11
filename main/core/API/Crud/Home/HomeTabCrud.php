<?php

namespace Claroline\CoreBundle\API\Crud\Home;

use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Persistence\ObjectManager;

class HomeTabCrud
{
    /**
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
