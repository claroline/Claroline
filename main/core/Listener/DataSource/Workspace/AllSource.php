<?php

namespace Claroline\CoreBundle\Listener\DataSource\Workspace;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;

/**
 * List all the workspaces (excluding models) visible by the current user.
 */
class AllSource
{
    /** @var FinderProvider */
    private $finder;

    /**
     * AllSource constructor.
     *
     * @param FinderProvider $finder
     */
    public function __construct(
        FinderProvider $finder
    ) {
        $this->finder = $finder;
    }

    /**
     * @param GetDataEvent $event
     */
    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions();
        $options['hiddenFilters']['hidden'] = false;

        if (DataSource::CONTEXT_HOME === $event->getContext()) {
            $options['hiddenFilters']['model'] = false;
            $options['hiddenFilters']['personal'] = false;
        }

        $event->setData(
            $this->finder->search(Workspace::class, $options)
        );

        $event->stopPropagation();
    }
}
