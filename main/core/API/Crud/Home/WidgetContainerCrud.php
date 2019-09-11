<?php

namespace Claroline\CoreBundle\API\Crud\Home;

use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Persistence\ObjectManager;

class WidgetContainerCrud
{
    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om, WidgetInstanceCrud $instanceCrud)
    {
        $this->om = $om;
        $this->instanceCrud = $instanceCrud;
    }

    /**
     * @param DeleteEvent $event
     */
    public function preDelete(DeleteEvent $event)
    {
        $container = $event->getObject();

        $this->delete($container);
    }

    public function delete($container)
    {
        foreach ($container->getInstances() as $instance) {
            $this->instanceCrud->delete($instance);
            $this->om->remove($instance);
        }

        foreach ($container->getWidgetContainerConfigs() as $config) {
            $this->om->remove($config);
        }

        $this->om->flush();
    }
}
