<?php

namespace Claroline\CoreBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * @DI\Service
 */
class AdminToolSource
{
    /** @var FinderProvider */
    private $finder;

    /** @var TokenStorage */
    private $tokenStorage;

    /**
     * AdminToolSource constructor.
     *
     * @DI\InjectParams({
     *     "finder"       = @DI\Inject("claroline.api.finder"),
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     *
     * @param FinderProvider $finder
     * @param TokenStorage   $tokenStorage
     */
    public function __construct(FinderProvider $finder, TokenStorage $tokenStorage)
    {
        $this->finder = $finder;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @DI\Observe("data_source.admin_tools.load")
     *
     * @param GetDataEvent $event
     */
    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions();
        $user = $this->tokenStorage->getToken()->getUser();
        $roles = 'anon.' === $user ?
            ['ROLE_ANONYMOUS'] :
            $user->getRoles();

        if (!in_array('ROLE_ADMIN', $roles)) {
            $options['hiddenFilters']['roles'] = $roles;
        }

        $event->setData($this->finder->search(AdminTool::class, $options));
        $event->stopPropagation();
    }
}
