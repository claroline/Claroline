<?php

namespace Claroline\CoreBundle\Listener\Resource;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\ResourceActionEvent;
use Claroline\CoreBundle\Manager\Resource\ResourceLifecycleManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service()
 */
class ResourceListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var Crud */
    private $crud;

    /** @var SerializerProvider */
    private $serializer;

    /** @var ResourceManager */
    private $manager;

    /** @var ResourceLifecycleManager */
    private $lifecycleManager;

    /**
     * ResourceListener constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage"     = @DI\Inject("security.token_storage"),
     *     "crud"             = @DI\Inject("claroline.api.crud"),
     *     "serializer"       = @DI\Inject("claroline.api.serializer"),
     *     "manager"          = @DI\Inject("claroline.manager.resource_manager"),
     *     "lifecycleManager" = @DI\Inject("claroline.manager.resource_lifecycle")
     * })
     *
     * @param TokenStorageInterface    $tokenStorage
     * @param Crud                     $crud
     * @param SerializerProvider       $serializer
     * @param ResourceManager          $manager
     * @param ResourceLifecycleManager $lifecycleManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        Crud $crud,
        SerializerProvider $serializer,
        ResourceManager $manager,
        ResourceLifecycleManager $lifecycleManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->crud = $crud;
        $this->serializer = $serializer;
        $this->manager = $manager;
        $this->lifecycleManager = $lifecycleManager;
    }

    /**
     * @DI\Observe("resource.load")
     *
     * @param LoadResourceEvent $event
     */
    public function load(LoadResourceEvent $event)
    {
        $resourceNode = $event->getResourceNode();

        // propagate event to resource type
        $subEvent = $this->lifecycleManager->load($resourceNode);

        $event->setData(array_merge([
            'resourceNode' => $this->serializer->serialize($resourceNode),
            'managed' => $this->manager->isManager($resourceNode),
            'userEvaluation' => null, // todo flag evaluated resource types and auto load Evaluation if any
        ], $subEvent->getData()));
    }

    /**
     * @DI\Observe("resource.create")
     *
     * @param ResourceActionEvent $event
     */
    public function create(ResourceActionEvent $event)
    {
        // forward to the resource type
        $this->lifecycleManager->create($event->getResourceNode());
    }

    /**
     * @DI\Observe("resource.open")
     *
     * @param ResourceActionEvent $event
     */
    public function open(ResourceActionEvent $event)
    {
        // forward to the resource type
        $this->lifecycleManager->open($event->getResourceNode());
    }

    /**
     * @DI\Observe("resource.about")
     *
     * @param ResourceActionEvent $event
     */
    public function about(ResourceActionEvent $event)
    {
        $event->setResponse(
            new JsonResponse($this->serializer->serialize($event->getResourceNode()))
        );
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("resource.configure")
     *
     * @param ResourceActionEvent $event
     */
    public function configure(ResourceActionEvent $event)
    {
        $data = $event->getData();
        $this->crud->update(ResourceNode::class, $data);

        $event->setResponse(new JsonResponse($data));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("resource.rights")
     *
     * @param ResourceActionEvent $event
     */
    public function rights(ResourceActionEvent $event)
    {
        // forward to the resource type
        $data = $event->getData();
        $this->crud->update(ResourceNode::class, $data);
        $this->lifecycleManager->rights($event->getResourceNode(), $event->getData());

        $event->setResponse(new JsonResponse(
            $this->serializer->serialize($event->getResourceNode())
        ));
    }

    /**
     * @DI\Observe("resource.edit")
     *
     * @param ResourceActionEvent $event
     */
    public function edit(ResourceActionEvent $event)
    {
        $this->lifecycleManager->edit($event->getResourceNode());
    }

    /**
     * @DI\Observe("resource.publish")
     *
     * @param ResourceActionEvent $event
     */
    public function publish(ResourceActionEvent $event)
    {
        $nodes = $this->manager->setPublishedStatus([$event->getResourceNode()], true);

        $event->setResponse(
            new JsonResponse($this->serializer->serialize($nodes[0]))
        );
    }

    /**
     * @DI\Observe("resource.unpublish")
     *
     * @param ResourceActionEvent $event
     */
    public function unpublish(ResourceActionEvent $event)
    {
        $nodes = $this->manager->setPublishedStatus([$event->getResourceNode()], false);

        $event->setResponse(
            new JsonResponse($this->serializer->serialize($nodes[0]))
        );
    }

    /**
     * @DI\Observe("resource.export")
     *
     * @param ResourceActionEvent $event
     */
    public function export(ResourceActionEvent $event)
    {
        $this->lifecycleManager->export($event->getResourceNode());
    }

    /**
     * @DI\Observe("resource.delete")
     *
     * @param ResourceActionEvent $event
     */
    public function delete(ResourceActionEvent $event)
    {
        $this->manager->delete($event->getResourceNode());

        $event->setResponse(
            new JsonResponse(null, 204)
        );
    }

    /**
     * @DI\Observe("resource.restore")
     *
     * @param ResourceActionEvent $event
     */
    public function restore(ResourceActionEvent $event)
    {
        $this->manager->restore($event->getResourceNode());

        $event->setResponse(
            new JsonResponse($this->serializer->serialize($event->getResourceNode()))
        );
    }

    /**
     * @DI\Observe("resource.copy")
     *
     * @param ResourceActionEvent $event
     */
    public function copy(ResourceActionEvent $event)
    {
        $resourceNode = $event->getResourceNode();
        $data = $event->getData();
        $parent = isset($data['destination']['autoId']) && isset($data['destination']['meta']['type']) && 'directory' === $data['destination']['meta']['type'] ?
            $this->manager->getById($data['destination']['autoId']) :
            null;
        $user = $this->tokenStorage->getToken()->getUser();

        if (!empty($parent) && 'anon.' !== $user) {
            $newResource = $this->manager->copy($resourceNode, $parent, $user);
            $event->setResponse(
                new JsonResponse($this->serializer->serialize($newResource->getResourceNode()))
            );
        } else {
            $event->setResponse(
                new JsonResponse(null, 500)
            );
        }
    }

    /**
     * @DI\Observe("resource.move")
     *
     * @param ResourceActionEvent $event
     */
    public function move(ResourceActionEvent $event)
    {
        $resourceNode = $event->getResourceNode();
        $data = $event->getData();
        $parent = isset($data['destination']['autoId']) && isset($data['destination']['meta']['type']) && 'directory' === $data['destination']['meta']['type'] ?
            $this->manager->getById($data['destination']['autoId']) :
            null;

        if (!empty($parent)) {
            $movedResource = $this->manager->move($resourceNode, $parent);
            $event->setResponse(
                new JsonResponse($this->serializer->serialize($movedResource), 200)
            );
        } else {
            $event->setResponse(
                new JsonResponse(null, 500)
            );
        }
    }
}
