<?php

namespace Claroline\CoreBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Event\DataSource\DataSourceEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class ResourceSource
{
    /** @var FinderProvider */
    private $finder;

    /**
     * ResourceSource constructor.
     *
     * @DI\InjectParams({
     *     "finder" = @DI\Inject("claroline.api.finder")
     * })
     *
     * @param FinderProvider $finder
     */
    public function __construct(
        FinderProvider $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @DI\Observe("data_source_resources_list")
     *
     * @param DataSourceEvent $event
     */
    public function listResources(DataSourceEvent $event)
    {
        $event->setData(
            $this->finder->search('Claroline\CoreBundle\Resource\ResourceNode', $event->getOptions())
        );

        $event->stopPropagation();
    }
}
