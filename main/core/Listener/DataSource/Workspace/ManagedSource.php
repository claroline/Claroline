<?php

namespace Claroline\CoreBundle\Listener\DataSource\Workspace;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * List the workspaces managed by the current user.
 *
 * A workspace is managed by a user if :
 *      - He is the creator of the workspace.
 *      - He has the ROLE_WS_MANAGER_ of the workspace.
 *      - He has a ROLE_ADMIN
 *      - He is an administrator of the organization(s) of the workspace.
 *
 * @DI\Service
 */
class ManagedSource
{
    /** @var FinderProvider */
    private $finder;

    /**
     * ManagedSource constructor.
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
     * @DI\Observe("data_source.managed_workspaces.load")
     *
     * @param GetDataEvent $event
     */
    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions();

        $options['hiddenFilters']['model'] = false;
        $options['hiddenFilters']['administrated'] = true;

        $event->setData(
            $this->finder->search(Workspace::class, $options)
        );

        $event->stopPropagation();
    }
}
