<?php

namespace Innova\PathBundle\Manager;

use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Library\Security\Utilities;
use Symfony\Component\Finder\Exception\AccessDeniedException;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Innova\PathBundle\Entity\Path\Path;

/**
 * Path Manager
 * Manages life cycle of paths
 * @author Innovalangues <contact@innovalangues.net>
 */
class PathManager
{
    /**
     * Current entity manage for data persist
     * @var \Doctrine\Common\Persistence\ObjectManager $om
     */
    protected $om;

    /**
     * claro resource manager
     * @var \Claroline\CoreBundle\Manager\ResourceManager
     */
    protected $resourceManager;

    /**
     * Current security context
     * @var \Symfony\Component\Security\Core\SecurityContextInterface $security
     */
    protected $security;

    /**
     * Class constructor - Inject required services
     * @param \Doctrine\Common\Persistence\ObjectManager                $objectManager
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
     * @param \Claroline\CoreBundle\Manager\ResourceManager             $resourceManager
     * @param \Claroline\CoreBundle\Library\Security\Utilities          $utils
     */
    public function __construct(
        ObjectManager            $objectManager,
        SecurityContextInterface $securityContext,
        ResourceManager          $resourceManager,
        Utilities                $utils)
    {
        $this->om              = $objectManager;
        $this->security        = $securityContext;
        $this->resourceManager = $resourceManager;
        $this->utils           = $utils;
    }

    /**
     * Check if a user has sufficient rights to execute action on Path
     * @param  string                                           $action
     * @param  \Innova\PathBundle\Entity\Path\Path              $path
     * @param  \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @throws \Symfony\Component\Finder\Exception\AccessDeniedException
     */
    public function checkAccess($action, Path $path, Workspace $workspace = null)
    {
        if (false === $this->isAllow($action, $path, $workspace)) {
            throw new AccessDeniedException();
        }
    }

    /**
     * Return if a user has sufficient rights to execute action on Path
     * @param  string                                           $actionName
     * @param  \Innova\PathBundle\Entity\Path\Path              $path
     * @param  \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @return boolean
     */
    public function isAllow($actionName, Path $path, Workspace $workspace = null)
    {
        if ($workspace && $actionName === 'CREATE') {
            $toolRepo = $this->om->getRepository('ClarolineCoreBundle:Role');
            $managerRole = $toolRepo->findManagerRole($workspace);

            return $this->security->isGranted($managerRole->getName());
        }

        $collection = new ResourceCollection(array ($path->getResourceNode()));

        return $this->security->isGranted($actionName, $collection);
    }

    /**
     * Get path resource type entity
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceType
     */
    public function getResourceType()
    {
        return $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('innova_path');
    }

    /**
     * Get a workspace from id
     * @param  integer $workspaceId
     * @return \Claroline\CoreBundle\Entity\Workspace\Workspace
     */
    public function getWorkspace($workspaceId)
    {
        return $this->om->getRepository('ClarolineCoreBundle:Workspace\Workspace')->find($workspaceId);
    }

    /**
     * Get all Paths of the Platform
     * @param bool $toPublish If false, returns all paths, if true returns only paths which need publishing
     */
    public function getPlatformPaths($toPublish = false)
    {
        return $this->om->getRepository('InnovaPathBundle:Path\Path')->findPlatformPaths($toPublish);
    }

    /**
     * Get all Paths of a Workspace
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param bool $toPublish If false, returns all paths, if true returns only paths which need publishing
     */
    public function getWorkspacePaths(Workspace $workspace, $toPublish = false)
    {
        return $this->om->getRepository('InnovaPathBundle:Path\Path')->findWorkspacePaths($workspace, $toPublish);
    }

    /**
     * Find accessible Paths
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @return array
     */
    public function findAccessibleByUser(Workspace $workspace = null)
    {
        $roots = array ();
        if (!empty($workspace)) {
            $root = $this->resourceManager->getWorkspaceRoot($workspace);
            $roots[] = $root->getPath();
        }

        $token = $this->security->getToken();
        $userRoles = $this->utils->getRoles($token);

        $entities = $this->om->getRepository('InnovaPathBundle:Path\Path')->findAccessibleByUser($roots, $userRoles);

        // Check edit and delete access for paths
        $paths = array ();
        foreach ($entities as $entity) {
            $paths[] = array (
                'entity'    => $entity,
                'canEdit'   => $this->isAllow('EDIT', $entity),
                'canDelete' => $this->isAllow('DELETE', $entity),
            );
        }

        return $paths;
    }

    /**
     * Create a new path
     * @param  \Innova\PathBundle\Entity\Path\Path $path
     * @param  \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @return \Innova\PathBundle\Entity\Path\Path
     */
    public function create(Path $path, Workspace $workspace)
    {
        // Check if JSON structure is built
        $structure = $path->getStructure();
        if (empty($structure)) {
            // Initialize path structure
            $path->initializeStructure();
        }

        // Persist Path
        $this->om->persist($path);
        $this->om->flush();

        // Create a new resource node
        $parent = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findWorkspaceRoot($workspace);
        $path = $this->resourceManager->create($path, $this->getResourceType(), $this->security->getToken()->getUser(), $workspace, $parent, null);

        return $path;
    }

    /**
     * Edit existing path
     * @param  \Innova\PathBundle\Entity\Path\Path $path
     * @return \Innova\PathBundle\Entity\Path\Path
     */
    public function edit(Path $path)
    {
        // Check if JSON structure is built
        $structure = $path->getStructure();
        if (empty($structure)) {
            // Initialize path structure
            $path->initializeStructure();
        }

        // Set path as modified (= need publishing to be able to play path with new modifs)
        $path->setModified(true);
        $this->om->persist($path);

        // Update resource node if needed
        $resourceNode = $path->getResourceNode();
        if ($path->getName() !== $resourceNode->getName()) {
            // Path name as changed => rename linked resource node
            $resourceNode->setName($path->getName());
            $this->om->persist($resourceNode);
        }

        $this->om->flush();

        return $path;
    }

    /**
     * Delete path
     * @param  \Innova\PathBundle\Entity\Path\Path $path
     * @return boolean
     * @throws \Exception
     */
    public function delete(Path $path)
    {
        // User can delete current path
        $this->om->remove($path->getResourceNode());
        $this->om->flush();

        return $this;
    }
}
