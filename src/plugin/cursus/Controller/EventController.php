<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Controller;

use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Manager\PdfManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CursusBundle\Entity\Event;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Claroline\CursusBundle\Entity\Registration\EventUser;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Manager\EventManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/training_event', name: 'apiv2_training_event_')]
class EventController extends AbstractCrudController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly TranslatorInterface $translator,
        private readonly EventManager $manager,
        private readonly PdfManager $pdfManager
    ) {
        $this->authorization = $authorization;
    }

    public static function getName(): string
    {
        return 'training_event';
    }

    public static function getClass(): string
    {
        return Event::class;
    }

    public function getIgnore(): array
    {
        return ['list'];
    }

    #[Route(path: '/copy', name: 'copy', methods: ['POST'])]
    public function copyAction(Request $request): JsonResponse
    {
        $processed = [];

        $this->om->startFlushSuite();

        $data = $this->decodeRequest($request);
        /** @var Event[] $events */
        $events = $this->om->getRepository(Event::class)->findBy(['uuid' => $data]);

        foreach ($events as $event) {
            if ($this->authorization->isGranted('EDIT', $event)) {
                $processed[] = $this->crud->copy($event, [], ['parent' => $event->getSession()]);
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (Event $event) {
            return $this->serializer->serialize($event);
        }, $processed));
    }

    #[Route(path: '/{workspace}', name: 'list', methods: ['GET'])]
    public function listAction(
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery(),
        #[MapEntity(mapping: ['workspace' => 'uuid'])]
        ?Workspace $workspace = null
    ): StreamedJsonResponse {
        if ($workspace) {
            $finderQuery->addFilter('workspace', $workspace->getUuid());
        }

        $options = static::getOptions();
        $results = $this->crud->search(static::getClass(), $finderQuery, $options['list'] ?? []);

        return $results->toResponse();
    }

    #[Route(path: '/{id}/open', name: 'open', methods: ['GET'])]
    public function openAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Event $sessionEvent
    ): JsonResponse {
        $this->checkPermission('OPEN', $sessionEvent, [], true);

        $user = $this->tokenStorage->getToken()?->getUser();
        $registration = [];
        if ($user instanceof User) {
            $userRegistration = $this->om->getRepository(EventUser::class)->findOneBy([
                'user' => $user,
                'event' => $sessionEvent,
            ]);

            if ($userRegistration) {
                $registration = $this->serializer->serialize($userRegistration);
            }
        }

        return new JsonResponse([
            'event' => $this->serializer->serialize($sessionEvent),
            'registration' => $registration,
        ]);
    }

    #[Route(path: '/{id}/pdf', name: 'download_pdf', methods: ['GET'])]
    public function downloadPdfAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Event $sessionEvent,
        Request $request
    ): StreamedResponse {
        $this->checkPermission('OPEN', $sessionEvent, [], true);

        return new StreamedResponse(function () use ($sessionEvent, $request): void {
            echo $this->pdfManager->fromHtml(
                $this->manager->generateFromTemplate($sessionEvent, $request->getLocale())
            );
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($sessionEvent->getName()).'.pdf',
        ]);
    }

    #[Route(path: '/{id}/ics', name: 'download_ics', methods: ['GET'])]
    public function downloadICSAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Event $sessionEvent
    ): StreamedResponse {
        $this->checkPermission('OPEN', $sessionEvent, [], true);

        return new StreamedResponse(function () use ($sessionEvent): void {
            echo $this->manager->getICS($sessionEvent);
        }, 200, [
            'Content-Type' => 'text/calendar',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($sessionEvent->getName()).'.ics',
        ]);
    }

    #[Route(path: '/{id}/self/register', name: 'self_register', methods: ['PUT'])]
    public function selfRegisterAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Event $sessionEvent,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        $this->checkPermission('OPEN', $sessionEvent, [], true);

        if (Session::REGISTRATION_PUBLIC !== $sessionEvent->getRegistrationType()) {
            throw new AccessDeniedException();
        }
        $eventUsers = $this->manager->addUsers($sessionEvent, [$user]);

        return new JsonResponse($this->serializer->serialize($eventUsers));
    }

    #[Route(path: '/{id}/invite/all', name: 'invite_all', methods: ['PUT'])]
    public function inviteAllAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Event $sessionEvent
    ): JsonResponse {
        $this->checkPermission('FOLLOW', $sessionEvent, [], true);

        $this->manager->inviteAllSessionEventLearners($sessionEvent);

        return new JsonResponse(null, 204);
    }

    #[Route(path: '/{id}/users/{type}', name: 'list_users', methods: ['GET'])]
    public function listUsersAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Event $sessionEvent,
        string $type,
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkPermission('OPEN', $sessionEvent, [], true);

        $users = $this->crud->search(EventUser::class, $finderQuery->addFilters([
            'type' => $type,
            'event' => $sessionEvent->getUuid(),
        ]), [SerializerInterface::SERIALIZE_LIST]);

        return $users->toResponse();
    }

    #[Route(path: '/{id}/users/{type}', name: 'add_users', methods: ['PATCH'])]
    public function addUsersAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Event $sessionEvent,
        string $type,
        Request $request
    ): JsonResponse {
        $this->checkPermission('FOLLOW', $sessionEvent, [], true);

        $ids = $this->decodeRequest($request);
        $users = $this->om->getRepository(User::class)->findBy(['uuid' => $ids]);

        $nbUsers = count($users);

        if (AbstractRegistration::LEARNER === $type && !$this->manager->checkSessionEventCapacity($sessionEvent, $nbUsers)) {
            return new JsonResponse(['errors' => [
                $this->translator->trans('users_limit_reached', ['%count%' => $nbUsers], 'cursus'),
            ]], 422); // not the best status (same as form validation errors)
        }

        $sessionEventUsers = $this->manager->addUsers($sessionEvent, $users, $type);

        return new JsonResponse(array_map(function (EventUser $sessionEventUser) {
            return $this->serializer->serialize($sessionEventUser);
        }, $sessionEventUsers));
    }

    #[Route(path: '/{id}/users/{type}', name: 'remove_users', methods: ['DELETE'])]
    public function removeUsersAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Event $sessionEvent,
        Request $request
    ): JsonResponse {
        $this->checkPermission('FOLLOW', $sessionEvent, [], true);

        $ids = $this->decodeRequest($request);
        $eventUsers = $this->om->getRepository(EventUser::class)->findBy(['uuid' => $ids]);

        $this->manager->removeUsers($sessionEvent, $eventUsers);

        return new JsonResponse(null, 204);
    }

    #[Route(path: '/{id}/invite/users', name: 'invite_users', methods: ['PUT'])]
    public function inviteUsersAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Event $sessionEvent,
        Request $request
    ): JsonResponse {
        $this->checkPermission('FOLLOW', $sessionEvent, [], true);

        $ids = $this->decodeRequest($request);
        $eventUsers = $this->om->getRepository(EventUser::class)->findBy(['uuid' => $ids]);

        $this->manager->sendSessionEventInvitation($sessionEvent, array_map(function (EventUser $eventUser) {
            return $eventUser->getUser();
        }, $eventUsers));

        return new JsonResponse(null, 204);
    }
}
