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
use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Event\DataSource\DataSourceEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class AgendaSource
{
    /** @var FinderProvider */
    private $finder;

    /**
     * AgendaSource constructor.
     *
     * @DI\InjectParams({
     *     "finder" = @DI\Inject("claroline.api.finder")
     * })
     *
     * @param FinderProvider $finder
     */
    public function __construct(FinderProvider $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @DI\Observe("data_source.events.load")
     *
     * @param DataSourceEvent $event
     */
    public function getEventsData(DataSourceEvent $event)
    {
        $options = $event->getOptions() ? $event->getOptions() : [];
        $options['hiddenFilters']['types'] = ['event'];

        if (DataSource::CONTEXT_WORKSPACE === $event->getContext()) {
            $options['hiddenFilters']['workspaces'] = [$event->getWorkspace()->getUuid()];
        }
        $event->setData(
            $this->finder->search(Event::class, $options)
        );

        $event->stopPropagation();
    }

    /**
     * @DI\Observe("data_source.tasks.load")
     *
     * @param DataSourceEvent $event
     */
    public function getTasksData(DataSourceEvent $event)
    {
        $options = $event->getOptions() ? $event->getOptions() : [];
        $options['hiddenFilters']['types'] = ['task'];

        if (DataSource::CONTEXT_WORKSPACE === $event->getContext()) {
            $options['hiddenFilters']['workspaces'] = [$event->getWorkspace()->getUuid()];
        }
        $event->setData(
            $this->finder->search(Event::class, $options)
        );

        $event->stopPropagation();
    }
}
