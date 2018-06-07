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

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\MenuAction;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Event\Resource\ResourceActionEvent;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Doctrine\Common\Persistence\ObjectRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * ResourceActionManager.
 * Manages and executes implemented actions on resources.
 *
 * NB. Resource actions can be defined through plugins config.yml.
 *
 * @DI\Service("claroline.manager.resource_action")
 */
class ResourceActionManager
{
    /** @var ObjectManager */
    private $om;

    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var StrictDispatcher */
    private $dispatcher;

    /** @var ObjectRepository */
    private $repository;

    /**
     * @var MenuAction[]
     */
    private $actions = [];

    /**
     * ResourceMenuManager constructor.
     *
     * @DI\InjectParams({
     *     "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "dispatcher"    = @DI\Inject("claroline.event.event_dispatcher")
     * })
     *
     * @param ObjectManager                 $om
     * @param AuthorizationCheckerInterface $authorization
     * @param StrictDispatcher              $dispatcher
     */
    public function __construct(
        ObjectManager $om,
        AuthorizationCheckerInterface $authorization,
        StrictDispatcher $dispatcher)
    {
        $this->om = $om;
        $this->authorization = $authorization;
        $this->dispatcher = $dispatcher;

        $this->repository = $this->om->getRepository('ClarolineCoreBundle:Resource\MenuAction');
    }

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
     *
     * @param ResourceNode $resourceNode
     * @param string       $actionName
     * @param array        $options
     * @param array        $content
     *
     * @return Response
     */
    public function execute(ResourceNode $resourceNode, string $actionName, array $options = [], array $content = null): Response
    {
        $resourceAction = $this->get($resourceNode, $actionName);

        /** @var ResourceActionEvent $event */
        $event = $this->dispatcher->dispatch(
            static::eventName($actionName, $resourceAction->getResourceType()),
            ResourceActionEvent::class,
            [$options, $content] // todo : pass current resource
        );

        return $event->getResponse();
    }

    /**
     * Retrieves the correct action instance for resource.
     *
     * @param ResourceNode $resourceNode
     * @param string       $actionName
     *
     * @return MenuAction
     */
    public function get(ResourceNode $resourceNode, string $actionName)
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
     * @param ResourceType $resourceType
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
     *
     * @param MenuAction         $action
     * @param ResourceCollection $resourceNodes
     *
     * @return bool
     */
    public function hasPermission(MenuAction $action, ResourceCollection $resourceNodes): bool
    {
        return $this->authorization->isGranted($action->getDecoder(), $resourceNodes);
    }

    /**
     * Generates the names for resource actions events.
     *
     * @param string       $actionName
     * @param ResourceType $resourceType
     *
     * @return string
     */
    private static function eventName($actionName, ResourceType $resourceType = null): string
    {
        if (!empty($resourceType)) {
            // This is an action only available for the current type
            return 'resource.'.$resourceType->getName().'.'.$actionName;
        }

        // This is an action available for all resource types
        return 'resource.'.$actionName;
    }

    private function load()
    {
        // preload the list of actions available for all resource types
        // it will avoid having to load it for each node
        // this is safe because the only way to change actions is through
        // the platform install/update process
        $this->actions = $this->repository->findAll(true);
    }
}
