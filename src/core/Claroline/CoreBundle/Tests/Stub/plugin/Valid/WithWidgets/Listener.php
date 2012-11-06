<?php

namespace Valid\WithWidgets;

use Claroline\CoreBundle\Library\Widget\Event\DisplayWidgetEvent;

class Listener
{
   function onDisplay(DisplayWidgetEvent $event)
   {
       $event->setContent('someContent');
   }

}
