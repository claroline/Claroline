<?php

namespace Claroline\CoreBundle\Library\Browsing;

use Symfony\Component\Routing\Router;
use Claroline\CoreBundle\Exception\ClarolineException;

class ClaroRouting
{
    /** 
     * @var Router 
     */
    private $router;
    
    public function __construct(Router $router)
    {
        $this->router=$router;
    }
    
    public function getRouteName($bundle, $controller, $method)
    {
        $controller = "Claroline\\{$bundle}\\Controller\\{$controller}Controller::{$method}Action";
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
   
}

