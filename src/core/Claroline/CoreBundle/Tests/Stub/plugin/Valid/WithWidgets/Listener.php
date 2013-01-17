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
        $event->setContent(new Response('configure stub widget form'));
    }
}
