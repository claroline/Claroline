<?php

namespace Claroline\CoreBundle\Subscriber;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Update the user last activity date at the end of each request.
 */
class UserActivitySubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ObjectManager */
    private $om;

    public function __construct(TokenStorageInterface $tokenStorage, ObjectManager $om)
    {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
    }

    public static function getSubscribedEvents(): array
    {
        return [
             TerminateEvent::class => ['setLastActivityDate', -500], // arbitrary low priority to be the last
        ];
    }

    public function setLastActivityDate()
    {
        $currentUser = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        if ($currentUser instanceof User) {
            $now = new \DateTime();
            // We update the user last activity only if there is no activity in the last 30 seconds
            // to avoid too many updates in the user table.
            if (empty($currentUser->getLastActivity()) || $now > date_add($currentUser->getLastActivity(), new \DateInterval('PT30S'))) {
                $currentUser->setLastActivity($now);

                $this->om->persist($currentUser);
                $this->om->flush();
            }
        }
    }
}
