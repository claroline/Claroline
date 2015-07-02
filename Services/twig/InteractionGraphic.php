<?php

namespace UJM\ExoBundle\Services\twig;

use Doctrine\Bundle\DoctrineBundle\Registry;

class InteractionGraphic extends \Twig_Extension
{

    protected $dotrine;

    /**
     * Constructor
     *
     * @access public
     *
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine Dependency Injection
     *
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine  = $doctrine;
    }

    public function getName()
    {
        return "InteractionGraphic";
    }

     /**
     * Get functions
     *
     * @access public
     *
     * Return array
     */
    public function getFunctions()
    {
          return array(
              'getCoordsGraphTwig'      => new \Twig_Function_Method($this, 'getCoordsGraphTwig'),
              'goodGraphCoords'         => new \Twig_Function_Method($this, 'goodGraphCoords'),
           );
    }

    /**
     * Get the coords of response zones of an InteractionGraphic
     *
     * @access public
     *
     * @param integer $interGraphId id InteractionGraphic
     *
     * Return array[Coords]
     */
    public function getCoordsGraphTwig($interGraphId)
    {
        $coords = $this->doctrine
                       ->getManager()
                       ->getRepository('UJMExoBundle:Coords')
                       ->findBy(array('interactionGraphic' => $interGraphId));

        return $coords;
    }

      public function goodGraphCoords($interGraph)
    {
         $coords = $this->doctrine
                        ->getManager()
                        ->getRepository('UJMExoBundle:Coords')
                        ->findBy(array('interactionGraphic' => $interGraph->getId()));

         return $coords;
    }
}
