<?php

namespace UJM\ExoBundle\Services\twig;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\DependencyInjection\Container;

class InteractionMatching extends \Twig_Extension
{
    protected $doctrine;
    protected $container;
    /**
     * Constructor.
     *
     *
     * @param \Doctrine\Bundle\DoctrineBundle\Registry         $doctrine  Dependency Injection
     * @param \Symfony\Component\DependencyInjection\Container $container
     */
    public function __construct(Registry $doctrine, Container $container)
    {
        $this->doctrine = $doctrine;
        $this->container = $container;
    }

    public function getName()
    {
        return 'InteractionMatching';
    }

    /**
     * Get functions.
     */
    public function getFunctions()
    {
        return array(
              'getProposal' => new \Twig_Function_Method($this, 'getProposal'),
              'initTabResponseMatching' => new \Twig_Function_Method($this, 'initTabResponseMatching'),
           );
    }

    /**
     * Get a proposal entity.
     *
     *
     * @param int
     *
     * Return \UJM\ExoBundle\Entity\Proposal $proposal
     */
    public function getProposal($id)
    {
        $proposal = $this->doctrine
                         ->getManager()
                         ->getRepository('UJMExoBundle:Proposal')
                         ->find($id);

        return $proposal;
    }

    /**
     * For the correction of a matching question :
     * init array of responses of user indexed by labelId
     * init array of rights responses indexed by labelId.
     *
     *
     * @param string                                          $response
     * @param \UJM\ExoBundle\Entity\Paper\InteractionMatching $interMatching
     *
     * Return array of arrays
     */
    public function initTabResponseMatching($response, $interMatching)
    {
        $interMatchSer = $this->container->get('ujm.exo_InteractionMatching');

        return $interMatchSer->initTabResponseMatching($response, $interMatching);
    }
}
