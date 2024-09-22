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

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Claroline\AgendaBundle\Entity\Event;
use Claroline\AgendaBundle\Entity\EventInvitation;
use Claroline\AgendaBundle\Manager\EventManager;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route(path: '/event', name: 'apiv2_event_')]
class EventController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly RequestStack $requestStack,
        private readonly EventManager $manager,
        private readonly RoutingHelper $routing
    ) {
        $this->authorization = $authorization;
    }

    public static function getClass(): string
    {
        return Event::class;
    }

    public static function getName(): string
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
            $user = $this->tokenStorage->getToken()?->getUser();
            if ($user instanceof User) {
                $hiddenFilters['user'] = $user->getUuid();
            } else {
                $hiddenFilters['anonymous'] = true;
            }
        }

        return $hiddenFilters;
    }

    #[Route(path: '/{id}/ics', name: 'download_ics', methods: ['GET'])]
    public function downloadICSAction(#[MapEntity(mapping: ['id' => 'uuid'])]
    Event $event): StreamedResponse
    {
        $this->checkPermission('OPEN', $event, [], true);

        return new StreamedResponse(function () use ($event): void {
            echo $this->manager->getICS($event);
        }, 200, [
            'Content-Type' => 'text/calendar',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($event->getName()).'.ics',
        ]);
    }

    /**
     * Lists the participants of an event.
     *
     */
    #[Route(path: '/{id}/participants', name: 'list_participants', methods: ['GET'])]
    public function listParticipantsAction(#[MapEntity(mapping: ['id' => 'uuid'])]
    Event $event, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $event, [], true);

        return new JsonResponse(
            $this->crud->list(EventInvitation::class, array_merge($request->query->all(), [
                'hiddenFilters' => ['event' => $event->getUuid()],
            ]))
        );
    }

    /**
     * Adds the selected users as event participants.
     *
     */
    #[Route(path: '/{id}/participants', name: 'add_participants', methods: ['POST'])]
    public function addParticipantsAction(#[MapEntity(mapping: ['id' => 'uuid'])]
    Event $event, Request $request): JsonResponse
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
     */
    #[Route(path: '/{id}/participants', name: 'remove_participants', methods: ['DELETE'])]
    public function removeParticipantsAction(#[MapEntity(mapping: ['id' => 'uuid'])]
    Event $event, Request $request): JsonResponse
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
     */
    #[Route(path: '/{id}/invitations/send', name: 'send_invitations', methods: ['POST'])]
    public function sendInvitationsAction(#[MapEntity(mapping: ['id' => 'uuid'])]
    Event $event, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $event, [], true);

        $users = $this->decodeIdsString($request, User::class);
        if (!empty($users)) {
            $this->manager->sendInvitation($event, $users);
        }

        return new JsonResponse(null, 204);
    }

    #[Route(path: '/invitations/{id}/status/{status}', name: 'change_invitation_status', methods: ['GET'])]
    public function changeInvitationStatusAction(#[MapEntity(mapping: ['id' => 'id'])]
    EventInvitation $invitation, string $status, Request $request): Response
    {
        $canEdit = $this->checkPermission('EDIT', $invitation->getEvent());

        $currentUser = $this->tokenStorage->getToken()?->getUser();
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

        // for email validation link, redirect to the event view
        return new RedirectResponse(
            $this->routing->desktopUrl('agenda').'/event/'.$invitation->getEvent()->getUuid()
        );
    }
}
