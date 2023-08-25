<?php

namespace Claroline\CommunityBundle\Subscriber\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class GroupSourceSubscriber implements EventSubscriberInterface
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
            'data_source.groups.load' => 'getData',
        ];
    }

    public function getData(GetDataEvent $event): void
    {
        $options = $event->getOptions();
        if (DataSource::CONTEXT_WORKSPACE === $event->getContext()) {
            if (!$this->authorization->isGranted('OPEN', $event->getWorkspace())) {
                throw new AccessDeniedException();
            }

            $options['hiddenFilters']['workspace'] = $event->getWorkspace()->getUuid();
        }

        $event->setData(
            $this->finder->search(Group::class, $options)
        );

        $event->stopPropagation();
    }
}
