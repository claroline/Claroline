<?php

namespace Claroline\NotificationBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\NotificationBundle\Entity\Notification;

class NotificationManager
{
    public function __construct(
        private readonly ObjectManager $om
    ) {
    }

    public function createNotifications(string $message, array $users): void
    {
        foreach ($users as $user) {
            $notification = new Notification();
            $notification->setMessage($message);
            $notification->setUser($user);

            $this->om->persist($notification);
        }

        $this->om->flush();
    }

    public function getNewNotifications(User $user): array
    {
        return $this->om->getRepository(Notification::class)->findBy([
            'user' => $user,
        ]);
    }
}
