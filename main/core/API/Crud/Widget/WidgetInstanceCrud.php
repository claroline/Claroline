<?php

namespace Claroline\CoreBundle\API\Crud\Widget;

use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Persistence\ObjectManager;

class WidgetInstanceCrud
{
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

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
