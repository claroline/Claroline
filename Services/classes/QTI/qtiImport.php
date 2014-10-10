<?php

/**
 * To import a question in QTI
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Security\Core\SecurityContextInterface;

abstract class qtiImport
{
    private $doctrine;
    private $securityContext;
    private $container;
    protected $qtiRepos;

    /**
     * Constructor
     *
     * @access public
     *
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine Dependency Injection
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext Dependency Injection
     *
     */
    public function __construct(Registry $doctrine, SecurityContextInterface $securityContext, $container)
    {
        $this->doctrine        = $doctrine;
        $this->securityContext = $securityContext;
        $this->container       = $container;
    }

    /**
     * abstract method to import a question
     *
     * @access public
     * @param qtiRepository $qtiRepos
     */
    abstract public function import(qtiRepository $qtiRepos);

}
