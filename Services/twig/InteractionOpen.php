<?php

namespace UJM\ExoBundle\Services\twig;

use Doctrine\Bundle\DoctrineBundle\Registry;

class InteractionOpen extends \Twig_Extension
{

    protected  $doctrine;

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
        return "InteractionOpen";
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
              'goodResponseOpenOneWord' => new \Twig_Function_Method($this, 'goodResponseOpenOneWord'),
          );
    }

     /**
     * return the good response for an open question with one word
     *
     * @access public
     *
     * @param integer $interOpenId id InteractionOpen
     *
     * Return integer
     */
    public function goodResponseOpenOneWord($interOpenId)
    {

        return $this->doctrine
                    ->getManager()
                    ->getRepository('UJMExoBundle:WordResponse')
                    ->getgoodResponseOneWord($interOpenId);
    }
}
