<?php

/**
 * To import a question in QTI
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

class qtiImport extends qtiRepository
{
    private $doctrine;
    private $securityContext;
    private $container;

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
}
