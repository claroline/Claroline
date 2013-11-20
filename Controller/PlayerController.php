<?php

namespace Innova\PathBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Innova\PathBundle\Entity\Path;
use Innova\PathBundle\Entity\Step;

/**
 * Player controller
 * @author Innovalangues <contact@innovalangues.net>
 * 
 * @Route(
 *      "",
 *      name="innova_path_player",
 *      service="innova.controller.path_player"
 * )
 */
class PlayerController
{
    /**
     * Current request
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;
    
    /**
     * Inject current request into service
     * @param Request $request
     * @return \Innova\PathBundle\Controller\StepController
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    
        return $this;
    }
    
    /**
     * Display path player
     * @param  Path $path
     * @return array
     * 
     * @Route(
     *      "innova_path_player_index",
     *      name="",
     *      options={"expose" = true}
     * )
     * @Method("GET")
     */
    public function displayAction(Path $path)
    {
        return array ();
    }
    
    /**
     * 
     */
    public function displayBreadcrumbAction()
    {
        return array ();
    }
    
    /**
     * 
     */
    public function displayResourcesAction(Path $path, Step $step)
    {
        return array ();
    }
    
    /**
     * 
     */
    public function displaySquaresBrowser()
    {
        
    }
}