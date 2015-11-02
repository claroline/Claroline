<?php

/**
 * Services for the qcm.
 */
namespace UJM\ExoBundle\Services\classes;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CategoryService
{
    private $doctrine;
    private $tokenStorage;

    /**
     * Constructor.
     *
     *
     * @param \Doctrine\Bundle\DoctrineBundle\Registry                                            $doctrine     Dependency Injection;
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage Dependency Injection
     */
    public function __construct(
            Registry $doctrine,
            TokenStorageInterface $tokenStorage
    ) {
        $this->doctrine = $doctrine;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Get the lock category.
     *
     *
     * @return String the name of category locked
     */
    public function getLockCategory()
    {
        $user = $this->tokenStorage->getToken()->getUser()->getId();
        $Locker = $this->doctrine
                       ->getManager()
                       ->getRepository('UJMExoBundle:Category')
                       ->getCategoryLocker($user);
        if (empty($Locker)) {
            $catLocker = '';
        } else {
            $catLocker = $Locker[0];
        }

        return $catLocker;
    }

    /**
     * Get information if these categories are linked to questions, allow to know if a category can be deleted or not.
     *
     *
     * @return boolean[]
     */
    public function getLinkedCategories()
    {
        $em = $this->doctrine->getEntityManager();
        $linkedCategory = array();
        $repositoryCategory = $em->getRepository('UJMExoBundle:Category');

        $repositoryQuestion = $em->getRepository('UJMExoBundle:Question');

        $categoryList = $repositoryCategory->findAll();

        foreach ($categoryList as $category) {
            $questionLink = $repositoryQuestion->findOneBy(array('category' => $category->getId()));
            if (!$questionLink) {
                $linkedCategory[$category->getId()] = 0;
            } else {
                $linkedCategory[$category->getId()] = 1;
            }
        }

        return $linkedCategory;
    }
}
