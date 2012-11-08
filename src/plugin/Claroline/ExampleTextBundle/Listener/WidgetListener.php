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
        $event->setContent('
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus vestibulum accumsan massa sed facilisis. Aliquam consequat porttitor suscipit. Curabitur lectus augue, lacinia tempor pretium quis, mollis eu tortor. Praesent mattis, quam ac ultrices consequat, magna lacus mollis lorem, in adipiscing diam sapien faucibus augue. Suspendisse quis purus dui. Pellentesque elit nulla, pretium vel lobortis ac, accumsan sit amet libero. Mauris sed magna pharetra turpis iaculis sollicitudin. Nulla eu velit euismod sapien molestie tincidunt. Morbi non lacus magna. Morbi pretium, augue ut porttitor tempor, leo sapien sollicitudin urna, ac placerat neque odio eu massa. Donec nec quam id augue porta rutrum a id lectus. Sed iaculis sem vitae mauris tincidunt facilisis. Cras libero eros, suscipit at ultrices sit amet, consectetur ac neque. Praesent rhoncus, est nec lacinia volutpat, lorem sem malesuada orci, vel eleifend tortor dolor ac justo. Donec feugiat, magna eu semper congue, dolor est ullamcorper nisi, in vestibulum mi sem id ante. Vestibulum tincidunt molestie quam vitae dignissim.

            Praesent ac viverra nulla. Sed fermentum auctor risus, sit amet convallis urna blandit vitae. Pellentesque elementum tincidunt nisi nec fermentum. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Ut ac neque ante. In in dolor a mi vestibulum pharetra a quis eros. Duis feugiat magna egestas orci pharetra congue. Sed justo magna, euismod at consectetur vel, tempus a sapien. Cras nunc velit, aliquet quis consequat sit amet, molestie in mauris. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.

            In consequat scelerisque nisi, aliquam scelerisque orci condimentum at. Suspendisse potenti. Etiam adipiscing elit at felis euismod consectetur. Donec id tellus ac metus pulvinar imperdiet. Morbi in tellus turpis. Quisque augue nunc, placerat quis malesuada vitae, pulvinar et lectus. Cras ultricies, arcu at egestas facilisis, diam leo auctor sapien, eu sollicitudin libero eros id orci. Ut nisl urna, condimentum vitae porttitor eget, iaculis vitae sapien. Cras laoreet fermentum congue. Quisque quis nisl et ipsum commodo accumsan ut et orci. Nulla a mauris a dolor placerat commodo eget a tellus. Nunc mattis consectetur nulla eget iaculis.

            Suspendisse aliquet dui in quam rutrum viverra. Praesent rutrum eleifend est, sit amet rutrum ligula semper id. Curabitur et odio leo. Donec nec semper nibh. Suspendisse potenti. Nullam et orci libero. Aenean lectus lorem, viverra ac posuere eget, tempor vel tellus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Pellentesque blandit, lectus nec facilisis egestas, sapien libero rhoncus lectus, eu varius risus nulla vitae odio. In mollis, erat id fringilla condimentum, lectus turpis iaculis risus, nec commodo dolor odio eget mauris. Nulla blandit risus vel magna dictum at tristique mauris molestie. Mauris bibendum justo nec est porttitor auctor. Nullam et fringilla urna. Cras fermentum, lacus vitae pulvinar ullamcorper, justo lacus ultrices dui, sed feugiat mauris diam vitae dui. Vestibulum a dolor tortor.

            Nam consequat nulla ac mauris lobortis vel rhoncus tellus ultrices. In blandit condimentum tellus eget fringilla. Duis eu suscipit lorem. Donec lectus nunc, aliquet eget cursus ac, pulvinar quis dolor. Quisque velit massa, sodales ac scelerisque eget, pellentesque vitae dolor. Sed porta luctus lacinia. Nunc ultricies lacinia nisi, vel auctor enim porttitor eget. Fusce quis massa et eros varius laoreet eu a dolor. Morbi a porta nibh. Vivamus euismod feugiat condimentum. ');
    }

    function onConfigure(ConfigureWidgetEvent $event)
    {
        $event->setResponse(new Response('some content'));
    }
}
