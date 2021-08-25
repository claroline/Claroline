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
use Claroline\AgendaBundle\Entity\EventInvitation;
use Claroline\AgendaBundle\Manager\EventManager;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/event")
 */
class EventController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var RequestStack */
    private $requestStack;
    /** @var EventManager */
    private $manager;
    /** @var RoutingHelper */
    private $routing;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack,
        EventManager $manager,
        RoutingHelper $routing
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
        $this->manager = $manager;
        $this->routing = $routing;
    }

    public function getClass()
    {
        return Event::class;
    }

    public function getName()
    {
        return 'event';
    }

    protected function getDefaultHiddenFilters(): array
    {
        $hiddenFilters = [];

        $query = $this->requestStack->getCurrentRequest()->query->all();

        // get start & end date and add them to the hidden filters list
        $hiddenFilters['inRange'] = [$query['start'] ?? null, $query['end'] ?? null];

        if (!isset($query['filters']['workspaces'])) {
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();
            if ($user instanceof User) {
                $hiddenFilters['user'] = $user->getUuid();
            } else {
                $hiddenFilters['anonymous'] = true;
            }
        }

        return $hiddenFilters;
    }

    /**
     * @Route("/{id}/ics", name="apiv2_event_download_ics", methods={"GET"})
     * @EXT\ParamConverter("event", options={"mapping": {"id": "uuid"}})
     */
    public function downloadICSAction(Event $event): StreamedResponse
    {
        $this->checkPermission('OPEN', $event, [], true);

        return new StreamedResponse(function () use ($event) {
            echo $this->manager->getICS($event);
        }, 200, [
            'Content-Type' => 'text/calendar',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($event->getName()).'.ics',
        ]);
    }

    /**
     * Lists the participants of an event.
     *
     * @Route("/{id}/participants", name="apiv2_event_list_participants", methods={"GET"})
     * @EXT\ParamConverter("event", options={"mapping": {"id": "uuid"}})
     */
    public function listParticipantsAction(Event $event, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $event, [], true);

        return new JsonResponse(
            $this->finder->search(EventInvitation::class, array_merge($request->query->all(), [
                'hiddenFilters' => ['event' => $event->getUuid()],
            ]))
        );
    }

    /**
     * Adds the selected users as event participants.
     *
     * @Route("/{id}/participants", name="apiv2_event_add_participants", methods={"POST"})
     * @EXT\ParamConverter("event", options={"mapping": {"id": "uuid"}})
     */
    public function addParticipantsAction(Event $event, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $event, [], true);

        $invitations = [];
        $this->om->startFlushSuite();

        $users = $this->decodeIdsString($request, User::class);
        foreach ($users as $user) {
            // TODO : use crud instead
            $invitations[] = $this->manager->createInvitation($event, $user);
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (EventInvitation $invitation) {
            return $this->serializer->serialize($invitation);
        }, $invitations));
    }

    /**
     * Removes selected users from the event participants.
     *
     * @Route("/{id}/participants", name="apiv2_event_remove_participants", methods={"DELETE"})
     * @EXT\ParamConverter("event", options={"mapping": {"id": "uuid"}})
     */
    public function removeParticipantsAction(Event $event, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $event, [], true);

        $this->om->startFlushSuite();

        $participants = $this->decodeIdsString($request, EventInvitation::class);
        foreach ($participants as $participant) {
            // TODO : use crud instead
            $this->manager->removeInvitation($participant);
        }

        $this->om->endFlushSuite();

        return new JsonResponse(null, 204);
    }

    /**
     * Sends invitations to the selected participants.
     *
     * @Route("/{id}/invitations/send", name="apiv2_event_send_invitations", methods={"POST"})
     * @EXT\ParamConverter("event", options={"mapping": {"id": "uuid"}})
     */
    public function sendInvitationsAction(Event $event, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $event, [], true);

        $users = $this->decodeIdsString($request, User::class);
        if (!empty($users)) {
            $this->manager->sendInvitation($event, $users);
        }

        return new JsonResponse(null, 204);
    }

    /**
     * @Route("/invitations/{id}/status/{status}", name="apiv2_event_change_invitation_status", methods={"GET"})
     * @EXT\ParamConverter("invitation", options={"mapping": {"id": "id"}})
     */
    public function changeInvitationStatusAction(EventInvitation $invitation, string $status, Request $request): Response
    {
        $canEdit = $this->checkPermission('EDIT', $invitation->getEvent());

        $currentUser = $this->tokenStorage->getToken()->getUser();
        if (!$canEdit || !$currentUser instanceof User || $currentUser->getUuid() !== $invitation->getUser()->getUuid()) {
            // only an admin or the invited user can update the status
            throw new AccessDeniedException('You cannot change the status of this invitation.');
        }

        $invitation->setStatus($status);
        $this->om->persist($invitation);
        $this->om->flush();

        if ($request->isXmlHttpRequest()) {
            // for ui
            return new JsonResponse(
                $this->serializer->serialize($invitation)
            );
        }

        // for email validation link, redirect to event view
        return new RedirectResponse(
            $this->routing->desktopUrl('agenda').'/event/'.$invitation->getEvent()->getUuid()
        );
    }
}
