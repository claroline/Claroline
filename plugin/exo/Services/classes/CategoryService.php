<?php

/**
 * Services for the qcm.
 */

namespace UJM\ExoBundle\Services\classes;

use Claroline\CoreBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;
use UJM\ExoBundle\Entity\Category;

class CategoryService
{
    private $doctrine;
    private $tokenStorage;
    private $translator;

    /**
     * Constructor.
     *
     * @param \Doctrine\Bundle\DoctrineBundle\Registry                                            $doctrine     Dependency Injection;
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage Dependency Injection
     * @param \Symfony\Component\Translation\TranslatorInterface                                  $translator
     */
    public function __construct(
            Registry $doctrine,
            TokenStorageInterface $tokenStorage,
            TranslatorInterface $translator
    ) {
        $this->doctrine = $doctrine;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
    }

    /**
     * Get the lock category.
     *
     *
     * @return string the name of category locked
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
     * @return bool[]
     */
    public function getLinkedCategories()
    {
        $em = $this->doctrine->getEntityManager();
        $linkedCategory = [];
        $repositoryCategory = $em->getRepository('UJMExoBundle:Category');

        $repositoryQuestion = $em->getRepository('UJMExoBundle:Question');

        $categoryList = $repositoryCategory->findAll();

        foreach ($categoryList as $category) {
            $questionLink = $repositoryQuestion->findOneBy(['category' => $category->getId()]);
            if (!$questionLink) {
                $linkedCategory[$category->getId()] = 0;
            } else {
                $linkedCategory[$category->getId()] = 1;
            }
        }

        return $linkedCategory;
    }

    /**
     * Control if the user is the owner of the category
     * If no, the default category of user will be used.
     *
     * @param \UJM\ExoBundle\Entity\Question $question
     */
    public function ctrlCategory($question)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $category = $question->getCategory();
        if ($category->getUser()->getId() !== $user->getId()) {
            $userDefaultCategory = $this->doctrine->getManager()->getRepository('UJMExoBundle:Category')->findOneBy([
                'user' => $user,
                'locker' => true,
            ]);

            if (!$userDefaultCategory) {
                $default = $this->translator->trans('default', [], 'ujm_exo');
                $userDefaultCategory = $this->createCategoryDefault($default, $user);
            }

            $question->setCategory($userDefaultCategory);
        }
    }

    /**
     * Create the default category for the user.
     *
     * @param string $default name of default's category
     * @param User   $user
     *
     * @return \UJM\ExoBundle\Entity\Category
     */
    private function createCategoryDefault($default, User $user)
    {
        $newCategory = new Category();
        $newCategory->setValue($default);
        $newCategory->setLocker(1);
        $newCategory->setUser($user);
        $this->doctrine->getManager()->persist($newCategory);
        $this->doctrine->getManager()->flush();

        return $newCategory;
    }
}
