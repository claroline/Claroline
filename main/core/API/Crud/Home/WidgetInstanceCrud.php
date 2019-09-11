<?php

namespace Claroline\CoreBundle\API\Crud\Home;

use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Persistence\ObjectManager;

class WidgetInstanceCrud
{
    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * @param DeleteEvent $event
     */
    public function preDelete(DeleteEvent $event)
    {
        $widgetInstance = $event->getObject();

        $this->delete($widgetInstance);
    }

    public function delete($instance)
    {
        foreach ($instance->getWidgetInstanceConfigs() as $config) {
            $this->om->remove($config);
        }

        $this->om->flush();
    }
}
