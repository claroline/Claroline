<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Controller;

use Claroline\AgendaBundle\Form\ImportAgendaType;
use Claroline\AgendaBundle\Manager\AgendaManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Controller of the agenda.
 */
class WorkspaceAgendaController extends Controller
{
    private $om;
    private $request;
    private $agendaManager;
    private $router;
    private $authorization;
    private $eventDispatcher;

    /**
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "request"         = @DI\Inject("request_stack"),
     *     "agendaManager"   = @DI\Inject("claroline.manager.agenda_manager"),
     *     "router"          = @DI\Inject("router"),
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher")
     * })
     */
    public function __construct(
        ObjectManager $om,
        RequestStack $request,
        AgendaManager $agendaManager,
        RouterInterface $router,
        AuthorizationCheckerInterface $authorization,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->om = $om;
        $this->request = $request->getMasterRequest();
        $this->agendaManager = $agendaManager;
        $this->router = $router;
        $this->authorization = $authorization;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @EXT\Route("/workspace/{workspace}/import", name="claro_workspace_agenda_import")
     * @EXT\Template("ClarolineAgendaBundle:tool:import_ics_modal_form.html.twig")
     *
     * @param Workspace $workspace
     *
     * @return array
     */
    public function importsEventsIcsAction(Workspace $workspace)
    {
        $this->agendaManager->checkEditAccess($workspace);
        $form = $this->createForm(new ImportAgendaType());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $events = $this->agendaManager->importEvents($form->get('file')->getData(), $workspace);

            return new JsonResponse($events, 200);
        }

        return ['form' => $form->createView(), 'workspace' => $workspace];
    }
}
