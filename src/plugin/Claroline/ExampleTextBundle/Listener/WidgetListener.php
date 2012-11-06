<?php

namespace Claroline\ExampleTextBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Claroline\CoreBundle\Library\Widget\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Library\Widget\Event\ConfigureWidgetEvent;
use Symfony\Component\HttpFoundation\Response;

class WidgetListener extends ContainerAware
{
    function onDisplay(DisplayWidgetEvent $event)
    {
        $event->setContent('someContent');
    }

    function onConfigure(ConfigureWidgetEvent $event)
    {
        $event->setResponse(new Response('some content'));
    }
}
