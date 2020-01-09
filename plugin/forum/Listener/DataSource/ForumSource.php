<?php

namespace Claroline\ForumBundle\Listener\DataSource;

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Claroline\ForumBundle\Entity\Message;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ForumSource
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var FinderProvider */
    private $finder;

    /**
     * ForumSource constructor.
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
     * @param GetDataEvent $event
     */
    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions() ? $event->getOptions() : [];
        $options['hiddenFilters']['moderation'] = false;

        $roles = DataSource::CONTEXT_HOME === $event->getContext() ?
            ['ROLE_ANONYMOUS'] :
            array_map(
                function ($role) { return $role->getRole(); },
                $this->tokenStorage->getToken()->getRoles()
            );

        if (!in_array('ROLE_ADMIN', $roles)) {
            $options['hiddenFilters']['roles'] = $roles;
        }

        if (DataSource::CONTEXT_WORKSPACE === $event->getContext()) {
            $options['hiddenFilters']['workspace'] = $event->getWorkspace()->getUuid();
        }

        $event->setData(
            $this->finder->search(Message::class, $options)
        );

        $event->stopPropagation();
    }
}
