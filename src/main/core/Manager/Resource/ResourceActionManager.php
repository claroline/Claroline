<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager\Resource;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\MenuAction;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Event\CatalogEvents\ResourceEvents;
use Claroline\CoreBundle\Event\Resource\ResourceActionEvent;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Repository\Resource\ResourceActionRepository;
use Claroline\CoreBundle\Security\Collection\ResourceCollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * ResourceActionManager.
 * Manages and executes implemented actions on resources.
 *
 * NB. Resource actions can be defined through plugins config.yml.
 */
class ResourceActionManager
{
    private ResourceActionRepository $repository;

    /**
     * @var MenuAction[]
     */
    private array $actions = [];

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly ObjectManager $om,
        private readonly ResourceManager $resourceManager
    ) {
        $this->repository = $this->om->getRepository(MenuAction::class);
    }

    /**
     * Checks if the resource node supports an action.
     */
    public function support(ResourceNode $resourceNode, string $actionName, string $method): bool
    {
        $action = $this->get($resourceNode, $actionName);

        if (empty($action) || !in_array($method, $action->getApi())) {
            return false;
        }

        return true;
    }

    /**
     * Executes an action on a resource.
     */
    public function execute(ResourceNode $resourceNode, string $actionName, array $options = [], array $content = null, array $files = null): Response
    {
        $resourceAction = $this->get($resourceNode, $actionName);
        $resource = $this->resourceManager->getResourceFromNode($resourceNode);

        $event = new ResourceActionEvent($resource, $options, $content, $files, $resourceNode);
        $this->dispatcher->dispatch($event, ResourceEvents::getEventName($actionName, $resourceAction->getResourceType()?->getName()));

        return $event->getResponse();
    }

    /**
     * Retrieves the correct action instance for resource.
     */
    public function get(ResourceNode $resourceNode, string $actionName): ?MenuAction
    {
        $nodeActions = $this->all($resourceNode->getResourceType());
        foreach ($nodeActions as $current) {
            if ($actionName === $current->getName()) {
                return $current;
            }
        }

        return null;
    }

    /**
     * Gets all actions available for a resource type.
     *
     * @return MenuAction[]
     */
    public function all(ResourceType $resourceType): array
    {
        if (empty($this->actions)) {
            $this->load();
        }

        // get all actions implemented for the resource
        $actions = array_filter($this->actions, function (MenuAction $action) use ($resourceType) {
            return empty($action->getResourceType()) || $resourceType->getId() === $action->getResourceType()->getId();
        });

        return array_values($actions);
    }

    /**
     * Checks if the current user can execute an action on a resource.
     */
    public function hasPermission(MenuAction $action, ResourceCollection $resourceNodes): bool
    {
        return $this->authorization->isGranted($action->getDecoder(), $resourceNodes);
    }

    /**
     * Loads all resource actions enabled in the platform.
     */
    private function load(): void
    {
        // preload the list of actions available for all resource types
        // it will avoid having to load it for each node
        // this is safe because the only way to change actions is through
        // the platform install/update process
        $this->actions = $this->repository->findAll(true);
    }
}
