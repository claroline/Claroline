<?php

namespace Claroline\CoreBundle\Listener\DataSource\Workspace;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * List the workspaces in which the current user is registered.
 *
 * @DI\Service
 */
class RegisteredSource
{
    /** @var FinderProvider */
    private $finder;

    /** @var TokenStorage */
    private $tokenStorage;

    /**
     * RegisteredSource constructor.
     *
     * @DI\InjectParams({
     *     "finder"       = @DI\Inject("claroline.api.finder"),
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     *
     * @param FinderProvider $finder
     * @param TokenStorage   $tokenStorage
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
     * @param GetDataEvent $event
     */
    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions();

        $options['hiddenFilters']['model'] = false;
        $options['hiddenFilters']['user'] = $this->tokenStorage->getToken()->getUser()->getId();

        $event->setData(
            $this->finder->search(Workspace::class, $options, [Options::SERIALIZE_LIST])
        );

        $event->stopPropagation();
    }
}
