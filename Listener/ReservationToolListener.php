<?php

namespace FormaLibre\ReservationBundle\Listener;

use Claroline\CoreBundle\Event\DisplayToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
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
    private $templating;

    /**
     * @DI\InjectParams({
     *      "container"     = @DI\Inject("service_container"),
     *      "requestStack"  = @DI\Inject("request_stack"),
     *      "httpKernel"    = @DI\Inject("http_kernel"),
     *      "templating"    = @DI\Inject("templating")
     * })
     */
    public function __construct(
        ContainerInterface $container,
        RequestStack $requestStack,
        HttpKernelInterface $httpKernel,
        TwigEngine $templating
    )
    {
        $this->container = $container;
        $this->request = $requestStack->getCurrentRequest();
        $this->httpKernel = $httpKernel;
        $this->templating = $templating;
    }

    /**
     * @DI\Observe("administration_tool_formalibre_reservation_tool")
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

    /**
     * @DI\Observe("open_tool_desktop_formalibre_reservation_agenda")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktopReservationAgenda(DisplayToolEvent $event)
    {
        $event->setContent($this->desktopReservationAgenda());
    }

    private function desktopReservationAgenda()
    {
        return $this->templating->render('FormaLibreReservationBundle:Tool:reservationAgenda.html.twig');
    }
}
