<?php

namespace Claroline\CoreBundle\Library\Browsing;

use Symfony\Component\Routing\Router;
use Claroline\CoreBundle\Exception\ClarolineException;
use Doctrine\ORM\EntityManager;

class ClaroRouting
{
    /** 
     * @var Router 
     */
    private $router;
    
    /**
     * @var EntityManager 
     */    
    private $em;
    
    public function __construct(Router $router, EntityManager $em)
    {
        $this->router=$router;
        $this->em=$em;
        
    }
    
    public function getRouteName($vendor, $bundle, $controller, $method)
    {
        $controller = "{$vendor}\\{$bundle}\\Controller\\{$controller}Controller::{$method}Action";
        $route = $this->findRoute($controller);
        
        return $route;
    }
    
    private function findRoute($controller)
    {
        $routes = array();
        
        foreach ($this->router->getRouteCollection()->all() as $name => $route) 
        {
            $routes[$name] = $route->compile();
            $arr=$routes[$name]->getDefaults();
           
            if(0 != count($arr) && $controller == $arr['_controller'])
            {
                return $name;
            }
        }  
        
        throw new ClarolineException("controller {$controller} was not found");
    }
    
    public function getAllFormRoutes()
    {
        $routes = array();
        
        $resourcesType = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();
        
        foreach($resourcesType as $resourceType)
        {       
            $routes[$resourceType->getType()]=$this->getRouteName($resourceType->getVendor(), $resourceType->getBundle(), $resourceType->getController(), 'form');
        }
        
        return $routes;
    }
}

