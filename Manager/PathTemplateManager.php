<?php

namespace Innova\PathBundle\Manager;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Path templates Manager
 * Manages life cycle of templates
 * @author Innovalangues <contact@innovalangues.net>
 *
 */
class PathTemplateManager
{
    /**
     * Current entity manage for data persist
     * @var \Doctrine\ORM\EntityManagerEntity Manager $em
     */
    protected $em;
    
    /**
     * Current request
     * @var \Symfony\Component\HttpFoundation\Request $request
     */
    protected $request;
    
    /**
     * Class constructor - Inject required services
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }
    
    /**
     * Inject current request
     * Request is not injected in class constructor to have current request each time we call this service
     * @param Request $request
     * @return \Innova\PathBundle\Manager\PathTemplateManager
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
        
        return $this;
    }
    
    public function create()
    {
        
    }
    
    public function edit()
    {
        
    }
    
    public function delete()
    {
        
    }
    
    /**
     * Check if wanted name is unique
     * @param string $name
     * @return string
     */
    public function checkNameIsUnique($name)
    {
        // Create query
        $dql  = 'SELECT COUNT(p) ';
        $dql .= 'FROM Innova\PathBundle\Entity\PathTemplate AS p ';
        $dql .= '  AND p.name = :pathTemplateName ';
        $query = $this->em->createQuery($dql);
    
        // Set query parameters
        $query->setParameter('pathTemplateName', trim($name));
    
        // Get results
        $count = $query->getSingleScalarResult();
    
        $return = true;
        if (!empty($count) && $count > 0) {
            // A path already have wanted name
            $return = false;
        }
    
        return $return;
    }
}