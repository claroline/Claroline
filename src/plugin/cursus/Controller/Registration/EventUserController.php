<?php

namespace Claroline\CursusBundle\Controller\Registration;

use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CursusBundle\Entity\Event;
use Claroline\CursusBundle\Entity\Registration\EventUser;
use Claroline\CursusBundle\Manager\EventManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/training_event_user', name: 'apiv2_training_event_user_')]
class EventUserController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly EventManager $eventManager
    ) {
        $this->authorization = $authorization;
    }

    public static function getName(): string
    {
        return 'training_event_user';
    }

    public static function getClass(): string
    {
        return EventUser::class;
    }

    #[Route(path: '/context/{context}/{contextId}', name: 'context_list', methods: ['GET'])]
    public function listByContextAction(
        string $context,
        ?string $contextId = null,
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        if (WorkspaceContext::getName() === $context) {
            $finderRequest->addFilter('event.workspace', $contextId);
        }

        $options = static::getOptions();
        $assertions = $this->crud->search(EventUser::class, $finderRequest, $options['list'] ?? []);

        return $assertions->toResponse();
    }

    #[Route(path: '/{id}', name: 'event_list', methods: ['GET'])]
    public function listByEventAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Event $event,
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        $this->checkPermission('FOLLOW', $event, [], true);

        $finderRequest->addFilter('event', $event->getUuid());

        $options = static::getOptions();
        $results = $this->crud->search(EventUser::class, $finderRequest, $options['list'] ?? []);

        return $results->toResponse();
    }

    /**
     * Send an invitation message to a subset of the registered users.
     */
    #[Route(path: '/invite', name: 'invite', methods: ['PUT'])]
    public function inviteAction(Request $request): JsonResponse
    {
        $ids = $this->decodeRequest($request);
        $eventUsers = $this->om->getRepository(EventUser::class)->findBy(['uuid' => $ids]);

        $this->om->startFlushSuite();
        foreach ($eventUsers as $eventUser) {
            $this->checkPermission('FOLLOW', $eventUser->getEvent());

            $this->eventManager->sendSessionEventInvitation($eventUser->getEvent(), [$eventUser->getUser()]);
        }
        $this->om->endFlushSuite();

        return new JsonResponse(null, 204);
    }
}
