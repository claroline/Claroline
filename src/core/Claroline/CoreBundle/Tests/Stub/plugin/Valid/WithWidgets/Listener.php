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
        $event->setContent('configure stub widget form');
    }
}
