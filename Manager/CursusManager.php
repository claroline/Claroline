<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Manager;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Cursus;
use Claroline\CursusBundle\Entity\CursusGroup;
use Claroline\CursusBundle\Entity\CursusUser;
use Claroline\CursusBundle\Entity\CursusDisplayedWord;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

/**
 * @DI\Service("claroline.manager.cursus_manager")
 */
class CursusManager
{
    private $om;
    private $pagerFactory;
    private $translator;
    private $courseRepo;
    private $cursusRepo;
    private $cursusGroupRepo;
    private $cursusUserRepo;
    private $cursusWordRepo;
    
    /**
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory"    = @DI\Inject("claroline.pager.pager_factory"),
     *     "translator"      = @DI\Inject("translator")
     * })
     */
    public function __construct(
        ObjectManager $om,
        PagerFactory $pagerFactory,
        Translator $translator
    )
    {
        $this->om = $om;
        $this->pagerFactory = $pagerFactory;
        $this->translator = $translator;
        $this->courseRepo =
            $om->getRepository('ClarolineCursusBundle:Course');
        $this->cursusRepo =
            $om->getRepository('ClarolineCursusBundle:Cursus');
        $this->cursusGroupRepo =
            $om->getRepository('ClarolineCursusBundle:CursusGroup');
        $this->cursusUserRepo =
            $om->getRepository('ClarolineCursusBundle:CursusUser');
        $this->cursusWordRepo =
            $om->getRepository('ClarolineCursusBundle:CursusDisplayedWord');
    }
    
    public function persistCursusDisplayedWord(CursusDisplayedWord $word)
    {
        $this->om->persist($word);
        $this->om->flush();
    }
    
    public function getDisplayedWord($word)
    {
        $cursusDisplayedWord = $this->cursusWordRepo->findOneByWord($word);
        
        if (is_null($cursusDisplayedWord)) {
            $result = $this->translator->trans($word, array(), 'cursus');
        } else {
            $displayedWord = $cursusDisplayedWord->getDisplayedWord();
            $result = empty($displayedWord) ?
                $this->translator->trans($word, array(), 'cursus'):
                $displayedWord;
        }
        
        return $result;
    }

    public function persistCursus(Cursus $cursus)
    {
        $this->om->persist($cursus);
        $this->om->flush();
    }

    public function deleteCursus(Cursus $cursus)
    {
        $this->om->remove($cursus);
        $this->om->flush();
    }

    public function persistCourse(Course $course)
    {
        $this->om->persist($course);
        $this->om->flush();
    }

    public function deleteCourse(Course $course)
    {
        $this->om->remove($course);
        $this->om->flush();
    }

    public function persistCursusUser(CursusUser $cursusUser)
    {
        $this->om->persist($cursusUser);
        $this->om->flush();
    }

    public function deleteCursusUser(CursusUser $cursusUser)
    {
        $this->om->remove($cursusUser);
        $this->om->flush();
    }

    public function persistCursusGroup(CursusGroup $cursusGroup)
    {
        $this->om->persist($cursusGroup);
        $this->om->flush();
    }

    public function deleteCursusGroup(CursusGroup $cursusGroup)
    {
        $this->om->remove($cursusGroup);
        $this->om->flush();
    }
    
    public function registerUserToCursus(Cursus $cursus, User $user)
    {
        $cursusUser = $this->cursusUserRepo->findOneCursusUserByCursusAndUser(
            $cursus,
            $user
        );

        if (is_null($cursusUser)) {
            $registrationDate = new \DateTime();
            $cursusUser = new CursusUser();
            $cursusUser->setCursus($cursus);
            $cursusUser->setUser($user);
            $cursusUser->setRegistrationDate($registrationDate);
            $this->persistCursusUser($cursusUser);
        }
    }

    public function unregisterUserFromCursus(Cursus $cursus, User $user)
    {
        $cursusUser = $this->cursusUserRepo->findOneCursusUserByCursusAndUser(
            $cursus,
            $user
        );

        if (!is_null($cursusUser)) {
            $this->deleteCursusUser($cursusUser);
        }
    }

    public function registerUsersToCursus(Cursus $cursus, array $users)
    {
        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $this->registerUserToCursus($cursus, $user);
        }
        $this->om->endFlushSuite();
    }

    public function unregisterUsersFromCursus(Cursus $cursus, array $users)
    {
        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $this->unregisterUserFromCursus($cursus, $user);
        }
        $this->om->endFlushSuite();
    }

    public function registerGroupToCursus(Cursus $cursus, Group $group)
    {
        $cursusGroup = $this->cursusGroupRepo->findOneCursusGroupByCursusAndGroup(
            $cursus,
            $group
        );

        if (is_null($cursusGroup)) {
            $this->om->startFlushSuite();
            $registrationDate = new \DateTime();
            $cursusGroup = new CursusGroup();
            $cursusGroup->setCursus($cursus);
            $cursusGroup->setGroup($group);
            $cursusGroup->setRegistrationDate($registrationDate);
            $this->persistCursusGroup($cursusGroup);
            $users = $group->getUsers();
            $this->registerUsersToCursus($cursus, $users->toArray());
            $this->om->endFlushSuite();
        }
    }

    public function unregisterGroupFromCursus(Cursus $cursus, Group $group)
    {
        $cursusGroup = $this->cursusGroupRepo->findOneCursusGroupByCursusAndGroup(
            $cursus,
            $group
        );

        if (!is_null($cursusGroup)) {
            $this->om->startFlushSuite();
            $this->deleteCursusGroup($cursusGroup);
            $users = $group->getUsers();
            $this->unregisterUsersFromCursus($cursus, $users->toArray());
            $this->om->endFlushSuite();
        }
    }
    

    /***************************************************
     * Access to CursusDisplayedWordRepository methods *
     ***************************************************/
    
    public function getOneDisplayedWordByWord($word)
    {
        return $this->cursusWordRepo->findOneByWord($word);
    }


    /**************************************
     * Access to CursusRepository methods *
     **************************************/

    public function getAllRootCursus($executeQuery = true)
    {
        return $this->cursusRepo->findAllRootCursus($executeQuery);
    }

    public function getLastRootCursusOrder($executeQuery = true)
    {
        return $this->cursusRepo->findLastRootCursusOrder($executeQuery);
    }

    public function getLastCursusOrderByParent(Cursus $cursus, $executeQuery = true)
    {
        return $this->cursusRepo->findLastCursusOrderByParent($cursus, $executeQuery);
    }

    public function getHierarchyByCursus(Cursus $cursus, $executeQuery = true)
    {
        return $this->cursusRepo->findHierarchyByCursus($cursus, $executeQuery);
    }


    /**************************************
     * Access to CourseRepository methods *
     **************************************/

    public function getAllCourses(
        $orderedBy = 'title',
        $order = 'ASC',
        $page = 1,
        $max = 50,
        $executeQuery = true
    )
    {
        $courses = $this->courseRepo->findAllCourses(
            $orderedBy,
            $order,
            $executeQuery
        );

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($courses, $page, $max) :
            $this->pagerFactory->createPager($courses, $page, $max);
    }

    public function getSearchedCourses(
        $search = '',
        $orderedBy = 'title',
        $order = 'ASC',
        $page = 1,
        $max = 50,
        $executeQuery = true
    )
    {
        $courses = $this->courseRepo->findSearchedCourses(
            $search,
            $orderedBy,
            $order,
            $executeQuery
        );

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($courses, $page, $max) :
            $this->pagerFactory->createPager($courses, $page, $max);
    }


    /******************************************
     * Access to CursusUserRepository methods *
     ******************************************/

    public function getOneCursusUserByCursusAndUser(
        Cursus $cursus,
        User $user,
        $executeQuery = true
    )
    {
        return $this->cursusUserRepo->findOneCursusUserByCursusAndUser(
            $cursus,
            $user,
            $executeQuery
        );
    }


    /*******************************************
     * Access to CursusGroupRepository methods *
     *******************************************/

    public function getOneCursusGroupByCursusAndGroup(
        Cursus $cursus,
        Group $group,
        $executeQuery = true
    )
    {
        return $this->cursusGroupRepo->findOneCursusGroupByCursusAndGroup(
            $cursus,
            $group,
            $executeQuery
        );
    }
}