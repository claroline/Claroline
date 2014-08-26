<?php

namespace UJM\ExoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * InteractionMatching Controller
 * 
 */
class InteractionMatchingController extends Controller
{
    /**
     * Lists all InteractionQCM entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        
        $entities = $em->getRepository('UJMExoBundle:InteractionMatching')->findAll();
        
        return $this->render(
                'UJMExoBundle:InteractionMatching.html.twig', array(
                'entities' => $entities
                )
        );
    }
    
    public function showAction()
    {
        
    }
    
    public function createAction()
    {
        
    }
    
    public function updateAction()
    {
        
    }
    
    public function deleteAction()
    {
        
    }
    
    public function responseMatchingAction()
    {
        
    }
}