<?php

namespace Valid\WithWidgets;

use Claroline\CoreBundle\Library\Plugin\Event\DisplayWidgetEvent;

class Listener
{
   function onDisplay(DisplayWidgetEvent $event)
    {
        $event->setContent('someContent');
    }

}
