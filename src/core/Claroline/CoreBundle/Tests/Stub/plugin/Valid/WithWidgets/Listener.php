<?php

namespace Valid\WithWidgets;

use Claroline\CoreBundle\Library\Event\DisplayWidgetEvent;

class Listener
{
    public function onDisplay(DisplayWidgetEvent $event)
    {
        $event->setContent('someContent');
    }

    public function onConfigure($event)
    {
        throw new \Exception('Here I came');
        $event->setContent('configure stub widget form');
    }
}
