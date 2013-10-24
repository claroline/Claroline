<?php

namespace Innova\PathBundle\Manager;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Mockery\CountValidator\Exception;

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
        
    }
    
    /**
     * Edit existing path
     */
    public function edit()
    {
        
    }
    
    /**
     * Delete path
     * @return boolean
     */
    public function delete()
    {
        $isDeleted = false;
        
        // Load current path
        $id = $this->request->get('id');
        $path = $this->em->getRepository('InnovaPathBundle:Path')->findOneByResourceNode($id);
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
            throw new Exception('The path you want to delete can not be found.');
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