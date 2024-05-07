<?php

namespace Claroline\CoreBundle\Listener\Resource;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\ResourceEvents;
use Claroline\CoreBundle\Event\Resource\EmbedResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\ResourceActionEvent;
use Claroline\CoreBundle\Event\Resource\UpdateResourceEvent;
use Claroline\CoreBundle\Manager\Resource\ResourceLifecycleManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

class ResourceListener
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Environment $templating,
        private readonly ObjectManager $om,
        private readonly Crud $crud,
        private readonly SerializerProvider $serializer,
        private readonly ResourceManager $manager,
        private readonly ResourceLifecycleManager $lifecycleManager
    ) {
    }

    public function load(LoadResourceEvent $event): void
    {
        $resourceNode = $event->getResourceNode();
        $user = $this->tokenStorage->getToken()->getUser();

        // Increment view count if viewer is not creator of the resource
        if (!($user instanceof User) || $user !== $resourceNode->getCreator()) {
            $this->manager->addView($resourceNode);
        }

        $openEvent = new LoadResourceEvent($this->getResourceFromNode($resourceNode), $event->isEmbedded());
        $this->eventDispatcher->dispatch($openEvent, ResourceEvents::getEventName(ResourceEvents::OPEN, $resourceNode->getResourceType()->getName()));

        $event->setData(array_merge($event->getData(), $openEvent->getData()));
    }

    /**
     * Embed the resource in texts.
     * It will generate a link for most of the resources. Some files (images, videos/audios) are directly rendered.
     */
    public function embed(EmbedResourceEvent $event): void
    {
        $resourceNode = $event->getResourceNode();

        // propagate event to resource type
        $subEvent = $this->lifecycleManager->embed($resourceNode);
        if ($subEvent->isPopulated()) {
            $event->setData($subEvent->getData());
        } else {
            $mimeType = explode('/', $resourceNode->getMimeType());

            $view = 'default';
            if ($mimeType[0] && in_array($mimeType[0], ['video', 'audio', 'image'])) {
                $view = $mimeType[0];
            }

            $event->setData($this->templating->render("@ClarolineCore/resource/embed/{$view}.html.twig", [
                'resource' => $event->getResource(),
            ]));
        }
    }

    public function create(ResourceActionEvent $event): void
    {
        // forward to the resource type
        $this->lifecycleManager->create($event->getResourceNode());
    }

    public function configure(ResourceActionEvent $event): void
    {
        $resourceNode = $event->getResourceNode();
        $resource = $this->getResourceFromNode($resourceNode);

        $data = $event->getData();

        $isManager = $this->authorization->isGranted('ADMINISTRATE', $resourceNode);
        if (!empty($data)) {
            $this->om->startFlushSuite();

            if (!empty($data['resourceNode'])) {
                $this->crud->update($resourceNode, $data['resourceNode'], [Options::PERSIST_TAG]);
            }

            if (!empty($data['resource'])) {
                $this->crud->update($resource, $data['resource']);
            }

            if (!empty($data['rights']) && $isManager) {
                $this->crud->update($resourceNode, ['rights' => $data['rights']]);
            }

            $this->om->endFlushSuite();
        }

        $updateResource = new UpdateResourceEvent($resource, $data);
        $this->eventDispatcher->dispatch($updateResource, ResourceEvents::getEventName(ResourceEvents::UPDATE, $resourceNode->getResourceType()->getName()));

        $this->om->refresh($resourceNode);

        $event->setResponse(new JsonResponse(array_merge([], $updateResource->getResponse(), [
            'resourceNode' => $this->serializer->serialize($resourceNode, [Options::NO_RIGHTS]),
            // FIXME : rights are out of date
            'rights' => !empty($data['rights']) && $isManager ? array_map(function (ResourceRights $rights) {
                return $this->serializer->serialize($rights);
            }, $resourceNode->getRights()->toArray()) : []
        ])));

        $event->stopPropagation();
    }

    public function rights(ResourceActionEvent $event): void
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

    public function publish(ResourceActionEvent $event): void
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

    public function unpublish(ResourceActionEvent $event): void
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

    public function export(ResourceActionEvent $event): void
    {
        $this->lifecycleManager->export($event->getResourceNode());
    }

    public function delete(ResourceActionEvent $event): void
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

    public function restore(ResourceActionEvent $event): void
    {
        $this->manager->restore($event->getResourceNode());

        $event->setResponse(
            new JsonResponse($this->serializer->serialize($event->getResourceNode()))
        );
    }

    public function copy(ResourceActionEvent $event): void
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

    public function move(ResourceActionEvent $event): void
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

    private function getResourceFromNode(ResourceNode $resourceNode): ?AbstractResource
    {
        /** @var AbstractResource $resource */
        $resource = $this->om
            ->getRepository($resourceNode->getClass())
            ->findOneBy(['resourceNode' => $resourceNode]);

        return $resource;
    }
}
