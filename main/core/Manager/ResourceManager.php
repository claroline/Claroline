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
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Exception\ExportResourceException;
use Claroline\CoreBundle\Exception\ResourceMoveException;
use Claroline\CoreBundle\Exception\ResourceNotFoundException;
use Claroline\CoreBundle\Exception\ResourceTypeNotFoundException;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\Resource\ResourceLifecycleManager;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
use Claroline\CoreBundle\Repository\ResourceNodeRepository;
use Claroline\CoreBundle\Repository\ResourceTypeRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @todo clean me
 */
class ResourceManager
{
    use LoggableTrait;

    /** @var RightsManager */
    private $rightsManager;
    /** @var ResourceTypeRepository */
    private $resourceTypeRepo;
    /** @var ResourceNodeRepository */
    private $resourceNodeRepo;

    /** @var UserRepository */
    private $userRepo;
    /** @var RoleRepository */
    private $roleRepo;
    /** @var RoleManager */
    private $roleManager;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var ObjectManager */
    private $om;
    /** @var ClaroUtilities */
    private $ut;
    /** @var Utilities */
    private $secut;
    /* @var TranslatorInterface */
    private $translator;
    /* @var PlatformConfigurationHandler */
    private $platformConfigHandler;
    private $filesDirectory;
    /* @var ContainerInterface */
    private $container;
    /** @var SerializerProvider */
    private $serializer;
    /** @var ResourceLifecycleManager */
    private $lifeCycleManager;
    /** @var Crud */
    private $crud;

    /**
     * ResourceManager constructor.
     *
     * @param RoleManager                  $roleManager
     * @param ContainerInterface           $container
     * @param RightsManager                $rightsManager
     * @param StrictDispatcher             $dispatcher
     * @param ObjectManager                $om
     * @param ClaroUtilities               $ut
     * @param Utilities                    $secut
     * @param TranslatorInterface          $translator
     * @param PlatformConfigurationHandler $platformConfigHandler
     * @param SerializerProvider           $serializer
     * @param ResourceLifecycleManager     $lifeCycleManager
     * @param Crud                         $crud
     */
    public function __construct(
        RoleManager $roleManager,
        ContainerInterface $container,
        RightsManager $rightsManager,
        StrictDispatcher $dispatcher,
        ObjectManager $om,
        ClaroUtilities $ut,
        Utilities $secut,
        TranslatorInterface $translator,
        PlatformConfigurationHandler $platformConfigHandler,
        SerializerProvider $serializer,
        ResourceLifecycleManager $lifeCycleManager,
        Crud $crud
    ) {
        $this->om = $om;

        $this->roleManager = $roleManager;
        $this->rightsManager = $rightsManager;
        $this->dispatcher = $dispatcher;
        $this->ut = $ut;
        $this->secut = $secut;
        $this->container = $container;
        $this->translator = $translator;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->filesDirectory = $container->getParameter('claroline.param.files_directory');
        $this->serializer = $serializer;
        $this->lifeCycleManager = $lifeCycleManager;
        $this->crud = $crud;
        $this->resourceTypeRepo = $om->getRepository(ResourceType::class);
        $this->resourceNodeRepo = $om->getRepository(ResourceNode::class);
        $this->userRepo = $om->getRepository(User::class);
        $this->roleRepo = $om->getRepository(Role::class);
    }

    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Creates a resource.
     *
     * array $rights should be defined that way:
     * array('ROLE_WS_XXX' => array('open' => true, 'edit' => false, ...
     * 'create' => array('directory', ...), 'role' => $entity))
     *
     * @param AbstractResource $resource
     * @param ResourceType     $resourceType
     * @param User             $creator
     * @param Workspace        $workspace
     * @param ResourceNode     $parent
     * @param array            $rights
     * @param bool             $isPublished
     * @param bool             $createRights
     *
     * @deprecated: use directory listener: onAdd instead ? I don't know. This is weird.
     *
     * @return AbstractResource
     */
    public function create(
        AbstractResource $resource,
        ResourceType $resourceType,
        User $creator,
        Workspace $workspace = null,
        ResourceNode $parent = null,
        array $rights = [],
        $isPublished = true,
        $createRights = true
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
        $node->setCreator($creator);
        if (!$workspace && $parent && $parent->getWorkspace()) {
            $workspace = $parent->getWorkspace();
        }
        if ($workspace) {
            $node->setWorkspace($workspace);
        }
        $node->setParent($parent);
        $node->setName($this->getUniqueName($node, $parent));
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
     * Gets a unique name for a resource in a folder.
     * If the name of the resource already exists here, ~*indice* will be appended
     * to its name.
     *
     * @param ResourceNode $node
     * @param ResourceNode $parent
     * @param bool         $isCopy
     *
     * @return string
     */
    public function getUniqueName(ResourceNode $node, ResourceNode $parent = null, $isCopy = false)
    {
        $candidateName = $node->getName();
        $nodeType = $node->getResourceType();
        //if the parent is null, then it's a workspace root and the name is always correct
        //otherwise we fetch each workspace root with the findBy and the UnitOfWork won't be happy...
        if (!$parent) {
            return $candidateName;
        }

        $parent = $parent ?: $node->getParent();
        $sameLevelNodes = $parent ?
            $parent->getChildren() :
            $this->resourceNodeRepo->findBy(['parent' => null]);
        $siblingNames = [];

        foreach ($sameLevelNodes as $levelNode) {
            if (!$isCopy && $levelNode === $node) {
                // without that condition, a node which is "renamed" with the
                // same name is also incremented
                continue;
            }
            if ($levelNode->getResourceType() === $nodeType) {
                $siblingNames[] = $levelNode->getName();
            }
        }

        if (!in_array($candidateName, $siblingNames)) {
            return $candidateName;
        }

        $candidateRoot = pathinfo($candidateName, PATHINFO_FILENAME);
        $candidateExt = ($ext = pathinfo($candidateName, PATHINFO_EXTENSION)) ? '.'.$ext : '';
        $candidatePattern = '/^'
            .preg_quote($candidateRoot)
            .'~(\d+)'
            .preg_quote($candidateExt)
            .'$/';
        $previousIndex = 0;

        foreach ($siblingNames as $name) {
            if (preg_match($candidatePattern, $name, $matches) && $matches[1] > $previousIndex) {
                $previousIndex = $matches[1];
            }
        }

        return $candidateRoot.'~'.++$previousIndex.$candidateExt;
    }

    /**
     * Set the right of a resource.
     * If $rights = array(), the $parent node rights will be copied.
     *
     * array $rights should be defined that way:
     * array('ROLE_WS_XXX' => array('open' => true, 'edit' => false, ...
     * 'create' => array('directory', ...), 'role' => $entity))
     *
     * @param ResourceNode $node
     * @param ResourceNode $parent
     * @param array        $rights
     *
     * @return ResourceNode
     */
    public function setRights(
        ResourceNode $node,
        ResourceNode $parent = null,
        array $rights = []
    ) {
        if (0 === count($rights) && null !== $parent) {
            $node = $this->rightsManager->copy($parent, $node);
        } else {
            $this->createRights($node, $rights);
        }

        return $node;
    }

    /**
     * Create the rights for a node.
     *
     * array $rights should be defined that way:
     * array('ROLE_WS_XXX' => array('open' => true, 'edit' => false, ...
     * 'create' => array('directory', ...), 'role' => $entity))
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     * @param array                                              $rights
     * @param bool                                               $withDefault
     */
    public function createRights(ResourceNode $node, array $rights = [], $withDefault = true, $log = true)
    {
        foreach ($rights as $data) {
            $resourceTypes = $this->checkResourceTypes($data['create']);
            $this->rightsManager->create($data, $data['role'], $node, false, $resourceTypes, $log);
        }
        if ($withDefault) {
            if (!array_key_exists('ROLE_ANONYMOUS', $rights)) {
                $this->rightsManager->create(
                    0,
                    $this->roleRepo->findOneBy(['name' => 'ROLE_ANONYMOUS']),
                    $node,
                    false,
                    [],
                    $log
                );
            }
            if (!array_key_exists('ROLE_USER', $rights)) {
                $this->rightsManager->create(
                    0,
                    $this->roleRepo->findOneBy(['name' => 'ROLE_USER']),
                    $node,
                    false,
                    [],
                    $log
                );
            }
        }
    }

    /**
     * Checks if an array of resource type name exists.
     * Expects an array of types array(array('name' => 'type'),...).
     *
     * @param array $resourceTypes
     *
     * @return array
     *
     * @throws ResourceTypeNotFoundException
     */
    public function checkResourceTypes(array $resourceTypes)
    {
        $validTypes = [];
        $unknownTypes = [];

        foreach ($resourceTypes as $type) {
            //@todo write findByNames method.
            $rt = $this->resourceTypeRepo->findOneBy(['name' => $type['name']]);
            if (null === $rt) {
                $unknownTypes[] = $type['name'];
            } else {
                $validTypes[] = $rt;
            }
        }

        if (count($unknownTypes) > 0) {
            $content = 'The resource type(s) ';
            foreach ($unknownTypes as $unknown) {
                $content .= "{$unknown}, ";
            }
            $content .= 'were not found';

            throw new ResourceTypeNotFoundException($content);
        }

        return $validTypes;
    }

    /**
     * @param ResourceNode $node
     * @param bool         $detach
     */
    public function reorder(ResourceNode $node, $detach = false)
    {
        /** @var \Claroline\CoreBundle\Repository\ResourceNodeRepository $resourceNodeRepository */
        $resourceNodeRepository = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode');
        $children = $resourceNodeRepository->getChildren($node, true, 'index');
        $index = 1;

        foreach ($children as $child) {
            $child->setIndex($index);
            ++$index;
            $this->om->persist($child);
        }

        $this->om->flush();

        if ($detach) {
            foreach ($children as $child) {
                $this->om->detach($child);
            }
        }
    }

    /**
     * Moves a resource.
     *
     * @param ResourceNode $child  currently treated node
     * @param ResourceNode $parent old parent
     *
     * @throws ResourceMoveException
     *
     * @return ResourceNode
     */
    public function move(ResourceNode $child, ResourceNode $parent)
    {
        if ($parent === $child) {
            throw new ResourceMoveException('You cannot move a directory into itself');
        }

        $descendants = $this->resourceNodeRepo->findDescendants($child);
        foreach ($descendants as $descendant) {
            if ($parent === $descendant) {
                throw new ResourceMoveException('You cannot move a directory into its descendants');
            }
        }

        $this->om->startFlushSuite();
        $this->setLastIndex($parent, $child);
        $child->setParent($parent);
        $child->setName($this->getUniqueName($child, $parent));

        if ($child->getWorkspace()->getId() !== $parent->getWorkspace()->getId()) {
            $this->updateWorkspace($child, $parent->getWorkspace());
        }

        $this->om->persist($child);
        $this->om->endFlushSuite();
        $this->dispatcher->dispatch('log', 'Log\LogResourceMove', [$child, $parent]);

        return $child;
    }

    /**
     * Set the $node at the last position of the $parent.
     *
     * @param ResourceNode $parent
     * @param ResourceNode $node
     * @param bool         $autoFlush
     */
    public function setLastIndex(ResourceNode $parent, ResourceNode $node, $autoFlush = true)
    {
        $max = $this->resourceNodeRepo->findLastIndex($parent);
        $node->setIndex($max + 1);

        $this->om->persist($node);

        if ($autoFlush) {
            $this->om->flush();
        }
    }

    /**
     * Copies a resource in a directory.
     *
     * @deprecated use crud instead
     *
     * @param ResourceNode $node
     * @param ResourceNode $parent
     * @param User         $user
     *
     * @return ResourceNode
     *
     * @throws ResourceNotFoundException
     */
    public function copy(
        ResourceNode $node,
        ResourceNode $parent,
        User $user
    ) {
        $check = ['activity', 'claroline_scorm_12', 'claroline_scorm_2004'];

        if (in_array($node->getResourceType()->getName(), $check)) {
            return null;
        }

        $this->log("Copying {$node->getName()} from type {$node->getResourceType()->getName()}");

        /** @var ResourceNode $newNode */
        $newNode = $this->crud->copy($node, [OPTIONS::IGNORE_RIGHTS], ['user' => $user, 'parent' => $parent]);

        $this->om->persist($newNode);
        $this->om->flush();

        return $newNode;
    }

    /**
     * Sets the publication flag of a collection of nodes.
     *
     * @param ResourceNode[] $nodes
     * @param bool           $arePublished
     * @param bool           $isRecursive
     *
     * @return ResourceNode[]
     */
    public function setPublishedStatus(array $nodes, $arePublished, $isRecursive = false)
    {
        foreach ($nodes as $node) {
            $this->crud->update(ResourceNode::class, [
              'id' => $node->getUuid(),
              'meta' => ['published' => $arePublished],
            ]);

            //do it on every children aswell
            if ($isRecursive) {
                $descendants = $this->resourceNodeRepo->findDescendants($node);
                $this->setPublishedStatus($descendants, $arePublished, false);
            }
        }

        return $nodes;
    }

    /**
     * Removes a resource.
     *
     * @param ResourceNode $node
     * @param bool         $force
     * @param bool         $softDelete
     *
     * @throws \LogicException
     */
    public function delete(ResourceNode $node, $force = false, $softDelete = false)
    {
        $this->crud->delete($node, $softDelete ? [Options::SOFT_DELETE] : []);
    }

    public function setActive(ResourceNode $node)
    {
        foreach ($node->getChildren() as $child) {
            $this->setActive($child);
        }

        $node->setActive(true);
        $this->om->persist($node);
    }

    /**
     * Returns an archive with the required content.
     *
     * @param ResourceNode[] $elements     - the nodes being exported
     * @param bool           $forceArchive
     *
     * @throws ExportResourceException
     *
     * @return array
     *
     * @todo rename into export
     */
    public function download(array $elements, $forceArchive = false)
    {
        $data = [];

        if (0 === count($elements)) {
            throw new ExportResourceException('No resources were selected.');
        }

        $pathArch = $this->container->get('claroline.manager.temp_file')->generate();

        $archive = new \ZipArchive();
        $archive->open($pathArch, \ZipArchive::CREATE);

        $nodes = $this->expandResources($elements);
        if (!$forceArchive && 1 === count($nodes)) {
            $event = $this->dispatcher->dispatch(
                "download_{$nodes[0]->getResourceType()->getName()}",
                'Resource\DownloadResource',
                [$this->getResourceFromNode($this->getRealTarget($nodes[0]))]
            );
            $extension = $event->getExtension();
            $hasExtension = '' !== pathinfo($nodes[0]->getName(), PATHINFO_EXTENSION);

            $extGuesser = ExtensionGuesser::getInstance();
            $mimeGuesser = MimeTypeGuesser::getInstance();

            if (!$hasExtension) {
                $extension = $extGuesser->guess($nodes[0]->getMimeType());
            }

            $data['name'] = $hasExtension ?
                $nodes[0]->getName() :
                $nodes[0]->getName().'.'.$extension;
            $data['file'] = $event->getItem();
            $data['mimeType'] = $nodes[0]->getMimeType() ? $nodes[0]->getMimeType() : $mimeGuesser->guess($extension);

            return $data;
        }

        if (isset($elements[0])) {
            $currentDir = $elements[0];
        } else {
            $archive->addEmptyDir($elements[0]->getName());
        }

        foreach ($nodes as $node) {
            //we only download is we can...
            if ($this->container->get('security.authorization_checker')->isGranted('EXPORT', $node)) {
                $resource = $this->getResourceFromNode($node);

                if ($resource) {
                    $filename = $this->getRelativePath($currentDir, $node).$node->getName();
                    $resource = $this->getResourceFromNode($node);

                    //if it's a file, we may have to add the extension back in case someone removed it from the name
                    if ('file' === $node->getResourceType()->getName()) {
                        $extension = '.'.pathinfo($resource->getHashName(), PATHINFO_EXTENSION);
                        if (!preg_match("#$extension#", $filename)) {
                            $filename .= $extension;
                        }
                    }

                    if ('directory' !== $node->getResourceType()->getName()) {
                        $event = $this->dispatcher->dispatch(
                            "download_{$node->getResourceType()->getName()}",
                            'Resource\DownloadResource',
                            [$resource]
                        );

                        $obj = $event->getItem();

                        if (null !== $obj) {
                            $archive->addFile($obj, iconv($this->ut->detectEncoding($filename), $this->getEncoding(), $filename));
                        } else {
                            $archive->addFromString(iconv($this->ut->detectEncoding($filename), $this->getEncoding(), $filename), '');
                        }
                    } else {
                        $archive->addEmptyDir(iconv($this->ut->detectEncoding($filename), $this->getEncoding(), $filename));
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

    /**
     * Returns every children of every resource (includes the startnode).
     *
     * @param ResourceNode[] $nodes
     *
     * @return ResourceNode[]
     *
     * @throws \Exception
     */
    public function expandResources(array $nodes)
    {
        $dirs = [];
        $ress = [];
        $toAppend = [];

        foreach ($nodes as $node) {
            $resourceTypeName = $node->getResourceType()->getName();
            ('directory' === $resourceTypeName) ? $dirs[] = $node : $ress[] = $node;
        }

        foreach ($dirs as $dir) {
            $children = $this->resourceNodeRepo->findDescendants($dir);

            foreach ($children as $child) {
                if ($child->isActive() &&
                    'directory' !== $child->getResourceType()->getName()) {
                    $toAppend[] = $child;
                }
            }
        }

        $merge = array_merge($toAppend, $ress);

        return $merge;
    }

    /**
     * Gets the relative path between 2 instances (not optimized yet).
     *
     * @param ResourceNode $root
     * @param ResourceNode $node
     *
     * @return string
     */
    private function getRelativePath(ResourceNode $root, ResourceNode $node, $path = '')
    {
        if ($node->getParent() !== $root->getParent() && null !== $node->getParent()) {
            $path = $node->getParent()->getName().DIRECTORY_SEPARATOR.$path;
            $path = $this->getRelativePath($root, $node->getParent(), $path);
        }

        return $path;
    }

    /**
     * Renames a node.
     *
     * @param ResourceNode $node
     * @param string       $name
     * @param bool         $noFlush
     *
     * @return ResourceNode
     *
     * @deprecated
     */
    public function rename(ResourceNode $node, $name, $noFlush = false)
    {
        $node->setName($name);
        $name = $this->getUniqueName($node, $node->getParent());
        $node->setName($name);
        $this->om->persist($node);
        $this->logChangeSet($node);

        if (!$noFlush) {
            $this->om->flush();
        }

        return $node;
    }

    /**
     * Logs every change on a node.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     */
    public function logChangeSet(ResourceNode $node)
    {
        $uow = $this->om->getUnitOfWork();
        $uow->computeChangeSets();
        $changeSet = $uow->getEntityChangeSet($node);

        if (count($changeSet) > 0) {
            $this->dispatcher->dispatch(
                'log',
                'Log\LogResourceUpdate',
                [$node, $changeSet]
            );
        }
    }

    /**
     * @param int $id
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode
     */
    public function getNode($id)
    {
        return $this->resourceNodeRepo->find($id);
    }

    /**
     * @param Workspace $workspace
     *
     * @return ResourceNode
     */
    public function getWorkspaceRoot(Workspace $workspace)
    {
        return $this->resourceNodeRepo->findWorkspaceRoot($workspace);
    }

    /**
     * @param string $name
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceType
     */
    public function getResourceTypeByName($name)
    {
        return $this->resourceTypeRepo->findOneBy(['name' => $name]);
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceType[]
     */
    public function getAllResourceTypes()
    {
        return $this->resourceTypeRepo->findAll();
    }

    /**
     * @param Workspace $workspace
     *
     * @return ResourceNode[]
     *
     * @deprecated use finder instead
     */
    public function getByWorkspace(Workspace $workspace)
    {
        return $this->resourceNodeRepo->findBy(['workspace' => $workspace]);
    }

    /**
     * @param int[] $ids
     * @param bool  $orderStrict, keep the same order as ids array
     *
     * @return ResourceNode[]
     *
     * @deprecated use finder instead
     */
    public function getByIds(array $ids, $orderStrict = false)
    {
        $nodes = $this->om->findByIds(
            'Claroline\CoreBundle\Entity\Resource\ResourceNode',
            $ids,
            $orderStrict
        );

        return $nodes;
    }

    /**
     * @param mixed $id
     *
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
     *
     * @param ResourceNode $node
     *
     * @return AbstractResource
     */
    public function getResourceFromNode(ResourceNode $node)
    {
        try {
            /* @var AbstractResource $resource */
            $resource = $this->om->getRepository($node->getClass())->findOneBy(['resourceNode' => $node]);

            return $resource;
        } catch (\Exception $e) {
            $this->log('class '.$node->getClass().' doesnt exists', 'error');
        }
    }

    private function getEncoding()
    {
        return 'UTF-8//TRANSLIT';
    }

    private function updateWorkspace(ResourceNode $node, Workspace $workspace)
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
     * Check if a ResourceNode can be added in a Workspace (resource amount limit).
     *
     * @todo move into workspace manager
     *
     * @param Workspace $workspace
     *
     * @return bool
     */
    public function checkResourceLimitExceeded(Workspace $workspace)
    {
        return $workspace->getMaxUploadResources() < $this->countActiveResources($workspace);
    }

    /**
     * Count the number of resources in a workspace.
     *
     * @param Workspace $workspace
     *
     * @return int
     */
    public function countActiveResources(Workspace $workspace = null)
    {
        return $this->resourceNodeRepo->count([
            'workspace' => $workspace,
            'active' => true,
        ]);
    }

    /**
     * Search a ResourceNode which is persisted but not flushed yet.
     *
     * @param Workspace $workspace
     * @param $name
     * @param ResourceNode $parent
     *
     * @return ResourceNode
     */
    public function getNodeScheduledForInsert(Workspace $workspace, $name, $parent = null)
    {
        $scheduledForInsert = $this->om->getUnitOfWork()->getScheduledEntityInsertions();
        $res = null;

        foreach ($scheduledForInsert as $entity) {
            if ('Claroline\CoreBundle\Entity\Resource\ResourceNode' === get_class($entity)) {
                if ($entity->getWorkspace()->getCode() === $workspace->getCode() &&
                    $entity->getName() === $name &&
                    $entity->getParent() === $parent) {
                    return $entity;
                }
            }
        }

        return $res;
    }

    /**
     * Adds the public file directory in a workspace.
     *
     * @todo move in workspace manager
     * @todo use crud instead
     *
     * @param Workspace $workspace
     *
     * @return Directory
     */
    public function addPublicFileDirectory(Workspace $workspace)
    {
        $directory = new Directory();
        $dirName = $this->translator->trans('my_public_documents', [], 'platform');
        $directory->setName($dirName);
        $directory->setUploadDestination(true);
        $parent = $this->getNodeScheduledForInsert($workspace, $workspace->getName());
        if (!$parent) {
            $parent = $this->resourceNodeRepo->findOneBy(['workspace' => $workspace->getId(), 'parent' => $parent]);
        }
        $role = $this->roleManager->getRoleByName('ROLE_ANONYMOUS');

        /** @var Directory $publicDir */
        $publicDir = $this->create(
            $directory,
            $this->getResourceTypeByName('directory'),
            $workspace->getCreator(),
            $workspace,
            $parent,
            null,
            ['ROLE_ANONYMOUS' => ['open' => true, 'export' => true, 'create' => [], 'role' => $role]],
            true
        );

        return $publicDir;
    }

    public function getLastIndex(ResourceNode $parent)
    {
        try {
            $lastIndex = $this->resourceNodeRepo->findLastIndex($parent);
        } catch (NonUniqueResultException $e) {
            $lastIndex = 0;
        } catch (NoResultException $e) {
            $lastIndex = 0;
        }

        return $lastIndex;
    }

    /**
     * @param ResourceNode $node
     * @param bool         $throwException
     *
     * @return ResourceNode|null
     *
     * @throws \Exception
     *
     * @deprecated
     */
    public function getRealTarget(ResourceNode $node, $throwException = true)
    {
        if ('Claroline\LinkBundle\Entity\Resource\Shortcut' === $node->getClass()) {
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

    public function checkIntegrity()
    {
        $resources = $this->resourceNodeRepo->findAll();
        $batchSize = 500;
        $i = 0;

        /** @var ResourceNode $resource */
        foreach ($resources as $resource) {
            $absRes = $this->getResourceFromNode($resource);

            if (!$absRes) {
                $this->log('Resource '.$resource->getName().' not found. Removing...');
                $this->om->remove($resource);
            } else {
                if (null === $resource->getWorkspace() && $parent = $resource->getParent()) {
                    if ($workspace = $parent->getWorkspace()) {
                        $resource->setWorkspace($workspace);
                        $this->om->persist($workspace);
                        if (0 === $batchSize % $i) {
                            $this->om->flush();
                        }
                    }
                }
            }
            ++$i;
        }

        $this->om->flush();
    }

    public function getNotDeletableResourcesByWorkspace(Workspace $workspace)
    {
        return $this->resourceNodeRepo->findBy(['workspace' => $workspace, 'deletable' => false]);
    }

    /**
     * Find all content for a given user and the replace him by another.
     *
     * @param User $from
     * @param User $to
     *
     * @return int
     */
    public function replaceCreator(User $from, User $to)
    {
        /** @var ResourceNode[] $nodes */
        $nodes = $this->resourceNodeRepo->findBy([
            'creator' => $from,
        ]);

        if (count($nodes) > 0) {
            foreach ($nodes as $node) {
                $node->setCreator($to);
            }

            $this->om->flush();
        }

        return count($nodes);
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
     *
     * @param ResourceNode $resourceNode
     */
    public function restore(ResourceNode $resourceNode)
    {
        $this->setActive($resourceNode);
        $workspace = $resourceNode->getWorkspace();
        if ($workspace) {
            $root = $this->getWorkspaceRoot($workspace);
            $resourceNode->setParent($root);
        }
        $name = substr($resourceNode->getName(), 0, strrpos($resourceNode->getName(), '_'));
        $resourceNode->setName($name);
        $resourceNode->setName($this->getUniqueName($resourceNode));
        $this->om->persist($resourceNode);
        $this->om->flush();
    }

    public function load(ResourceNode $resourceNode, $embedded = false)
    {
        $resource = $this->getResourceFromNode($resourceNode);
        if ($resource) {
            /** @var LoadResourceEvent $event */
            $event = $this->dispatcher->dispatch(
              'resource.load',
              LoadResourceEvent::class,
              [$resource, $embedded]
          );

            return $event->getData();
        }

        throw new ResourceNotFoundException();
    }

    public function isManager(ResourceNode $resourceNode)
    {
        return $this->rightsManager->isManager($resourceNode);
    }
}
