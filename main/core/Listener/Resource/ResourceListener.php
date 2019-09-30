<?php

namespace Claroline\CoreBundle\Listener\Resource;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\ResourceActionEvent;
use Claroline\CoreBundle\Manager\Resource\ResourceLifecycleManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
     * @param LoadResourceEvent $event
     */
    public function load(LoadResourceEvent $event)
    {
        $resourceNode = $event->getResourceNode();

        // propagate event to resource type
        $subEvent = $this->lifecycleManager->load($resourceNode);

        $event->setData(array_merge([
            // we need the full workspace object for some rendering config
            // (we only have access to the minimal version of the WS in the node)
            'workspace' => $resourceNode->getWorkspace() ? $this->serializer->serialize($resourceNode->getWorkspace()) : null,
            'resourceNode' => $this->serializer->serialize($resourceNode),
            'managed' => $this->manager->isManager($resourceNode),
            'userEvaluation' => null, // todo flag evaluated resource types and auto load Evaluation if any
        ], $subEvent->getData()));
    }

    /**
     * @param ResourceActionEvent $event
     */
    public function create(ResourceActionEvent $event)
    {
        // forward to the resource type
        $this->lifecycleManager->create($event->getResourceNode());
    }

    /**
     * @param ResourceActionEvent $event
     */
    public function open(ResourceActionEvent $event)
    {
        // forward to the resource type
        $this->lifecycleManager->open($event->getResourceNode());
    }

    /**
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
     * @param ResourceActionEvent $event
     */
    public function rights(ResourceActionEvent $event)
    {
        // forward to the resource type
        $options = [];

        $params = $event->getOptions();

        if (isset($params['recursive']) && 'true' === $params['recursive']) {
            $options[] = Options::IS_RECURSIVE;
        }

        $data = $event->getData();
        $this->crud->update(ResourceNode::class, $data, $options);
        $this->lifecycleManager->rights($event->getResourceNode(), $event->getData());

        $event->setResponse(new JsonResponse(
            $this->serializer->serialize($event->getResourceNode())
        ));
    }

    /**
     * @param ResourceActionEvent $event
     */
    public function edit(ResourceActionEvent $event)
    {
        $this->lifecycleManager->edit($event->getResourceNode());
    }

    /**
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
     * @param ResourceActionEvent $event
     */
    public function export(ResourceActionEvent $event)
    {
        $this->lifecycleManager->export($event->getResourceNode());
    }

    /**
     * @param ResourceActionEvent $event
     */
    public function delete(ResourceActionEvent $event)
    {
        $options = $event->getOptions();

        if (isset($options['hard']) && is_string($options['hard'])) {
            $hard = 'true' === $options['hard'] ? true : false;
        } else {
            if (isset($options['hard'])) {
                $hard = $options['hard'];
            } else {
                $hard = false;
            }
        }

        $this->manager->delete($event->getResourceNode(), false, !$hard);

        $event->setResponse(
            new JsonResponse(null, 204)
        );
    }

    /**
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
