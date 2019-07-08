<?php

namespace Claroline\CoreBundle\Listener\DataSource\Workspace;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * List the models of workspaces available for the current user.
 *
 * @DI\Service
 */
class ModelSource
{
    /** @var FinderProvider */
    private $finder;

    /**
     * ModelSource constructor.
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
     * @DI\Observe("data_source.workspace_models.load")
     *
     * @param GetDataEvent $event
     */
    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions();

        $options['hiddenFilters']['model'] = true;

        $event->setData(
            $this->finder->search(Workspace::class, $options)
        );

        $event->stopPropagation();
    }
}
