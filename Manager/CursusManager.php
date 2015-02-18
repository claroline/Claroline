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
    private $courseGroupRepo;
    private $courseUserRepo;
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
        $this->courseGroupRepo =
            $om->getRepository('ClarolineCursusBundle:CourseGroup');
        $this->courseUserRepo =
            $om->getRepository('ClarolineCursusBundle:CourseUser');
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

    public function addCoursesToCursus(Cursus $parent, array $courses)
    {
        $this->om->startFlushSuite();
        $lastOrder = $this->cursusRepo->findLastCursusOrderByParent($parent);

        foreach ($courses as $course) {
            $newCursus = new Cursus();
            $newCursus->setParent($parent);
            $newCursus->setCourse($course);
            $newCursus->setTitle($course->getTitle());
            $newCursus->setBlocking(false);
            $lastOrder++;
            $newCursus->setCursusOrder($lastOrder);
            $this->om->persist($newCursus);
        }
        $this->om->endFlushSuite();
    }

    public function removeCoursesFromCursus(Cursus $parent, array $courses)
    {
        if (count($courses) > 0) {
            $toRemove = $this->cursusRepo->findCursusByParentAndCourses(
                $parent,
                $courses
            );
            $this->om->startFlushSuite();

            foreach ($toRemove as $cursus) {
                $this->om->remove($cursus);
            }
            $this->om->endFlushSuite();
        }
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

    public function registerUserToMultipleCursus(array $multipleCursus, User $user)
    {
        $registrationDate = new \DateTime();

        $this->om->startFlushSuite();

        foreach ($multipleCursus as $cursus) {
            $cursusUser = $this->cursusUserRepo->findOneCursusUserByCursusAndUser(
                $cursus,
                $user
            );

            if (is_null($cursusUser)) {
                $cursusUser = new CursusUser();
                $cursusUser->setCursus($cursus);
                $cursusUser->setUser($user);
                $cursusUser->setRegistrationDate($registrationDate);
                $this->persistCursusUser($cursusUser);
            }
        }
        $this->om->endFlushSuite();
    }

    public function registerUsersToMultipleCursus(array $multipleCursus, array $users)
    {
        $registrationDate = new \DateTime();

        $this->om->startFlushSuite();

        foreach ($users as $user) {

            foreach ($multipleCursus as $cursus) {
                $cursusUser = $this->cursusUserRepo->findOneCursusUserByCursusAndUser(
                    $cursus,
                    $user
                );

                if (is_null($cursusUser)) {
                    $cursusUser = new CursusUser();
                    $cursusUser->setCursus($cursus);
                    $cursusUser->setUser($user);
                    $cursusUser->setRegistrationDate($registrationDate);
                    $this->persistCursusUser($cursusUser);
                }
            }
        }
        $this->om->endFlushSuite();
    }

    public function unregisterUserFromCursus(Cursus $cursus, User $user)
    {
        $cursusUser = $this->cursusUserRepo->findOneCursusUserByCursusAndUser(
            $cursus,
            $user
        );
        $toDelete = array();

        if (!is_null($cursusUser)) {
            $toDelete[] = $cursusUser;

            if (!$cursus->isBlocking()) {
                // Determines from which cursus descendants user has to be removed
                $unlockedDescendants = $this->getUnlockedDescendants($cursus);
                $removableDescendants = $this->getCursusUsersFromUsersAndCursus(
                    $unlockedDescendants,
                    array($user)
                );

                // Determines from which cursus ancestors user has to be removed
                $removableAncestors = $this->searchRemovableCursusUsersFromAncestors(
                    $cursus,
                    $user
                );

                // Merge all removable CursusUser
                $toDelete = array_merge_recursive(
                    $toDelete,
                    $removableAncestors,
                    $removableDescendants
                );
            }
        }

        $this->om->startFlushSuite();

        foreach ($toDelete as $cu) {
            $this->deleteCursusUser($cu);
        }
        $this->om->endFlushSuite();
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

    public function registerGroupToMultipleCursus(array $multipleCursus, Group $group)
    {
        $registrationDate = new \DateTime();

        $this->om->startFlushSuite();

        foreach ($multipleCursus as $cursus) {
            $cursusGroup = $this->cursusGroupRepo->findOneCursusGroupByCursusAndGroup(
                $cursus,
                $group
            );

            if (is_null($cursusGroup)) {
                $cursusGroup = new CursusGroup();
                $cursusGroup->setCursus($cursus);
                $cursusGroup->setGroup($group);
                $cursusGroup->setRegistrationDate($registrationDate);
                $this->persistCursusGroup($cursusGroup);
                $users = $group->getUsers();
                $this->registerUsersToCursus($cursus, $users->toArray());
            }
        }
        $this->om->endFlushSuite();
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

    public function updateCursusOrder(Cursus $cursus, $cursusOrder)
    {
        $this->updateCursusOrderByParent($cursusOrder, $cursus->getParent());
        $cursus->setCursusOrder($cursusOrder);
        $this->om->persist($cursus);
        $this->om->flush();
    }

    public function updateCursusOrderByParent(
        $cursusOrder,
        Cursus $parent = null,
        $executeQuery = true
    )
    {
        return is_null($parent) ?
            $this->cursusRepo->updateCursusOrderWithoutParent(
                $cursusOrder,
                $executeQuery
            ) :
            $this->cursusRepo->updateCursusOrderByParent(
                $parent,
                $cursusOrder,
                $executeQuery
            );
    }

    private function getUnlockedDescendants(Cursus $cursus)
    {
        $descendantsCursus = $this->cursusRepo->findDescendantHierarchyByCursus($cursus);
        $hierarchy = array();
        $unlockedDescendants = array();

        foreach ($descendantsCursus as $descendant) {
            $parent = $descendant->getParent();

            if (!is_null($parent)) {
                $parentId = $parent->getId();

                if (!isset($hierarchy[$parentId])) {
                    $hierarchy[$parentId] = array();
                }
                $hierarchy[$parentId][] = $descendant;
            }
        }
        $this->searchUnlockedDescendants(
            $cursus,
            $hierarchy,
            $unlockedDescendants
        );

        return $unlockedDescendants;
    }

    private function searchUnlockedDescendants(
        Cursus $cursus,
        array $hierarchy,
        array &$unlockedDescendants
    )
    {
        $cursusId = $cursus->getId();

        if (isset($hierarchy[$cursusId])) {

            foreach ($hierarchy[$cursusId] as $child) {

                if (!$child->isBlocking()) {
                    $unlockedDescendants[] = $child;
                    $this->searchUnlockedDescendants(
                        $child,
                        $hierarchy,
                        $unlockedDescendants
                    );
                }
            }
        }
    }

    private function searchRemovableCursusUsersFromAncestors(Cursus $cursus, User $user)
    {
        $removableCursusUsers = array();
        $parent = $cursus->getParent();

        while (!is_null($parent) && !$parent->isBlocking()) {
            $parentUser = $this->cursusUserRepo->findOneCursusUserByCursusAndUser(
                $parent,
                $user
            );

            if (is_null($parentUser)) {
                break;
            } else {
                $childrenUsers = $this->cursusUserRepo->findCursusUsersOfCursusChildren(
                    $parent,
                    $user
                );

                if (count($childrenUsers) > 1) {
                    break;
                } else {
                    $removableCursusUsers[] = $parentUser;
                    $parent = $parent->getParent();
                }
            }
        }

        return $removableCursusUsers;
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

    public function getAllCursus(
        $orderedBy = 'cursusOrder',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        return $this->cursusRepo->findAllCursus($orderedBy, $order, $executeQuery);
    }

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

    public function getHierarchyByCursus(
        Cursus $cursus,
        $orderedBy = 'cursusOrder',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        return $this->cursusRepo->findHierarchyByCursus(
            $cursus,
            $orderedBy,
            $order,
            $executeQuery
        );
    }

    public function getRelatedHierarchyByCursus(
        Cursus $cursus,
        $orderedBy = 'cursusOrder',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        return $this->cursusRepo->findRelatedHierarchyByCursus(
            $cursus,
            $orderedBy,
            $order,
            $executeQuery
        );
    }

    public function getDescendantHierarchyByCursus(
        Cursus $cursus,
        $orderedBy = 'cursusOrder',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        return $this->cursusRepo->findDescendantHierarchyByCursus(
            $cursus,
            $orderedBy,
            $order,
            $executeQuery
        );
    }

    public function getCursusByParentAndCourses(
        Cursus $parent,
        array $courses,
        $executeQuery = true
    )
    {
        return $this->cursusRepo->findCursusByParentAndCourses(
            $parent,
            $courses,
            $executeQuery = true
        );
    }

    public function getSearchedCursus(
        $search,
        $orderedBy = 'title',
        $order = 'ASC',
        $page = 1,
        $max = 50,
        $executeQuery = true
    )
    {
        $cursus = $this->cursusRepo->findSearchedCursus(
            $search,
            $orderedBy,
            $order,
            $executeQuery
        );

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($cursus, $page, $max) :
            $this->pagerFactory->createPager($cursus, $page, $max);
    }
    
    public function getCursusByIds(array $ids, $executeQuery = true)
    {
        return count($ids) > 0 ?
            $this->cursusRepo->findCursusByIds($ids, $executeQuery)
            : array();
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

    public function getCursusUsersByCursus(
        Cursus $cursus,
        $executeQuery = true
    )
    {
        return $this->cursusUserRepo->findCursusUsersByCursus(
            $cursus,
            $executeQuery
        );
    }

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

    public function getCursusUsersFromUsersAndCursus(
        array $cursus,
        array $users
    )
    {
        if (count($cursus) > 0 && count($users) > 0) {

            return $this->cursusUserRepo->findCursusUsersFromUsersAndCursus(
                $cursus,
                $users
            );
        } else {

            return array();
        }
    }

    public function getCursusUsersOfCursusChildren(
        Cursus $cursus,
        User $user,
        $executeQuery = true
    )
    {
        return $this->cursusUserRepo->findCursusUsersOfCursusChildren(
            $cursus,
            $user,
            $executeQuery
        );
    }

    public function getUnregisteredUsersByCursus(
        Cursus $cursus,
        $orderedBy = 'username',
        $order = 'ASC',
        $page = 1,
        $max = 50,
        $executeQuery = true
    )
    {
        $users = $this->cursusUserRepo->findUnregisteredUsersByCursus(
            $cursus,
            $orderedBy,
            $order,
            $executeQuery
        );

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($users, $page, $max) :
            $this->pagerFactory->createPager($users, $page, $max);
    }

    public function getSearchedUnregisteredUsersByCursus(
        Cursus $cursus,
        $search = '',
        $orderedBy = 'username',
        $order = 'ASC',
        $page = 1,
        $max = 50,
        $executeQuery = true
    )
    {
        $users = $this->cursusUserRepo->findSearchedUnregisteredUsersByCursus(
            $cursus,
            $search,
            $orderedBy,
            $order,
            $executeQuery
        );

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($users, $page, $max) :
            $this->pagerFactory->createPager($users, $page, $max);
    }

    public function getUsersByCursus(
        Cursus $cursus,
        $orderedBy = 'firstName',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        return $this->cursusUserRepo->findUsersByCursus(
            $cursus,
            $orderedBy,
            $order,
            $executeQuery
        );
    }

    public function getSearchedUsersByCursus(
        Cursus $cursus,
        $search = '',
        $orderedBy = 'firstName',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        return $this->cursusUserRepo->findSearchedUsersByCursus(
            $cursus,
            $search,
            $orderedBy,
            $order,
            $executeQuery
        );
    }


    /*******************************************
     * Access to CursusGroupRepository methods *
     *******************************************/

    public function getCursusGroupsByCursus(
        Cursus $cursus,
        $executeQuery = true
    )
    {
        return $this->cursusGroupRepo->findCursusGroupsByCursus(
            $cursus,
            $executeQuery
        );
    }

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

    public function getUnregisteredGroupsByCursus(
        Cursus $cursus,
        $orderedBy = 'name',
        $order = 'ASC',
        $page = 1,
        $max = 50,
        $executeQuery = true
    )
    {
        $groups = $this->cursusGroupRepo->findUnregisteredGroupsByCursus(
            $cursus,
            $orderedBy,
            $order,
            $executeQuery
        );

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($groups, $page, $max) :
            $this->pagerFactory->createPager($groups, $page, $max);
    }

    public function getSearchedUnregisteredGroupsByCursus(
        Cursus $cursus,
        $search = '',
        $orderedBy = 'name',
        $order = 'ASC',
        $page = 1,
        $max = 50,
        $executeQuery = true
    )
    {
        $groups = $this->cursusGroupRepo->findSearchedUnregisteredGroupsByCursus(
            $cursus,
            $search,
            $orderedBy,
            $order,
            $executeQuery
        );

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($groups, $page, $max) :
            $this->pagerFactory->createPager($groups, $page, $max);
    }

    public function getGroupsByCursus(
        Cursus $cursus,
        $orderedBy = 'name',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        return $this->cursusGroupRepo->findGroupsByCursus(
            $cursus,
            $orderedBy,
            $order,
            $executeQuery
        );
    }

    public function getSearchedGroupsByCursus(
        Cursus $cursus,
        $search = '',
        $orderedBy = 'name',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        return $this->cursusGroupRepo->findSearchedGroupsByCursus(
            $cursus,
            $search,
            $orderedBy,
            $order,
            $executeQuery
        );
    }


    /******************************************
     * Access to CourseUserRepository methods *
     ******************************************/

    public function getOneCourseUserByCourseAndUser(
        Course $course,
        User $user,
        $executeQuery = true
    )
    {
        return $this->courseUserRepo->findOneCourseUserByCourseAndUser(
            $course,
            $user,
            $executeQuery
        );
    }


    /*******************************************
     * Access to CourseGroupRepository methods *
     *******************************************/

    public function getOneCourseGroupByCourseAndGroup(
        Course $course,
        Group $group,
        $executeQuery = true
    )
    {
        return $this->courseGroupRepo->findOneCourseGroupByCourseAndGroup(
            $course,
            $group,
            $executeQuery
        );
    }
}