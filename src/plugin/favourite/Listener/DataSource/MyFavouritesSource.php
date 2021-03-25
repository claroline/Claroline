<?php

namespace HeVinci\FavouriteBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MyFavouritesSource
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var FinderProvider */
    private $finder;

    /**
     * MyFavouritesSource constructor.
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        FinderProvider $finder
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->finder = $finder;
    }

    public function getData(GetDataEvent $event)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $badges = [];
        if ($user instanceof User) {
            $options = $event->getOptions() ? $event->getOptions() : [];
            $options['hiddenFilters']['recipient'] = $user->getUuid();

            if (DataSource::CONTEXT_WORKSPACE === $event->getContext()) {
                $options['hiddenFilters']['workspace'] = $event->getWorkspace()->getUuid();
            }

            $badges = $this->finder->search(BadgeClass::class, $options);
        }

        $event->setData($badges);

        $event->stopPropagation();
    }
}
