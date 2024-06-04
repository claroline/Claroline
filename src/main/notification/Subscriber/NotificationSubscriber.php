<?php

namespace Claroline\NotificationBundle\Subscriber;

use Claroline\CoreBundle\Entity\User;
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
            $notifications = $this->notificationManager->countNewNotifications($user);
            if (!empty($notifications)) {
                $response->headers->set('Claroline-Notifications', $notifications);
            }
        }
    }
}
