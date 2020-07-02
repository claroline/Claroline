<?php

namespace Claroline\CursusBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Claroline\CursusBundle\Entity\CourseSession;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class MySessionSource
{
    /** @var FinderProvider */
    private $finder;

    /** @var TokenStorage */
    private $tokenStorage;

    /**
     * MySessionSource constructor.
     *
     * @param FinderProvider $finder
     * @param TokenStorage   $tokenStorage
     */
    public function __construct(
        FinderProvider $finder,
        TokenStorage $tokenStorage
    ) {
        $this->finder = $finder;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param GetDataEvent $event
     */
    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions();
        $user = $this->tokenStorage->getToken()->getUser();
        $options['hiddenFilters']['user'] = 'anon.' !== $user ? $user->getUuid() : null;

        $event->setData(
            $this->finder->search(CourseSession::class, $options)
        );

        $event->stopPropagation();
    }
}
