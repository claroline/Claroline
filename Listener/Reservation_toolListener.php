<?php

namespace FormaLibre\ReservationBundle\Listener;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Symfony\Component\HttpFoundation\Response;

/**
 *  @DI\Service()
 */
class Reservation_toolListener
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
     * @DI\Observe("administration_tool_reservation_tool")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenEvent(OpenAdministrationToolEvent $event)
    {
        $response = new Response('change me !');
        $event->setContent($response);
    }
}
