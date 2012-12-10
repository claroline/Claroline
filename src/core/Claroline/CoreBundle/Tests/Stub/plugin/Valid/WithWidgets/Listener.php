<?php

namespace Valid\WithWidgets;

use Claroline\CoreBundle\Library\Widget\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Library\Widget\Event\ConfigureWidgetWorkspaceEvent;

class Listener
{
   function onDisplay(DisplayWidgetEvent $event)
   {
       $event->setContent('someContent');
   }

   function onConfigure($event)
   {
       var_dump('je passe');
       $event->setContent(new Response('configure stub widget form'));
   }

}
