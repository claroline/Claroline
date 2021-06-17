<?php

namespace Claroline\CoreBundle\Listener\Resource;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\ResourceActionEvent;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use Claroline\CoreBundle\Manager\Resource\ResourceLifecycleManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\EvaluationBundle\Entity\Evaluation\AbstractEvaluation;
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

    /** @var ResourceEvaluationManager */
    private $evaluationManager;

    /**
     * ResourceListener constructor.
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        Crud $crud,
        SerializerProvider $serializer,
        ResourceManager $manager,
        ResourceLifecycleManager $lifecycleManager,
        ResourceEvaluationManager $evaluationManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->crud = $crud;
        $this->serializer = $serializer;
        $this->manager = $manager;
        $this->lifecycleManager = $lifecycleManager;
        $this->evaluationManager = $evaluationManager;
    }

    public function load(LoadResourceEvent $event)
    {
        $resourceNode = $event->getResourceNode();
        $user = $this->tokenStorage->getToken()->getUser();

        // Increment view count if viewer is not creator of the resource
        if (!($user instanceof User) || $user !== $resourceNode->getCreator()) {
            $this->manager->addView($resourceNode);
        }

        // Update current user evaluation
        if ($user instanceof User) {
            $this->evaluationManager->updateResourceUserEvaluationData(
                $resourceNode,
                $user,
                new \DateTime(),
                ['status' => AbstractEvaluation::STATUS_OPENED],
                false,
                true
            );
        }

        // propagate event to resource type
        $subEvent = $this->lifecycleManager->load($resourceNode);

        $event->setData(array_merge([
            'userEvaluation' => null, // TODO : find a way to get current user evaluation here
        ], $subEvent->getData()));
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
        $data = $event->getData();
        $this->crud->update(ResourceNode::class, $data);

        $event->setResponse(new JsonResponse($data));
        $event->stopPropagation();
    }

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

        $event->setResponse(new JsonResponse(
            $this->serializer->serialize($event->getResourceNode())
        ));
    }

    public function edit(ResourceActionEvent $event)
    {
        $this->lifecycleManager->edit($event->getResourceNode());
    }

    public function publish(ResourceActionEvent $event)
    {
        $nodes = $this->manager->setPublishedStatus([$event->getResourceNode()], true);

        $event->setResponse(
            new JsonResponse($this->serializer->serialize($nodes[0]))
        );
    }

    public function unpublish(ResourceActionEvent $event)
    {
        $nodes = $this->manager->setPublishedStatus([$event->getResourceNode()], false);

        $event->setResponse(
            new JsonResponse($this->serializer->serialize($nodes[0]))
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

        if (!empty($parent) && 'anon.' !== $user) {
            $newNode = $this->manager->copy($resourceNode, $parent, $user);

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
