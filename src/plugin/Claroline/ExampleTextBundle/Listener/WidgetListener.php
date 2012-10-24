<?php

namespace Claroline\ExampleTextBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Claroline\CoreBundle\Library\Plugin\Event\DisplayWidgetEvent;


class WidgetListener extends ContainerAware
{
    function onDisplay(DisplayWidgetEvent $event)
    {
        $event->setContent('someContent');
    }
}
