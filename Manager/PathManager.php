<?php

namespace Innova\PathBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\SecurityContext;
use Claroline\CoreBundle\Manager\ResourceManager;

use Symfony\Component\Security\Core\User\UserInterface;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
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
     * @var \Symfony\Component\Security\Core\SecurityContext $security
     */
    protected $security;
    
    /**
     * Authenticated user
     * @var \Claroline\CoreBundle\Entity\User\User $user
     */
    protected $user;
    
    /**
     * Class constructor - Inject required services
     * @param \Doctrine\Common\Persistence\ObjectManager       $objectManager
     * @param \Symfony\Component\Security\Core\SecurityContext $securityContext
     * @param \Claroline\CoreBundle\Manager\ResourceManager    $resourceManager
     */
    public function __construct(
        ObjectManager   $objectManager, 
        SecurityContext $securityContext, 
        ResourceManager $resourceManager)
    {
        $this->om = $objectManager;
        $this->security = $securityContext;
        $this->resourceManager = $resourceManager;
        
        // Retrieve current user
        $this->user = $this->security->getToken()->getUser();
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
     * @return \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace
     */
    public function getWorkspace($workspaceId)
    {
        return $this->om->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);
    }
    
    /**
     * Find all paths for a workspace
     * @param  \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @return array
     */
    public function findAllFromWorkspace(AbstractWorkspace $workspace)
    {
        $paths = array();
        if (!empty($this->user) && $this->user instanceof UserInterface) {
            // User is logged => get his paths
            $paths['me'] = $this->om->getRepository('InnovaPathBundle:Path\Path')->findAllByWorkspaceByUser($workspace, $this->user);
            $paths['others'] = $this->om->getRepository('InnovaPathBundle:Path\Path')->findAllByWorkspaceByNotUser($workspace, $this->user);
        }
        else {
            $paths['me'] = array();
            $paths['others'] = $this->om->getRepository('InnovaPathBundle:Path\Path')->findAllByWorkspace($workspace);
        }
    
        return $paths;
    }
    
    /**
     * Create a new path
     * @param  \Innova\PathBundle\Entity\Path\Path $path
     * @param  \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @return \Innova\PathBundle\Entity\Path\Path
     */
    public function create(Path $path, AbstractWorkspace $workspace)
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
        $path = $this->resourceManager->create($path, $this->getResourceType(), $this->user, $workspace, $parent, null);
        
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

        // Set path as modified (= need publishment to be able to play path with new modifs)
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
        $pathCreator = $path->getResourceNode()->getCreator();
        
        // Check if current user can delete this path
        if ($pathCreator == $this->user) {
            // User can delete current path
            $this->om->remove($path->getResourceNode());
            $this->om->flush();
        }
        else {
            // User can't delete path from other users
            throw new \Exception('You can delete only your own paths.');
        }
        
        return $this;
    }
}