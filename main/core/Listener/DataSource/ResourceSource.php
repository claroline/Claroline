<?php

namespace Claroline\CoreBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\DataSource\DataSourceEvent;
use Claroline\CoreBundle\Repository\ResourceNodeRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * @DI\Service
 */
class ResourceSource
{
    /** @var ResourceNodeRepository */
    private $repository;

    /** @var FinderProvider */
    private $finder;

    /** @var TokenStorage */
    private $tokenStorage;

    /**
     * ResourceSource constructor.
     *
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "finder"       = @DI\Inject("claroline.api.finder"),
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     *
     * @param ObjectManager  $om
     * @param FinderProvider $finder
     * @param TokenStorage   $tokenSorage
     */
    public function __construct(
        ObjectManager $om,
        FinderProvider $finder,
        TokenStorage $tokenStorage
    ) {
        $this->repository = $om->getRepository(ResourceNode::class);
        $this->finder = $finder;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @DI\Observe("data_source.resources.load")
     *
     * @param DataSourceEvent $event
     */
    public function getData(DataSourceEvent $event)
    {
        $options = $event->getOptions();
        $options['hiddenFilters']['hidden'] = false;

        if (DataSource::CONTEXT_WORKSPACE === $event->getContext()) {
            // only grab workspace root directory content
            /** @var ResourceNode $workspaceRoot */
            $workspaceRoot = $this->repository->findOneBy([
                'parent' => null,
                'workspace' => $event->getWorkspace(),
            ]);

            $options['hiddenFilters']['parent'] = $workspaceRoot->getId();
        }

        $roles = array_map(
            function ($role) { return $role->getRole(); },
            $this->tokenStorage->getToken()->getRoles()
        );

        if (!in_array('ROLE_ADMIN', $roles)) {
            $options['hiddenFilters']['roles'] = $roles;
        }

        $event->setData(
            $this->finder->search(ResourceNode::class, $options)
        );

        $event->stopPropagation();
    }
}
