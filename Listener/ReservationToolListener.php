<?php

namespace FormaLibre\ReservationBundle\Listener;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 *  @DI\Service()
 */
class ReservationToolListener
{
    private $container;
    private $request;
    private $httpKernel;

    /**
     * @DI\InjectParams({
     *      "container"     = @DI\Inject("service_container"),
     *      "requestStack"  = @DI\Inject("request_stack"),
     *      "httpKernel"    = @DI\Inject("http_kernel")
     * })
     */
    public function __construct(
        ContainerInterface $container,
        RequestStack $requestStack,
        HttpKernelInterface $httpKernel
    )
    {
        $this->container = $container;
        $this->request = $requestStack->getCurrentRequest();
        $this->httpKernel = $httpKernel;
    }

    /**
     * @DI\Observe("administration_tool_reservation_tool")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenEvent(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'FormaLibreReservationBundle:ReservationAdmin:index';
        $this->redirect($params, $event);
    }

    private function redirect($params, $event)
    {
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}
