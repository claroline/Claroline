<?php

namespace Claroline\CoreBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\DataSource\DataSourceEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * @DI\Service
 */
class MyWorkspaceSource
{
    /** @var FinderProvider */
    private $finder;

    /** @var TokenStorage */
    private $tokenStorage;

    /**
     * WorkspaceSource constructor.
     *
     * @DI\InjectParams({
     *     "finder"       = @DI\Inject("claroline.api.finder"),
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     *
     * @param FinderProvider $finder
     */
    public function __construct(
        FinderProvider $finder,
        TokenStorage $tokenStorage
    ) {
        $this->finder = $finder;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @DI\Observe("data_source.my_workspaces.load")
     *
     * @param DataSourceEvent $event
     */
    public function getData(DataSourceEvent $event)
    {
        $options = $event->getOptions();
        $options['hiddenFilters']['user'] = $this->tokenStorage->getToken()->getUser()->getId();

        $event->setData(
            $this->finder->search(Workspace::class, $options)
        );

        $event->stopPropagation();
    }
}
