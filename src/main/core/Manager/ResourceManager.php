<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Manager\File\TempFileManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Component\Resource\DownloadableResourceInterface;
use Claroline\CoreBundle\Component\Resource\ResourceProvider;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\CatalogEvents\ResourceEvents;
use Claroline\CoreBundle\Event\Resource\CreateResourceEvent;
use Claroline\CoreBundle\Event\Resource\DownloadResourceEvent;
use Claroline\CoreBundle\Event\Resource\EmbedResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
use Claroline\CoreBundle\Repository\Resource\ResourceNodeRepository;
use Claroline\CoreBundle\Repository\Resource\ResourceTypeRepository;
use Claroline\CoreBundle\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ResourceManager
{
    private ResourceTypeRepository $resourceTypeRepo;
    private ResourceNodeRepository $resourceNodeRepo;
    private RoleRepository $roleRepo;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly SerializerProvider $serializer,
        private readonly RightsManager $rightsManager,
        private readonly ObjectManager $om,
        private readonly TempFileManager $tempManager,
        private readonly ResourceProvider $resourceProvider,
        private readonly Crud $crud
    ) {
        $this->resourceTypeRepo = $om->getRepository(ResourceType::class);
        $this->resourceNodeRepo = $om->getRepository(ResourceNode::class);
        $this->roleRepo = $om->getRepository(Role::class);
    }

    public function createResource(ResourceNode $parent, array $data): AbstractResource
    {
        $nodeData = $data['resourceNode'];
        $resourceData = !empty($data['resource']) ? $data['resource'] : [];

        // checks if the current user can add
        $collection = new ResourceCollection([$parent], ['type' => $nodeData['meta']['type']]);
        if (!$this->authorization->isGranted('CREATE', $collection)) {
            throw new AccessDeniedException('Cannot create resource.');
        }

        $this->om->startFlushSuite();

        // initialize resource node Entity
        try {
            $resourceNode = new ResourceNode();
            $resourceNode->setParent($parent);
            $resourceNode->setWorkspace($parent->getWorkspace());

            $this->crud->create($resourceNode, $nodeData, [Options::NO_RIGHTS, Options::PERSIST_TAG]);
        } catch (InvalidDataException $e) {
            // for resource creation, we submit the resourceNode and resource data at once
            // we need to update the errors' path for correct rendering in form
            $errors = array_map(function (array $error) {
                return [
                    'path' => 'resourceNode/'.ltrim($error['path'], '/'),
                    'message' => $error['message'],
                ];
            }, $e->getErrors());

            throw new InvalidDataException(sprintf('%s is not valid', ResourceNode::class), $errors);
        }

        // initialize custom resource Entity
        $resourceClass = $resourceNode->getResourceType()->getClass();

        try {
            /** @var AbstractResource $resource */
            $resource = new $resourceClass();
            $resource->setResourceNode($resourceNode);

            $this->crud->create($resource, $resourceData, [Options::PERSIST_TAG]);
        } catch (InvalidDataException $e) {
            // for resource creation, we submit the resourceNode and resource data at once
            // we need to update the errors' path for correct rendering in form
            $errors = array_map(function (array $error) {
                return [
                    'path' => 'resource/'.ltrim($error['path'], '/'),
                    'message' => $error['message'],
                ];
            }, $e->getErrors());

            throw new InvalidDataException(sprintf('%s is not valid', $resourceClass), $errors);
        }

        $this->om->endFlushSuite();

        $createResource = new CreateResourceEvent($resource, [
            'resourceNode' => $nodeData,
            'resource' => $resourceData,
        ]);
        // generic event
        $this->eventDispatcher->dispatch($createResource, ResourceEvents::getEventName(ResourceEvents::CREATE));
        // specific event
        $this->eventDispatcher->dispatch($createResource, ResourceEvents::getEventName(ResourceEvents::CREATE, $resourceNode->getResourceType()->getName()));

        // initialize resource rights
        if (!empty($nodeData['rights'])) {
            foreach ($nodeData['rights'] as $rights) {
                /** @var Role $role */
                $role = $this->om->getRepository(Role::class)->findOneBy(['name' => $rights['name']]);

                $creation = [];
                if (!empty($rights['permissions']['create']) && $resource instanceof Directory) {
                    // only forward creation rights to resource which can handle it (only directories atm)
                    $creation = $rights['permissions']['create'];
                }
                $this->rightsManager->update($rights['permissions'], $role, $resourceNode, false, $creation);
            }
        } else {
            // copy parent rights on the new resource
            $this->rightsManager->copy($parent, $resourceNode);
        }

        return $resource;
    }

    /**
     * Creates a resource.
     *
     * array $rights should be defined that way:
     * array('ROLE_WS_XXX' => array('open' => true, 'edit' => false, ...
     * 'create' => array('directory', ...), 'role' => $entity))
     *
     * @deprecated only used by teams
     */
    public function create(
        AbstractResource $resource,
        ResourceType $resourceType,
        User $creator = null,
        Workspace $workspace = null,
        ResourceNode $parent = null,
        array $rights = [],
        bool $isPublished = true,
        bool $createRights = true
    ): AbstractResource {
        $this->om->startFlushSuite();

        $node = new ResourceNode();
        $node->setResourceType($resourceType);
        $node->setPublished($isPublished);
        $mimeType = (null === $resource->getMimeType()) ?
            'custom/'.$resourceType->getName() :
            $resource->getMimeType();
        $node->setMimeType($mimeType);
        $node->setName($resource->getName());
        $node->setCode($this->resourceNodeRepo->findNextUnique('code', $resource->getName()));

        if (!empty($creator)) {
            $node->setCreator($creator);
        } else {
            $node->setCreator($this->tokenStorage->getToken()?->getUser());
        }
        if (!$workspace && $parent && $parent->getWorkspace()) {
            $workspace = $parent->getWorkspace();
        }
        if ($workspace) {
            $node->setWorkspace($workspace);
        }
        $node->setParent($parent);
        if (!is_null($parent)) {
            $node->setAccessibleFrom($parent->getAccessibleFrom());
            $node->setAccessibleUntil($parent->getAccessibleUntil());
        }
        $resource->setResourceNode($node);
        if ($createRights) {
            $this->setRights($node, $parent, $rights);
        }
        $this->om->persist($node);
        $this->om->persist($resource);
        $this->om->endFlushSuite();

        return $resource;
    }

    /**
     * Create the rights for a node.
     *
     * array $rights should be defined that way:
     * array('ROLE_WS_XXX' => array('open' => true, 'edit' => false, ...
     * 'create' => array('directory', ...), 'role' => $entity))
     */
    public function createRights(ResourceNode $node, array $rights = [], bool $withDefault = true): void
    {
        foreach ($rights as $data) {
            $resourceTypes = [];
            if (isset($data['create'])) {
                $resourceTypes = $this->checkResourceTypes($data['create']);
            }

            $this->rightsManager->create($data, $data['role'], $node, false, $resourceTypes);
        }

        if ($withDefault) {
            if (!array_key_exists('ROLE_ANONYMOUS', $rights)) {
                /** @var Role $anonymous */
                $anonymous = $this->roleRepo->findOneBy(['name' => 'ROLE_ANONYMOUS']);

                $this->rightsManager->create(0, $anonymous, $node);
            }

            if (!array_key_exists('ROLE_USER', $rights)) {
                /** @var Role $user */
                $user = $this->roleRepo->findOneBy(['name' => 'ROLE_USER']);

                $this->rightsManager->create(0, $user, $node);
            }
        }
    }

    public function move(ResourceNode $child, ResourceNode $parent): ResourceNode
    {
        if ($parent === $child) {
            throw new \RuntimeException('You cannot move a directory into itself');
        }

        $descendants = $this->resourceNodeRepo->findDescendants($child);
        foreach ($descendants as $descendant) {
            if ($parent === $descendant) {
                throw new \RuntimeException('You cannot move a directory into its descendants');
            }
        }

        $this->om->startFlushSuite();
        $child->setParent($parent);

        if ($child->getWorkspace()->getId() !== $parent->getWorkspace()->getId()) {
            $this->updateWorkspace($child, $parent->getWorkspace());
        }

        $this->om->persist($child);
        $this->om->endFlushSuite();

        return $child;
    }

    /**
     * @param ResourceNode[] $resourceNodes - the nodes being exported
     */
    public function download(array $resourceNodes, ?FileBag $fileBag = null): ?array
    {
        if (!$fileBag) {
            $fileBag = new FileBag();
        }

        foreach ($resourceNodes as $resourceNode) {
            if (!$resourceNode->isDownloadable() || !$resourceNode->isActive() || !$this->authorization->isGranted('OPEN', $resourceNode)) {
                continue;
            }

            $resourceHandler = $this->resourceProvider->getComponent($resourceNode->getResourceType()->getName());
            if ($resourceHandler instanceof DownloadableResourceInterface) {
                $resource = $this->getResourceFromNode($resourceNode);

                $resourceHandler->download($resource, $fileBag);

                $event = new DownloadResourceEvent($resource, $fileBag);
                // generic event
                $this->eventDispatcher->dispatch($event, ResourceEvents::getEventName(ResourceEvents::DOWNLOAD));
                // specific event
                $this->eventDispatcher->dispatch($event, ResourceEvents::getEventName(ResourceEvents::DOWNLOAD, $resourceNode->getResourceType()->getName()));
            }
        }

        if (0 === $fileBag->count()) {
            return null;
        } elseif (1 === $fileBag->count()) {
            $filename = array_keys($fileBag->all())[0];

            return [
                'filename' => TextNormalizer::toFilename($filename),
                'path' => $fileBag->get($filename),
            ];
        }

        $pathArch = $this->tempManager->generate();

        $archive = new \ZipArchive();
        $archive->open($pathArch, \ZipArchive::CREATE);
        foreach ($fileBag->all() as $fileName => $filePath) {
            $archive->addFile(TextNormalizer::toUtf8($filePath), TextNormalizer::toFilename($fileName));
        }

        $archive->close();

        return [
            'filename' => 'resources.zip',
            'path' => $pathArch,
        ];
    }

    public function getWorkspaceRoot(Workspace $workspace): ?ResourceNode
    {
        return $this->resourceNodeRepo->findWorkspaceRoot($workspace);
    }

    public function getResourceTypeByName(string $name): ?ResourceType
    {
        return $this->resourceTypeRepo->findOneBy(['name' => $name]);
    }

    /**
     * @return ResourceType[]
     */
    public function getAllResourceTypes(): array
    {
        return $this->resourceTypeRepo->findAll();
    }

    /**
     * Returns the resource linked to a node.
     */
    public function getResourceFromNode(ResourceNode $node): ?AbstractResource
    {
        /* @var AbstractResource $resource */
        $resource = $this->om->getRepository($node->getClass())->findOneBy(['resourceNode' => $node]);

        return $resource;
    }

    public function addView(ResourceNode $node): ResourceNode
    {
        $this->resourceNodeRepo->addView($node);

        // we do a direct DB call to update nbViews, so we need to refresh the entity
        $this->om->refresh($node);

        return $node;
    }

    public function load(ResourceNode $resourceNode, ?bool $embedded = false): ?array
    {
        $resource = $this->getResourceFromNode($resourceNode);
        $user = $this->tokenStorage->getToken()?->getUser();

        // Increment view count if viewer is not creator of the resource
        if (!($user instanceof User) || $user !== $resourceNode->getCreator()) {
            $this->addView($resourceNode);
        }

        if ($resource) {
            // generic event
            $event = new LoadResourceEvent($resource, $embedded);
            $this->eventDispatcher->dispatch($event, ResourceEvents::getEventName(ResourceEvents::OPEN));

            // specific event
            $openEvent = new LoadResourceEvent($this->getResourceFromNode($resourceNode), $event->isEmbedded());
            $this->eventDispatcher->dispatch($openEvent, ResourceEvents::getEventName(ResourceEvents::OPEN, $resourceNode->getResourceType()->getName()));

            return array_merge([
                'resource' => $this->serializer->serialize($resource),
            ], $event->getData(), $openEvent->getData());
        }

        throw new \RuntimeException(sprintf('Cannot load AbstractResource from ResourceNode (%s).', $resourceNode->getUuid()));
    }

    /**
     * Embed a resource inside a rich text.
     */
    public function embed(ResourceNode $resourceNode): ?string
    {
        $resource = $this->getResourceFromNode($resourceNode);
        if ($resource) {
            /** @var EmbedResourceEvent $event */
            $event = $this->eventDispatcher->dispatch(
                new EmbedResourceEvent($resource),
                ResourceEvents::EMBED
            );

            return $event->getData();
        }

        throw new \RuntimeException(sprintf('Cannot load AbstractResource from ResourceNode (%s).', $resourceNode->getUuid()));
    }

    public function isManager(ResourceNode $resourceNode): bool
    {
        return $this->rightsManager->isManager($resourceNode);
    }

    private function updateWorkspace(ResourceNode $node, Workspace $workspace): void
    {
        $this->om->startFlushSuite();
        $node->setWorkspace($workspace);
        $this->om->persist($node);

        if (!empty($node->getChildren())) {
            // recursively load all children
            $children = $this->resourceNodeRepo->getChildren($node);

            /** @var ResourceNode $child */
            foreach ($children as $child) {
                $child->setWorkspace($workspace);
                $this->om->persist($child);
            }
        }
        $this->om->endFlushSuite();
    }

    /**
     * Checks if an array of resource type name exists.
     * Expects an array of types [['name' => 'type'],...].
     */
    private function checkResourceTypes(array $resourceTypes): array
    {
        $typeNames = array_map(function (array $type) {
            return $type['name'];
        }, $resourceTypes);

        $validTypes = $this->resourceTypeRepo->findByNames($typeNames, false);
        if (count($validTypes) !== count($resourceTypes)) {
            $unknownTypes = array_filter($typeNames, function (string $type) use ($validTypes) {
                foreach ($validTypes as $validType) {
                    if ($type === $validType->getName()) {
                        return false;
                    }
                }

                return true;
            });

            throw new \RuntimeException(sprintf('The resource type(s) %s were not found.', implode(', ', $unknownTypes)));
        }

        return $validTypes;
    }

    /**
     * Set the right of a resource.
     * If $rights = array(), the $parent node rights will be copied.
     *
     * array $rights should be defined that way:
     * array('ROLE_WS_XXX' => array('open' => true, 'edit' => false, ...
     * 'create' => array('directory', ...), 'role' => $entity))
     */
    private function setRights(ResourceNode $node, ResourceNode $parent = null, array $rights = []): void
    {
        if (0 === count($rights) && null !== $parent) {
            $node = $this->rightsManager->copy($parent, $node);
        } else {
            $this->createRights($node, $rights);
        }
    }
}
