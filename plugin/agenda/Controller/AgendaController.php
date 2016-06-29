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

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpFoundation\Request;
use Claroline\AgendaBundle\Entity\Event;
use Claroline\AgendaBundle\Manager\AgendaManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Routing\RouterInterface;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AgendaController extends Controller
{
    private $authorization;
    private $request;
    private $agendaManager;
    private $router;
    private $tokenStorage;
    private $em;

    /**
     * @DI\InjectParams({
     *     "authorization"      = @DI\Inject("security.authorization_checker"),
     *     "request"            = @DI\Inject("request"),
     *     "agendaManager"      = @DI\Inject("claroline.manager.agenda_manager"),
     *     "router"             = @DI\Inject("router"),
     *     "tokenStorage"       = @DI\Inject("security.token_storage"),
     *     "em"                 = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        Request $request,
        AgendaManager $agendaManager,
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        EntityManager $em
    ) {
        $this->authorization = $authorization;
        $this->request = $request;
        $this->agendaManager = $agendaManager;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
    }

    /**
     * @EXT\Route(
     *     "set-task/{event}/as-not-done",
     *     name="claro_agenda_set_task_as_not_done",
     *     options = {"expose"=true}
     * )
     *
     * @throws \Exception
     *
     * @param Event $event
     *
     * @return Response
     */
    public function setTaskAsNotDone(Event $event)
    {
        $this->checkPermission($event);
        if (!$event->isTaskDone()) {
            throw new \Exception('This task is already mark as not done.');
        }

        $event->setIsTaskDone(false);
        $om = $this->getDoctrine()->getManager();
        $om->flush();

        return new Response();
    }

    /**
     * @EXT\Route(
     *     "/set-task/{event}/as-done",
     *     name="claro_agenda_set_task_as_done",
     *     options = {"expose"=true}
     * )
     *
     * @throws \Exception
     *
     * @param Event $event
     *
     * @return Response
     */
    public function setTaskAsDone(Event $event)
    {
        $this->checkPermission($event);
        if ($event->isTaskDone()) {
            throw new \Exception('This task is already mark as done.');
        }

        $event->setIsTaskDone(true);
        $om = $this->getDoctrine()->getManager();
        $om->flush();

        return new Response();
    }

    /**
     * @EXT\Route(
     *      "/accept/invitation/{event}/{action}",
     *      name="claro_agenda_invitation_action"
     * )
     * @EXT\Template("ClarolineAgendaBundle:Agenda:invitation.html.twig")
     */
    public function invitationAction(Event $event, $action)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $invitation = $this->em->getRepository('ClarolineAgendaBundle:EventInvitation')->findOneBy([
            'event' => $event->getId(),
            'user' => $user->getId(),
        ]);

        if ($invitation && $invitation->getStatus() != $action) {
            $invitation->setStatus($action);
            $this->em->flush();

            return [
                'invitation' => $invitation,
                'already_done' => false,
            ];
        }

        return [
            'invitation' => $invitation,
            'already_done' => true,
        ];
    }

    /**
     * @EXT\Route(
     *     "/{event}/delete",
     *     name="claro_agenda_delete_event",
     *     options={"expose"=true}
     * )
     *
     * @param Event $event
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Event $event)
    {
        $this->checkPermission($event);
        $removed = $this->agendaManager->deleteEvent($event);

        return new JsonResponse($removed, 200);
    }

    /**
     * @EXT\Route(
     *     "/resize/event/{event}/day/{day}/minute/{minute}",
     *     name="claro_workspace_agenda_resize",
     *     options = {"expose"=true}
     * )
     */
    public function resizeAction(Event $event, $day, $minute)
    {
        $this->checkPermission($event);
        $data = $this->agendaManager->updateEndDate($event, $day, $minute);

        return new JsonResponse($data, 200);
    }

    /**
     * @EXT\Route(
     *     "/move/event/{event}/day/{day}/minute/{minute}",
     *     name="claro_workspace_agenda_move",
     *     options = {"expose"=true}
     * )
     */
    public function moveAction(Event $event, $day, $minute)
    {
        $this->checkPermission($event);
        $data = $this->agendaManager->moveEvent($event, $day, $minute);

        return new JsonResponse($data, 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/export",
     *     name="claro_workspace_agenda_export"
     * )
     *
     * @param Workspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportWorkspaceEventIcsAction(Workspace $workspace)
    {
        //if you can open the tool, you can export
        if (!$this->authorization->isGranted('agenda_', $workspace)) {
            throw new AccessDeniedException('The event cannot be updated');
        }

        return $this->exportEvent($workspace);
    }

    /**
     * @EXT\Route(
     *     "/desktop/export",
     *     name="claro_desktop_agenda_export"
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportDesktopEventIcsAction()
    {
        return $this->exportEvent();
    }

    private function exportEvent($workspace = null)
    {
        $file = $this->agendaManager->export();
        $response = new StreamedResponse();

        $response->setCallBack(
            function () use ($file) {
                readfile($file);
            }
        );

        $name = $workspace ? $workspace->getName() : 'desktop';
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$name.'.ics');
        $response->headers->set('Content-Type', ' text/calendar');
        $response->headers->set('Connection', 'close');

        return $response;
    }

    private function checkPermission(Event $event)
    {
        if ($event->isEditable() === false) {
            throw new AccessDeniedException('You cannot edit this event');
        }

        if ($event->getWorkspace()) {
            if (!$this->authorization->isGranted(array('agenda_', 'edit'), $event->getWorkspace())) {
                throw new AccessDeniedException('You cannot edit the agenda');
            }

            return;
        }

        if ($this->tokenStorage->getToken()->getUser() != $event->getUser()) {
            throw new AccessDeniedException('You cannot edit the agenda');
        }
    }
}
