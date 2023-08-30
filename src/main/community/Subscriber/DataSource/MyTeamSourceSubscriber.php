<?php

namespace Claroline\CommunityBundle\Subscriber\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CommunityBundle\Entity\Team;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MyTeamSourceSubscriber implements EventSubscriberInterface
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
            'data_source.my-teams.load' => 'getData',
        ];
    }

    public function getData(GetDataEvent $event): void
    {
        $options = $event->getOptions();

        $user = $this->tokenStorage->getToken()->getUser();

        if ($user instanceof User) {
            $options['hiddenFilters']['user'] = $user->getUuid();
            if (DataSource::CONTEXT_WORKSPACE === $event->getContext()) {
                $options['hiddenFilters']['workspace'] = $event->getWorkspace()->getUuid();
            }
        } else {
            throw new AccessDeniedException();
        }

        $event->setData(
            $this->finder->search(Team::class, $options)
        );

        $event->stopPropagation();
    }
}
