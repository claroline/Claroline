<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Listener\DataSource;

use Claroline\AgendaBundle\Entity\Event;
use Claroline\AgendaBundle\Entity\Task;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AgendaSource
{
    public function __construct(
        private readonly FinderProvider $finder,
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public function getEventsData(GetDataEvent $event): void
    {
        $options = $event->getOptions() ?: [];

        if (DataSource::CONTEXT_WORKSPACE === $event->getContext()) {
            $options['hiddenFilters']['workspaces'] = [$event->getWorkspace()->getUuid()];
        } elseif (DataSource::CONTEXT_HOME === $event->getContext()) {
            $options['hiddenFilters']['anonymous'] = true;
        } else {
            $options['hiddenFilters']['user'] = $this->tokenStorage->getToken()?->getUser()->getUuid();
        }

        $event->setData(
            $this->finder->search(Event::class, $options)
        );

        $event->stopPropagation();
    }

    public function getTasksData(GetDataEvent $event): void
    {
        $options = $event->getOptions() ?: [];

        if (DataSource::CONTEXT_WORKSPACE === $event->getContext()) {
            $options['hiddenFilters']['workspaces'] = [$event->getWorkspace()->getUuid()];
        } elseif (DataSource::CONTEXT_HOME === $event->getContext()) {
            $options['hiddenFilters']['anonymous'] = true;
        } else {
            $options['hiddenFilters']['user'] = $this->tokenStorage->getToken()?->getUser()->getUuid();
        }

        $event->setData(
            $this->finder->search(Task::class, $options)
        );

        $event->stopPropagation();
    }
}
