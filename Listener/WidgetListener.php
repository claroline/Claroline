<?php

namespace Innova\PathBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Claroline\CoreBundle\Library\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Library\Event\ConfigureWidgetEvent;
use Symfony\Component\HttpFoundation\Response;

class WidgetListener extends ContainerAware
{
    public function onDisplay(DisplayWidgetEvent $event)
    {
        $event->setContent(
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit.
            Vivamus vestibulum accumsan massa sed facilisis.
            Aliquam consequat porttitor suscipit.
            Curabitur lectus augue, lacinia tempor pretium quis, mollis eu tortor.
            Praesent mattis, quam ac ultrices consequat, magna lacus mollis lorem,
            in adipiscing diam sapien faucibus augue. Suspendisse quis purus dui.
            Pellentesque elit nulla, pretium vel lobortis ac, accumsan sit amet libero.
            Mauris sed magna pharetra turpis iaculis sollicitudin.
            Nulla eu velit euismod sapien molestie tincidunt. Morbi non lacus magna.
            Morbi pretium, augue ut porttitor tempor, leo sapien sollicitudin urna,
            ac placerat neque odio eu massa. Donec nec quam id augue porta rutrum a id lectus.
            Sed iaculis sem vitae mauris tincidunt facilisis. Cras libero eros, suscipit at ultrices sit amet,
            consectetur ac neque. Praesent rhoncus, est nec lacinia volutpat, lorem sem malesuada orci,
            vel eleifend tortor dolor ac justo. Donec feugiat, magna eu semper congue,
            dolor est ullamcorper nisi, in vestibulum mi sem id ante.
            Vestibulum tincidunt molestie quam vitae dignissim.'
        );
    }

    public function onConfigure(ConfigureWidgetEvent $event)
    {
        $event->setResponse(new Response('some content'));
    }
}
