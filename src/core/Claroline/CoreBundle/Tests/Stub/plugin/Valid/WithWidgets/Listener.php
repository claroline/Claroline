<?php

namespace Valid\WithWidgets;

use Claroline\CoreBundle\Library\Widget\Event\DisplayWidgetEvent;

class Listener
{
    public function onDisplay(DisplayWidgetEvent $event)
    {
        $event->setContent('someContent');
    }

    public function onConfigure($event)
    {
        var_dump('je passe');
        $event->setContent('configure stub widget form');
    }
}
