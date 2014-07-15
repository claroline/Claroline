<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Tool\Agenda;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Claroline\CoreBundle\Entity\Event;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\AgendaManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Routing\RouterInterface;

/**
 * Controller of the Agenda
 */
class AgendaController extends Controller
{
    private $security;
    private $formFactory;
    private $request;
    private $agendaManager;
    private $router;

    /**
     * @DI\InjectParams({
     *     "security"           = @DI\Inject("security.context"),
     *     "formFactory"        = @DI\Inject("claroline.form.factory"),
     *     "request"            = @DI\Inject("request"),
     *     "agendaManager"      = @DI\Inject("claroline.manager.agenda_manager"),
     *     "router"             = @DI\Inject("router"),
     * })
     */
    public function __construct(
        SecurityContextInterface $security,
        FormFactory $formFactory,
        Request $request,
        AgendaManager $agendaManager,
        RouterInterface $router
    )
    {
        $this->security      = $security;
        $this->formFactory   = $formFactory;
        $this->request       = $request;
        $this->agendaManager = $agendaManager;
        $this->router        = $router;
    }

    /**
     * @EXT\Route(
     *     "/{event}/update/form",
     *     name="claro_agenda_update_event_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Agenda:updateEventModalForm.html.twig")
     *
     * @param Workspace $workspace
     * @return array
     */
    public function updateEventModalFormAction(Event $event)
    {
        $form = $this->formFactory->create(FormFactory::TYPE_AGENDA, array(), $event);

        return array(
            'form' => $form->createView(),
            'action' => $this->router->generate(
                'claro_agenda_update', array('event' => $event->getId())
            ),
            'event' => $event
        );
    }

    /**
     * @EXT\Route(
     *     "/{event}/update",
     *     name="claro_agenda_update"
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Agenda:updateEventModalForm.html.twig")
     * @param Workspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(Event $event)
    {
        $form = $this->formFactory->create(FormFactory::TYPE_AGENDA, array(), $event);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $event = $this->agendaManager->updateEvent($event);

            return new JsonResponse($event, 200);
        }

        return array(
            'form' => $form->createView(),
            'action' => $this->router->generate(
                'claro_agenda_update', array('event' => $event->getId())
            ),
            'event' => $event
        );
    }


    /**
     * @EXT\Route(
     *     "/{event}/delete",
     *     name="claro_agenda_delete_event"
     * )
     *
     * @param Event $event
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Event $event)
    {
        $removed = $this->agendaManager->deleteEvent($event);

        return new JsonResponse($removed, 200);
    }
} 