<?php

namespace Claroline\OpenBadgeBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MyBadgesSource
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly FinderProvider $finder
    ) {
    }

    public function getData(GetDataEvent $event): void
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $badges = [];
        if ($user instanceof User) {
            $options = $event->getOptions() ? $event->getOptions() : [];
            $options['hiddenFilters']['recipient'] = $user->getUuid();

            if (DataSource::CONTEXT_WORKSPACE === $event->getContext()) {
                $options['hiddenFilters']['workspace'] = $event->getWorkspace()->getUuid();
            }

            $badges = $this->finder->search(Assertion::class, $options);
        }

        $event->setData($badges);

        $event->stopPropagation();
    }
}
