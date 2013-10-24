<?php

namespace Innova\PathBundle\Manager;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PathManager
{
    protected $em;
    protected $request;
    protected $security;
    protected $user;
    
    /**
     * Class constructor - Inject required services
     * @param EntityManager $entityManager
     * @param SecurityContext $securityContext
     */
    public function __construct(EntityManager $entityManager, SecurityContext $securityContext)
    {
        $this->em = $entityManager;
        $this->security = $securityContext;
        
        // Retrieve current user
        $this->user = $this->security->getToken()->getUser();
    }
    
    /**
     * Inject current request
     * Request is not injected in class constructor to have current request each time we call this service
     * @param Request $request
     * @return \Innova\PathBundle\Manager\PathManager
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
        return $this;
    }
    
    /**
     * Create a new path
     */
    public function create()
    {
        // Get current workspace
//         $workspaceId = $this->request->get('workspaceId');
//         $workspace = $this->em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->findOneById($workspaceId);
        
//         // création du dossier _paths s'il existe pas.
//         if (!$pathsDirectory = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneByName("_paths")) {
//             $pathsDirectory = new ResourceNode();
//             $pathsDirectory->setName("_paths");
//             $pathsDirectory->setClass("Claroline\CoreBundle\Entity\Resource\Directory");
//             $pathsDirectory->setCreator($this->user);
//             $pathsDirectory->setResourceType($this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneById(2));
//             $root = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findWorkspaceRoot($workspace);
//             $pathsDirectory->setWorkspace($workspace);
//             $pathsDirectory->setParent($root);
//             $pathsDirectory->setMimeType("custom/directory");
//             $pathsDirectory->setIcon($this->em->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findOneById(7));
        
//             $this->em->persist($pathsDirectory);
//             $this->em->flush();
//         }
        
//         $resourceNode = new ResourceNode();
//         $resourceNode->setClass('Innova\PathBundle\Entity\Path');
//         $resourceNode->setCreator($this->user);
//         $resourceNode->setResourceType($this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('path'));
//         $resourceNode->setWorkspace($workspace);
//         $resourceNode->setParent($pathsDirectory);
//         $resourceNode->setMimeType('');
//         $resourceNode->setIcon($this->em->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findOneById(1));
//         $resourceNode->setName('Path');
        
//         $pathName = $this->get('request')->request->get('pathName');
//         $content = $this->get('request')->request->get('path');
        
//         $new_path = new Path;
//         $new_path->setPath($content);
//         $resourceNode->setName($pathName);
//         $new_path->setResourceNode($resourceNode);
        
//         $this->em->persist($resourceNode);
//         $this->em->persist($new_path);
//         $this->em->flush();
    }
    
    /**
     * Edit existing path
     * @return string|null
     * @throws NotFoundHttpException
     */
    public function edit()
    {
        $pathId = null;
        
        // Load current path
        $path = $this->em->getRepository('InnovaPathBundle:Path')->findOneByResourceNode($this->request->get('id'));
        if ($path) {
            // Path found
            // Update resource node 
            $resourceNode = $path->getResourceNode();
            $resourceNode->setName($this->request->get('pathName'));
            $this->em->persist($resourceNode);
        
            // Update path
            $path->setPath($this->request->get('path'));
            $path->setModified(true);
            $this->em->persist($path);
            
            // Write data into DB
            $this->em->flush();
        
            $pathId = $resourceNode->getId();
        }
        else {
            // Path not found
            throw new NotFoundHttpException('The path you want to edit does not exist.');
        }
        
        return $pathId;
    }
    
    /**
     * Delete path
     * @return boolean
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function delete()
    {
        $isDeleted = false;
        
        // Load current path
        $path = $this->em->getRepository('InnovaPathBundle:Path')->findOneByResourceNode($this->request->get('id'));
        if (!empty($path)) {
            // Path found
            $pathCreator = $path->getResourceNode()->getCreator();
            
            // Check if current user can delete this path
            if ($pathCreator == $this->user) {
                // User can delete current path
                $this->em->remove($path->getResourceNode());
                $this->em->flush();
            
                $isDeleted = true;
            }
            else {
                // User can't delete path from other users
                throw new Exception('You can delete only your own paths.');
            }
        }
        else {
            // Path not found
            throw new NotFoundHttpException('The path you want to delete can not be found.');
        }
        
        return $isDeleted;
    }
    
    /**
     * Check if wanted name is unique for current user and current Workspace
     */
    public function checkNameIsUnique()
    {
        
    }
}