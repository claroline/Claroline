<?php

namespace [[Vendor]]\[[Bundle]]Bundle\Listener;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;

/**
 *  @DI\Service()
 */
class [[Widget]]Listener
{
    private $container;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @DI\Observe("widget_[[vendor]]_[[widget]]_widget")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplay(DisplayWidgetEvent $event)
    {
        if ($event->getInstance()->isDesktop()) {
            $event->setContent('Desktop widget');
        } else {
            $event->setContent('Workspace widget');
        }

        $event->stopPropagation();
    }
}
