<?php

namespace Claroline\AuthenticationBundle\Subscriber\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\NewPasswordEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::PRE_CREATE, User::class) => 'preCreate',
            CrudEvents::getEventName(CrudEvents::PRE_UPDATE, User::class) => 'preUpdate',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var User $user */
        $user = $event->getObject();

        if ($user->getPlainpassword()) {
            // hash the password (based on the security.yaml config for the $user class)
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $user->getPlainpassword()
            );
            $user->setPassword($hashedPassword);
        }
    }

    public function preUpdate(UpdateEvent $event): void
    {
        /** @var User $user */
        $user = $event->getObject();

        if ($user->getPlainpassword()) {
            // hash the password (based on the security.yaml config for the $user class)
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $user->getPlainpassword()
            );
            $user->setPassword($hashedPassword);

            $event = new NewPasswordEvent($user);
            $this->eventDispatcher->dispatch($event, SecurityEvents::NEW_PASSWORD);
        }
    }
}
