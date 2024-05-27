<?php

namespace Claroline\NotificationBundle\Subscriber;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\User;
use Claroline\NotificationBundle\Entity\Notification;
use Claroline\NotificationBundle\Manager\NotificationManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly SerializerProvider $serializer,
        private readonly NotificationManager $notificationManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['getUserNotifications', -255],
        ];
    }

    public function getUserNotifications(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $user = $this->tokenStorage->getToken()?->getUser();
        if ($event->isMainRequest() && 200 === $response->getStatusCode() && $response instanceof JsonResponse && $user instanceof User) {
            $notifications = $this->notificationManager->getNewNotifications($user);
            if (!empty($notifications)) {
                $decodedContent = json_decode($response->getContent(), true);

                /*$event->setResponse(new JsonResponse(array_merge($decodedContent ?? [], [
                    '__notifications' => array_map(function (Notification $notification) {
                        return $this->serializer->serialize($notification);
                    }, $notifications)
                ]), $response->getStatusCode()));*/
            }
        }
    }
}