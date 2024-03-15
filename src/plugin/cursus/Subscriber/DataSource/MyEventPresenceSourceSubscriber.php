<?php

namespace Claroline\CursusBundle\Subscriber\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Claroline\CursusBundle\Entity\EventPresence;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MyEventPresenceSourceSubscriber implements EventSubscriberInterface
{
    private TokenStorageInterface $tokenStorage;
    private FinderProvider $finder;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        FinderProvider $finder
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->finder = $finder;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'data_source.my_event_presences.load' => 'getData',
        ];
    }

    public function getData(GetDataEvent $event): void
    {
        $options = $event->getOptions();
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user instanceof User) {
            $options['hiddenFilters']['user'] = $user->getUuid();

            if (!array_key_exists('filters', $options)) {
                $options['filters'] = [];
            }

            if (DataSource::CONTEXT_WORKSPACE === $event->getContext() && (empty($options['filters'] || empty($options['filters']['workspace'])))) {
                $options['hiddenFilters']['workspace'] = $event->getWorkspace()->getUuid();
            }
        } else {
            throw new AccessDeniedException();
        }

        $eventPresences = $this->finder->search(EventPresence::class, $options);

        $event->setData($eventPresences);

        $event->stopPropagation();
    }
}
