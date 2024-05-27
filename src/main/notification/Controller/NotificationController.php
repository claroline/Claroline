<?php

namespace Claroline\NotificationBundle\Controller;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\NotificationBundle\Entity\Notification;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/notification")
 */
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
     * @Route("", name="claro_notification_list", methods={"GET"})
     */
    public function listAction(): JsonResponse
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse(null, 204);
        }

        $user = $this->tokenStorage->getToken()->getUser();

        $notifications = $this->om->getRepository(Notification::class)->findBy(['user' => $user]);

        return new JsonResponse(array_map(function (Notification $notification) {
            return $this->serializer->serialize($notification);
        }, $notifications));
    }

    /**
     * @Route("", name="claro_notification_read", methods={"PUT"})
     */
    public function readAction(Request $request): JsonResponse
    {
        return new JsonResponse();
    }
}
