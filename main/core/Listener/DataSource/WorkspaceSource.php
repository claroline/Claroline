<?php

namespace Claroline\CoreBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class WorkspaceSource
{
    /** @var FinderProvider */
    private $finder;

    /**
     * WorkspaceSource constructor.
     *
     * @DI\InjectParams({
     *     "finder" = @DI\Inject("claroline.api.finder")
     * })
     *
     * @param FinderProvider $finder
     */
    public function __construct(
        FinderProvider $finder
    ) {
        $this->finder = $finder;
    }

    /**
     * @DI\Observe("data_source.workspaces.load")
     *
     * @param GetDataEvent $event
     */
    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions();
        $options['hiddenFilters']['hidden'] = false;

        if (DataSource::CONTEXT_HOME === $event->getContext()) {
            $options['hiddenFilters']['displayable'] = true;
            $options['hiddenFilters']['model'] = false;
            $options['hiddenFilters']['personal'] = false;
        }

        $event->setData(
            $this->finder->search(Workspace::class, $options)
        );

        $event->stopPropagation();
    }
}
