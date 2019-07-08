<?php

namespace Claroline\CoreBundle\Listener\DataSource\Workspace;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * List the public workspaces of the platform.
 *
 * A workspace is considered public if :
 *      - It's displayable in lists.
 *      - It's not a model.
 *      - Self-registration is enabled.
 *
 * NB. It does not check if the user is already registered to it.
 *
 * @DI\Service
 */
class PublicSource
{
    /** @var FinderProvider */
    private $finder;

    /** @var TokenStorage */
    private $tokenStorage;

    /**
     * PublicSource constructor.
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
     * @DI\Observe("data_source.public_workspaces.load")
     *
     * @param GetDataEvent $event
     */
    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions();

        $options['hiddenFilters']['hidden'] = false;
        $options['hiddenFilters']['model'] = false;
        $options['hiddenFilters']['selfRegistration'] = true;
        $options['hiddenFilters']['sameOrganization'] = true;

        $event->setData(
            $this->finder->search(Workspace::class, $options, [Options::SERIALIZE_LIST])
        );

        $event->stopPropagation();
    }
}
