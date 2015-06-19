<?php

/**
 *
 * Services for the qcm
 */
namespace UJM\ExoBundle\Services\classes;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CategoryService {

    private $doctrine;
    private $tokenStorage;

    /**
     * Constructor
     *
     * @access public
     *
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine Dependency Injection;
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage Dependency Injection
     *
     */
    public function __construct(
            Registry $doctrine,
            TokenStorageInterface $tokenStorage
    )
    {
        $this->doctrine = $doctrine;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Get the lock category
     *
     * @access public
     *
     * @return String the name of category locked
     */
    public function getLockCategory() {
        $user  = $this->tokenStorage->getToken()->getUser()->getId();
        $Locker = $this->doctrine
                       ->getManager()
                       ->getRepository('UJMExoBundle:Category')
                       ->getCategoryLocker($user);
        if (empty($Locker)) {
            $catLocker = "";
        } else {
            $catLocker = $Locker[0];
        }

        return $catLocker;
    }
}
