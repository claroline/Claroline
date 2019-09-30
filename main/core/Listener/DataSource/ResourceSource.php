<?php

namespace Claroline\CoreBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Claroline\CoreBundle\Repository\ResourceNodeRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

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
     * @param ObjectManager  $om
     * @param FinderProvider $finder
     * @param TokenStorage   $tokenStorage
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
     * @param GetDataEvent $event
     */
    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions();
        $options['hiddenFilters']['hidden'] = false;

        if (DataSource::CONTEXT_WORKSPACE === $event->getContext()) {
            //parent allow to fetch things outside of the workspace.
            if (!isset($options['filters']) || !array_key_exists('parent', $options['filters'])) {
                // only grab workspace root directory content
                /** @var ResourceNode $workspaceRoot */
                $workspaceRoot = $this->repository->findOneBy([
                    'parent' => null,
                    'workspace' => $event->getWorkspace(),
                ]);

                $options['hiddenFilters']['path.after'] = $workspaceRoot->getPath();
            }
        }

        $options['hiddenFilters']['active'] = true;
        $options['hiddenFilters']['resourceTypeEnabled'] = true;

        $roles = DataSource::CONTEXT_HOME === $event->getContext() ?
            ['ROLE_ANONYMOUS'] :
            array_map(
                function ($role) { return $role->getRole(); },
                $this->tokenStorage->getToken()->getRoles()
            );

        if (!in_array('ROLE_ADMIN', $roles)) {
            $options['hiddenFilters']['roles'] = $roles;
        }

        $event->setData($this->finder->search(ResourceNode::class, $options));

        $event->stopPropagation();
    }
}
