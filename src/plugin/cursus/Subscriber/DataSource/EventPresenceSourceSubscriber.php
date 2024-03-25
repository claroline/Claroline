<?php

namespace Claroline\CursusBundle\Subscriber\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Claroline\CursusBundle\Entity\EventPresence;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class EventPresenceSourceSubscriber implements EventSubscriberInterface
{
    private AuthorizationCheckerInterface $authorization;
    private FinderProvider $finder;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        FinderProvider $finder
    ) {
        $this->authorization = $authorization;
        $this->finder = $finder;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'data_source.event_presences.load' => 'getData',
        ];
    }

    public function getData(GetDataEvent $event): void
    {
        $options = $event->getOptions();

        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        if (!array_key_exists('filters', $options)) {
            $options['filters'] = [];
        }

        if (DataSource::CONTEXT_WORKSPACE === $event->getContext() && (empty($options['filters'] || empty($options['filters']['workspace'])))) {
            $options['hiddenFilters']['workspace'] = $event->getWorkspace()->getUuid();
        }

        $eventPresences = $this->finder->search(EventPresence::class, $options);

        $event->setData($eventPresences);

        $event->stopPropagation();
    }
}
