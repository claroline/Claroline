<?php

namespace Claroline\NotificationBundle\Controller;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\NotificationBundle\Entity\Notification;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/notification')]
class NotificationController
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer
    ) {
    }

    /**
     * Lists all the notifications of the current user.
     */
    #[Route(path: '', name: 'claro_notification_list', methods: ['GET'])]
    public function listAction(): JsonResponse
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse(null, 204);
        }

        $user = $this->tokenStorage->getToken()?->getUser();

        $notifications = $this->om->getRepository(Notification::class)->findBy(['user' => $user], ['createdAt' => 'DESC']);

        return new JsonResponse(array_map(function (Notification $notification) {
            return $this->serializer->serialize($notification);
        }, $notifications));
    }

    #[Route(path: '', name: 'claro_notification_read', methods: ['PUT'])]
    public function readAction(Request $request): JsonResponse
    {
        return new JsonResponse();
    }
}
