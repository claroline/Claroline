<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 *  @DI\Service()
 */
class AgendaListener
{
    private $formFactory;
    private $templating;
    private $sc;
    private $container;
    private $router;
    private $request;
    private $httpKernel;

    /**
     * @DI\InjectParams({
     *     "formFactory"    = @DI\Inject("claroline.form.factory"),
     *     "templating"     = @DI\Inject("templating"),
     *     "sc"             = @DI\Inject("security.context"),
     *     "container"      = @DI\Inject("service_container"),
     *     "router"         = @DI\Inject("router"),
     *     "requestStack"   = @DI\Inject("request_stack"),
     *     "httpKernel"     = @DI\Inject("http_kernel")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        TwigEngine $templating,
        SecurityContextInterface $sc,
        ContainerInterface $container,
        RouterInterface $router,
        RequestStack $requestStack,
        HttpKernelInterface $httpKernel
    )
    {
        $this->formFactory = $formFactory;
        $this->templating = $templating;
        $this->sc = $sc;
        $this->container = $container;
        $this->router = $router;
        $this->request = $requestStack->getCurrentRequest();
        $this->httpKernel = $httpKernel;
    }

    /**
     * @DI\Observe("widget_agenda")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplay(DisplayWidgetEvent $event)
    {
        if ($event->getInstance()->isDesktop()) {
            $event->setContent($this->desktopAgenda($event->getInstance()));
        } else {
            $event->setContent($this->workspaceAgenda($event->getInstance()));
        }
        $event->stopPropagation();
    }

    public function workspaceAgenda($id)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $usr = $this->container->get('security.context')->getToken()->getUser();
        $owners = $em->getRepository('ClarolineCoreBundle:Event')->findByWorkspaceId($id, 0, 5);

        return $this->templating->render(
            'ClarolineCoreBundle:Widget:agenda_widget.html.twig',
            array('listEvents' => $owners)
        );
    }

    public function desktopAgenda()
    {
        if (!$this->request) {
            throw new NoHttpRequestException();
        }

        $params = array();
        $params['_controller'] = 'ClarolineCoreBundle:Tool\DesktopAgenda:widget';
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        return $response->getContent();
    }
}
