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

use Claroline\AgendaBundle\Entity\Event;
use Claroline\AgendaBundle\Manager\AgendaManager;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
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
     *     "request"            = @DI\Inject("request_stack"),
     *     "agendaManager"      = @DI\Inject("claroline.manager.agenda_manager"),
     *     "router"             = @DI\Inject("router"),
     *     "tokenStorage"       = @DI\Inject("security.token_storage"),
     *     "em"                 = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        RequestStack $request,
        AgendaManager $agendaManager,
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        EntityManager $em
    ) {
        $this->authorization = $authorization;
        $this->request = $request->getMasterRequest();
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
     * @EXT\Template("ClarolineAgendaBundle:agenda:invitation.html.twig")
     */
    public function invitationAction(Event $event, $action)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $invitation = $this->em->getRepository('ClarolineAgendaBundle:EventInvitation')->findOneBy([
            'event' => $event->getId(),
            'user' => $user->getId(),
        ]);

        if ($invitation && $invitation->getStatus() !== $action) {
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

    private function checkPermission(Event $event)
    {
        if (false === $event->isEditable()) {
            throw new AccessDeniedException('You cannot edit this event');
        }

        if ($event->getWorkspace()) {
            if (!$this->authorization->isGranted(['agenda_', 'edit'], $event->getWorkspace())) {
                throw new AccessDeniedException('You cannot edit the agenda');
            }

            return;
        }

        if ($this->tokenStorage->getToken()->getUser() !== $event->getUser()) {
            throw new AccessDeniedException('You cannot edit the agenda');
        }
    }
}
