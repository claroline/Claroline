<?php

namespace Claroline\OpenBadgeBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service
 */
class MyBadgesSource
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var FinderProvider */
    private $finder;

    /**
     * MyBadgesSource constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage" = @DI\Inject("security.token_storage"),
     *     "finder"       = @DI\Inject("claroline.api.finder")
     * })
     *
     * @param TokenStorageInterface $tokenStorage
     * @param FinderProvider        $finder
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        FinderProvider $finder
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->finder = $finder;
    }

    /**
     * @DI\Observe("data_source.my_badges.load")
     *
     * @param GetDataEvent $event
     */
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
