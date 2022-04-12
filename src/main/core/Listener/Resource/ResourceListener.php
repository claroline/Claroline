<?php

namespace Claroline\CoreBundle\Listener\Resource;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\User;
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

    public function load(LoadResourceEvent $event)
    {
        $resourceNode = $event->getResourceNode();
        $user = $event->getUser();

        // Increment view count if viewer is not creator of the resource
        if (!($user instanceof User) || $user !== $resourceNode->getCreator()) {
            $this->manager->addView($resourceNode);
        }

        // propagate event to resource type
        $subEvent = $this->lifecycleManager->load($resourceNode);

        $event->setData(array_merge($event->getData(), $subEvent->getData()));
    }

    public function create(ResourceActionEvent $event)
    {
        // forward to the resource type
        $this->lifecycleManager->create($event->getResourceNode());
    }

    public function about(ResourceActionEvent $event)
    {
        $event->setResponse(
            new JsonResponse($this->serializer->serialize($event->getResourceNode(), [Options::NO_RIGHTS]))
        );
        $event->stopPropagation();
    }

    public function configure(ResourceActionEvent $event)
    {
        $resourceNode = $event->getResourceNode();
        $data = $event->getData();

        $this->crud->update($resourceNode, $data);

        $event->setResponse(new JsonResponse(
            $this->serializer->serialize($resourceNode)
        ));
        $event->stopPropagation();
    }

    public function rights(ResourceActionEvent $event)
    {
        $resourceNode = $event->getResourceNode();

        // forward to the resource type
        $options = [];

        $params = $event->getOptions();
        if (isset($params['recursive']) && 'true' === $params['recursive']) {
            $options[] = Options::IS_RECURSIVE;
        }

        $data = $event->getData();
        $this->crud->update($resourceNode, $data, $options);

        $event->setResponse(new JsonResponse(
            $this->serializer->serialize($resourceNode)
        ));
    }

    public function edit(ResourceActionEvent $event)
    {
        $this->lifecycleManager->edit($event->getResourceNode());
    }

    public function publish(ResourceActionEvent $event)
    {
        $resourceNode = $event->getResourceNode();

        $this->crud->update($resourceNode, [
            'id' => $resourceNode->getUuid(),
            'meta' => ['published' => true],
        ]);

        $event->setResponse(
            new JsonResponse($this->serializer->serialize($resourceNode))
        );
    }

    public function unpublish(ResourceActionEvent $event)
    {
        $resourceNode = $event->getResourceNode();

        $this->crud->update($resourceNode, [
            'id' => $resourceNode->getUuid(),
            'meta' => ['published' => false],
        ]);

        $event->setResponse(
            new JsonResponse($this->serializer->serialize($resourceNode))
        );
    }

    public function export(ResourceActionEvent $event)
    {
        $this->lifecycleManager->export($event->getResourceNode());
    }

    public function delete(ResourceActionEvent $event)
    {
        $options = $event->getOptions();
        if (isset($options['hard']) && 'false' === $options['hard']) {
            $options = [Options::SOFT_DELETE];
        } else {
            $options = [];
        }
        $this->crud->delete($event->getResourceNode(), $options);

        $event->setResponse(
            new JsonResponse(null, 204)
        );
    }

    public function restore(ResourceActionEvent $event)
    {
        $this->manager->restore($event->getResourceNode());

        $event->setResponse(
            new JsonResponse($this->serializer->serialize($event->getResourceNode()))
        );
    }

    public function copy(ResourceActionEvent $event)
    {
        $resourceNode = $event->getResourceNode();
        $data = $event->getData();
        $parent = isset($data['destination']['autoId']) && isset($data['destination']['meta']['type']) && 'directory' === $data['destination']['meta']['type'] ?
            $this->manager->getById($data['destination']['autoId']) :
            null;
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        if (!empty($parent) && $user instanceof User) {
            $newNode = $this->crud->copy($resourceNode, [Options::NO_RIGHTS, Crud::NO_PERMISSIONS], ['user' => $user, 'parent' => $parent]);

            $event->setResponse(
                new JsonResponse($this->serializer->serialize($newNode))
            );
        } else {
            $event->setResponse(
                new JsonResponse(null, 500)
            );
        }
    }

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
