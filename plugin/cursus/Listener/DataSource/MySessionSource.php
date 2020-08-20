<?php

namespace Claroline\CursusBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Claroline\CursusBundle\Entity\CourseSession;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MySessionSource
{
    /** @var FinderProvider */
    private $finder;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * MySessionSource constructor.
     */
    public function __construct(
        FinderProvider $finder,
        TokenStorageInterface $tokenStorage
    ) {
        $this->finder = $finder;
        $this->tokenStorage = $tokenStorage;
    }

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
