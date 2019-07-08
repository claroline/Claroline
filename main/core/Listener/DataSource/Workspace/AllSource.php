<?php

namespace Claroline\CoreBundle\Listener\DataSource\Workspace;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * List all the workspaces (excluding models) visible by the current user.
 *
 * @DI\Service
 */
class AllSource
{
    /** @var FinderProvider */
    private $finder;

    /**
     * AllSource constructor.
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

        $options['hiddenFilters']['model'] = false;
        $options['hiddenFilters']['hidden'] = false;

        $event->setData(
            $this->finder->search(Workspace::class, $options)
        );

        $event->stopPropagation();
    }
}
