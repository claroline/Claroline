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
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Manager\File\TempFileManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\CatalogEvents\ResourceEvents;
use Claroline\CoreBundle\Event\Resource\DownloadResourceEvent;
use Claroline\CoreBundle\Event\Resource\EmbedResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Exception\ResourceNotFoundException;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
use Claroline\CoreBundle\Repository\Resource\ResourceNodeRepository;
use Claroline\CoreBundle\Repository\Resource\ResourceTypeRepository;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;

class ResourceManager implements LoggerAwareInterface
{
    use LoggableTrait;

    private AuthorizationCheckerInterface $authorization;
    private StrictDispatcher $dispatcher;
    private ObjectManager $om;
    private Crud $crud;
    private RightsManager $rightsManager;
    private TempFileManager $tempManager;
    private Security $security;

    private ResourceTypeRepository $resourceTypeRepo;
    private ResourceNodeRepository $resourceNodeRepo;
    private RoleRepository $roleRepo;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        RightsManager $rightsManager,
        StrictDispatcher $dispatcher,
        ObjectManager $om,
        Crud $crud,
        TempFileManager $tempManager,
        Security $security
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->rightsManager = $rightsManager;
        $this->dispatcher = $dispatcher;
        $this->crud = $crud;
        $this->tempManager = $tempManager;
        $this->security = $security;

        $this->resourceTypeRepo = $om->getRepository(ResourceType::class);
        $this->resourceNodeRepo = $om->getRepository(ResourceNode::class);
        $this->roleRepo = $om->getRepository(Role::class);
    }

    /**
     * Creates a resource.
     *
     * array $rights should be defined that way:
     * array('ROLE_WS_XXX' => array('open' => true, 'edit' => false, ...
     * 'create' => array('directory', ...), 'role' => $entity))
     *
     * @deprecated: use directory listener: onAdd instead ? I don't know. This is weird.
     *
     * @return AbstractResource
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
    ) {
        $this->om->startFlushSuite();
        /** @var ResourceNode $node */
        $node = new ResourceNode();
        $node->setResourceType($resourceType);
        $node->setPublished($isPublished);
        $mimeType = (null === $resource->getMimeType()) ?
            'custom/'.$resourceType->getName() :
            $resource->getMimeType();
        $node->setMimeType($mimeType);
        $node->setName($resource->getName());
        $node->setCode($this->getUniqueCode($resource->getName()));

        if (!empty($creator)) {
            $node->setCreator($creator);
        } else {
            $node->setCreator($this->security->getUser());
        }
        if (!$workspace && $parent && $parent->getWorkspace()) {
            $workspace = $parent->getWorkspace();
        }
        if ($workspace) {
            $node->setWorkspace($workspace);
        }
        $node->setParent($parent);
        if ($parent) {
            $this->setLastIndex($parent, $node);
        }
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
        $parentPath = '';
        if ($parent) {
            $parentPath .= $parent->getPathForDisplay().' / ';
        }
        $node->setPathForCreationLog($parentPath.$node->getName());
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
    public function createRights(ResourceNode $node, array $rights = [], bool $withDefault = true, bool $log = true)
    {
        foreach ($rights as $data) {
            $resourceTypes = [];
            if (isset($data['create'])) {
                $resourceTypes = $this->checkResourceTypes($data['create']);
            }

            $this->rightsManager->create($data, $data['role'], $node, false, $resourceTypes, $log);
        }

        if ($withDefault) {
            if (!array_key_exists('ROLE_ANONYMOUS', $rights)) {
                /** @var Role $anonymous */
                $anonymous = $this->roleRepo->findOneBy(['name' => 'ROLE_ANONYMOUS']);

                $this->rightsManager->create(0, $anonymous, $node, false, [], $log);
            }

            if (!array_key_exists('ROLE_USER', $rights)) {
                /** @var Role $user */
                $user = $this->roleRepo->findOneBy(['name' => 'ROLE_USER']);

                $this->rightsManager->create(0, $user, $node, false, [], $log);
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
        $this->setLastIndex($parent, $child);
        $child->setParent($parent);

        if ($child->getWorkspace()->getId() !== $parent->getWorkspace()->getId()) {
            $this->updateWorkspace($child, $parent->getWorkspace());
        }

        $this->om->persist($child);
        $this->om->endFlushSuite();
        $this->dispatcher->dispatch('log', 'Log\LogResourceMove', [$child, $parent]);

        return $child;
    }

    /**
     * Returns an archive with the required content.
     *
     * @param ResourceNode[] $elements - the nodes being exported
     */
    public function download(array $elements, ?bool $forceArchive = false): array
    {
        $data = [];

        if (0 === count($elements)) {
            throw new \RuntimeException('No resources were selected.');
        }

        $pathArch = $this->tempManager->generate();

        $archive = new \ZipArchive();
        $archive->open($pathArch, \ZipArchive::CREATE);

        $nodes = $this->expandResources($elements);
        if (!$forceArchive && 1 === count($nodes)) {
            /** @var DownloadResourceEvent $event */
            $event = $this->dispatcher->dispatch(
                "download_{$nodes[0]->getResourceType()->getName()}",
                DownloadResourceEvent::class,
                [$this->getResourceFromNode($this->getRealTarget($nodes[0]))]
            );
            $extension = $event->getExtension();
            $hasExtension = '' !== pathinfo($nodes[0]->getName(), PATHINFO_EXTENSION);

            $mimeTypeGuesser = new MimeTypes();

            if (!$hasExtension) {
                $guessedExtension = $mimeTypeGuesser->getExtensions($nodes[0]->getMimeType());
                if (!empty($guessedExtension)) {
                    $extension = $guessedExtension[0];
                }
            }

            $data['name'] = $hasExtension ?
                $nodes[0]->getName() :
                $nodes[0]->getName().'.'.$extension;
            $data['file'] = $event->getItem();
            $data['mimeType'] = $nodes[0]->getMimeType() ? $nodes[0]->getMimeType() : $mimeTypeGuesser->guessMimeType($event->getItem());

            return $data;
        }

        $currentDir = null;
        if (isset($elements[0])) {
            $currentDir = $elements[0];
        } else {
            $archive->addEmptyDir($elements[0]->getName());
        }

        foreach ($nodes as $node) {
            // we only download is we can...
            if ($this->authorization->isGranted('EXPORT', $node)) {
                $resource = $this->getResourceFromNode($node);

                if ($resource) {
                    $filename = $this->getRelativePath($currentDir, $node).$node->getName();
                    $resource = $this->getResourceFromNode($node);

                    // if it's a file, we may have to add the extension back in case someone removed it from the name
                    if ('file' === $node->getResourceType()->getName()) {
                        $extension = '.'.pathinfo($resource->getHashName(), PATHINFO_EXTENSION);
                        if (!preg_match("#$extension#", $filename)) {
                            $filename .= $extension;
                        }
                    }

                    if ('directory' !== $node->getResourceType()->getName()) {
                        /** @var DownloadResourceEvent $event */
                        $event = $this->dispatcher->dispatch(
                            "download_{$node->getResourceType()->getName()}",
                            DownloadResourceEvent::class,
                            [$resource]
                        );

                        $obj = $event->getItem();

                        if (null !== $obj) {
                            $archive->addFile($obj, TextNormalizer::toUtf8($filename));
                        } else {
                            $archive->addFromString(TextNormalizer::toUtf8($filename), '');
                        }
                    } else {
                        $archive->addEmptyDir(TextNormalizer::toUtf8($filename));
                    }

                    $this->dispatcher->dispatch('log', 'Log\LogResourceExport', [$node]);
                }
            }
        }

        $archive->close();

        $data['name'] = 'archive.zip';
        $data['file'] = $pathArch;
        $data['mimeType'] = 'application/zip';

        return $data;
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
    public function getAllResourceTypes()
    {
        return $this->resourceTypeRepo->findAll();
    }

    /**
     * @return ResourceNode
     */
    public function getById($id)
    {
        /** @var ResourceNode $resourceNode */
        $resourceNode = $this->resourceNodeRepo->findOneBy(['id' => $id]);

        return $resourceNode;
    }

    /**
     * Returns the resource linked to a node.
     */
    public function getResourceFromNode(ResourceNode $node): ?AbstractResource
    {
        try {
            /* @var AbstractResource $resource */
            $resource = $this->om->getRepository($node->getClass())->findOneBy(['resourceNode' => $node]);

            return $resource;
        } catch (\Exception $e) {
            $this->log('class '.$node->getClass().' does not exists', 'error');
        }

        return null;
    }

    public function addView(ResourceNode $node)
    {
        $node->addView();

        $this->om->persist($node);
        $this->om->flush();

        return $node;
    }

    /**
     * Restores a soft deleted resource node.
     */
    public function restore(ResourceNode $resourceNode)
    {
        $this->setActive($resourceNode);
        $workspace = $resourceNode->getWorkspace();
        if ($workspace) {
            // TODO : node should keep its parent when deleted and this should be done only if parent has been deleted too
            $root = $this->getWorkspaceRoot($workspace);
            $resourceNode->setParent($root);
        }

        $this->om->persist($resourceNode);
        $this->om->flush();
    }

    public function load(ResourceNode $resourceNode, $embedded = false)
    {
        $resource = $this->getResourceFromNode($resourceNode);
        if ($resource) {
            /** @var LoadResourceEvent $event */
            $event = $this->dispatcher->dispatch(
                ResourceEvents::RESOURCE_OPEN,
                LoadResourceEvent::class,
                [$resource, $this->security->getUser(), $embedded]
            );

            return $event->getData();
        }

        throw new ResourceNotFoundException();
    }

    /**
     * Embed a resource inside a rich text.
     */
    public function embed(ResourceNode $resourceNode)
    {
        $resource = $this->getResourceFromNode($resourceNode);
        if ($resource) {
            /** @var EmbedResourceEvent $event */
            $event = $this->dispatcher->dispatch(
                ResourceEvents::EMBED,
                EmbedResourceEvent::class,
                [$resource]
            );

            return $event->getData();
        }

        throw new ResourceNotFoundException();
    }

    public function isManager(ResourceNode $resourceNode): bool
    {
        return $this->rightsManager->isManager($resourceNode);
    }

    /**
     * Generates an unique resource code from given one by iterating it.
     */
    public function getUniqueCode(string $code): string
    {
        $existingCodes = $this->resourceNodeRepo->findCodesWithPrefix($code);
        if (empty($existingCodes)) {
            return $code;
        }

        $index = count($existingCodes);
        do {
            ++$index;
            $currentCode = $code.'_'.$index;
            $upperCurrentCode = strtoupper($currentCode);
        } while (in_array($upperCurrentCode, $existingCodes));

        return $currentCode;
    }

    /**
     * @deprecated
     */
    private function getRealTarget(ResourceNode $node, bool $throwException = true): ?ResourceNode
    {
        if ('Claroline\LinkBundle\Entity\Resource\Shortcut' === $node->getClass()) {
            /** @var \Claroline\LinkBundle\Entity\Resource\Shortcut $resource */
            $resource = $this->getResourceFromNode($node);
            if (null === $resource) {
                if ($throwException) {
                    throw new \Exception('The resource was removed.');
                }

                return null;
            }
            $node = $resource->getTarget();
            if (null === $node) {
                if ($throwException) {
                    throw new \Exception('The node target was removed.');
                }

                return null;
            }
        }

        return $node;
    }

    /**
     * Gets the relative path between 2 instances (not optimized yet).
     */
    private function getRelativePath(ResourceNode $root, ResourceNode $node, ?string $path = ''): string
    {
        if ($node->getParent() !== $root->getParent() && null !== $node->getParent()) {
            $path = $node->getParent()->getName().DIRECTORY_SEPARATOR.$path;
            $path = $this->getRelativePath($root, $node->getParent(), $path);
        }

        return $path;
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

    private function setActive(ResourceNode $node): void
    {
        foreach ($node->getChildren() as $child) {
            $this->setActive($child);
        }

        $node->setActive(true);
        $this->om->persist($node);
    }

    /**
     * Set the $node at the last position of the $parent.
     */
    private function setLastIndex(ResourceNode $parent, ResourceNode $node): void
    {
        $max = $this->resourceNodeRepo->findLastIndex($parent);
        $node->setIndex($max + 1);

        $this->om->persist($node);
        $this->om->flush();
    }

    /**
     * Checks if an array of resource type name exists.
     * Expects an array of types array(array('name' => 'type'),...).
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
    private function setRights(ResourceNode $node, ResourceNode $parent = null, array $rights = []): ResourceNode
    {
        if (0 === count($rights) && null !== $parent) {
            $node = $this->rightsManager->copy($parent, $node);
        } else {
            $this->createRights($node, $rights);
        }

        return $node;
    }

    /**
     * Returns every child of every resource (includes the start node).
     *
     * @param ResourceNode[] $nodes
     */
    private function expandResources(array $nodes, bool $onlyActive = false): array
    {
        $resources = [];
        foreach ($nodes as $node) {
            if (!$onlyActive || ($node->isActive() && $node->isPublished())) {
                if ('directory' === $node->getResourceType()->getName() && !empty($node->getChildren())) {
                    $resources = array_merge($resources, $this->expandResources($node->getChildren()->toArray(), true));
                } else {
                    $resources[] = $node;
                }
            }
        }

        return $resources;
    }
}
