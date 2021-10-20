<?php

namespace Claroline\CursusBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Claroline\CursusBundle\Entity\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MySessionSource
{
    /** @var FinderProvider */
    private $finder;

    /** @var TokenStorageInterface */
    private $tokenStorage;

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

        /** @var User|string $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $options['hiddenFilters']['user'] = $user instanceof User ? $user->getUuid() : null;

        if (DataSource::CONTEXT_WORKSPACE === $event->getContext() && (empty($options['filters'] || empty($options['filters']['workspace'])))) {
            $options['hiddenFilters']['workspace'] = $event->getWorkspace()->getUuid();
        }

        $event->setData(
            $this->finder->search(Session::class, $options)
        );

        $event->stopPropagation();
    }
}
