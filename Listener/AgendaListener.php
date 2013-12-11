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
use Claroline\CoreBundle\Entity\Widget\SimpleTextConfig;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use Claroline\CoreBundle\Manager\SimpleTextManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service
 */
class AgendaListener
{
    private $formFactory;
    private $templating;
    private $sc;
    private $container;

    /**
     * @DI\InjectParams({
     *      "formFactory"       = @DI\Inject("claroline.form.factory"),
     *      "templating"        = @DI\Inject("templating"),
     *      "sc"                = @DI\Inject("security.context"),
     *     "container"          = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        TwigEngine $templating,
        SecurityContextInterface $sc,
        ContainerInterface $container
    )
    {
        $this->formFactory = $formFactory;
        $this->templating = $templating;
        $this->sc = $sc;
        $this->container = $container;
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

    public function workspaceAgenda($workspaceId)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $usr = $this->container->get('security.context')->getToken()->getUser();
        $owners = $em->getRepository('ClarolineCoreBundle:Event')->findByUserWithoutAllDay($usr , 5);

        return $this->templating->render(
            'ClarolineCoreBundle:Tool/workspace/agenda:agenda_widget.html.twig',
            array(
                'workspace' => $workspaceId,
                'listEvents' => $owners,
            )
        );
    }

    public function desktopAgenda()
    {
        $em = $this->container-> get('doctrine.orm.entity_manager');
        $usr = $this->container->get('security.context')->getToken()->getUser();
        $listEventsDesktop = $em->getRepository('ClarolineCoreBundle:Event')->findDesktop($usr, 1);
        $listEvents = $em->getRepository('ClarolineCoreBundle:Event')->findByUser($usr, 0);

        return $this->templating->render(
            'ClarolineCoreBundle:Tool/workspace/agenda:agenda_widget.html.twig',
            array(
                'listEvents' => array_merge($listEvents, $listEventsDesktop),
            )
        );
    }
} 