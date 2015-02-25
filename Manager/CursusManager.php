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
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\CourseSessionUser;
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
    private $roleManager;
    private $translator;
    private $courseRepo;
    private $courseSessionRepo;
    private $cursusRepo;
    private $cursusGroupRepo;
    private $cursusUserRepo;
    private $cursusWordRepo;
    private $registrationQueueRepo;
    private $sessionGroupRepo;
    private $sessionUserRepo;
    
    /**
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory"    = @DI\Inject("claroline.pager.pager_factory"),
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "translator"      = @DI\Inject("translator")
     * })
     */
    public function __construct(
        ObjectManager $om,
        PagerFactory $pagerFactory,
        RoleManager $roleManager,
        Translator $translator
    )
    {
        $this->om = $om;
        $this->pagerFactory = $pagerFactory;
        $this->roleManager = $roleManager;
        $this->translator = $translator;
        $this->courseRepo =
            $om->getRepository('ClarolineCursusBundle:Course');
        $this->courseSessionRepo =
            $om->getRepository('ClarolineCursusBundle:CourseSession');
        $this->cursusRepo =
            $om->getRepository('ClarolineCursusBundle:Cursus');
        $this->cursusGroupRepo =
            $om->getRepository('ClarolineCursusBundle:CursusGroup');
        $this->cursusUserRepo =
            $om->getRepository('ClarolineCursusBundle:CursusUser');
        $this->cursusWordRepo =
            $om->getRepository('ClarolineCursusBundle:CursusDisplayedWord');
        $this->registrationQueueRepo =
            $om->getRepository('ClarolineCursusBundle:CourseSessionRegistrationQueue');
        $this->sessionGroupRepo =
            $om->getRepository('ClarolineCursusBundle:CourseSessionGroup');
        $this->sessionUserRepo =
            $om->getRepository('ClarolineCursusBundle:CourseSessionUser');
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

    public function persistCourseSession(CourseSession $session)
    {
        $this->om->persist($session);
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
        $this->unregisterUsersFromCursus($cursus, array($user));
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
        $toDelete = array();

        if ($cursus->isBlocking()) {
            $toDelete = $this->getCursusUsersFromCursusAndUsers(
                array($cursus),
                $users
            );
        } else {
            // Determines from which cursus descendants user has to be removed.
            $unlockedDescendants = $this->getUnlockedDescendants($cursus);
            // Current cursus is included
            $unlockedDescendants[] = $cursus;
            $toDelete = $this->getCursusUsersFromCursusAndUsers(
                $unlockedDescendants,
                $users
            );

            foreach ($users as $user) {
                // Determines from which cursus ancestors user has to be removed
                $removableAncestors = $this->searchRemovableCursusUsersFromAncestors(
                    $cursus,
                    $user
                );

                // Merge all removable CursusUser
                $toDelete = array_merge_recursive(
                    $toDelete,
                    $removableAncestors
                );
            }
        }

        $this->om->startFlushSuite();

        foreach ($toDelete as $cu) {
            $this->deleteCursusUser($cu);
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
        $users = $group->getUsers()->toArray();
        $cursusGroupsToDelete = array();
        $cursusUsersToDelete = array();

        if ($cursus->isBlocking()) {
            $cursusUsersToDelete = $this->getCursusUsersFromCursusAndUsers(
                array($cursus),
                $users
            );
            $cursusGroupsToDelete = $this->getCursusGroupsFromCursusAndGroups(
                array($cursus),
                array($group)
            );
        } else {
            // Determines from which cursus descendants user has to be removed.
            $unlockedDescendants = $this->getUnlockedDescendants($cursus);
            // Current cursus is included
            $unlockedDescendants[] = $cursus;
            $cursusUsersToDelete = $this->getCursusUsersFromCursusAndUsers(
                $unlockedDescendants,
                $users
            );
            $removableGroupDescendants = $this->getCursusGroupsFromCursusAndGroups(
                $unlockedDescendants,
                array($group)
            );

            foreach ($users as $user) {
                // Determines from which cursus ancestors user has to be removed
                $removableUserAncestors = $this->searchRemovableCursusUsersFromAncestors(
                    $cursus,
                    $user
                );

                // Merge all removable CursusUser
                $cursusUsersToDelete = array_merge_recursive(
                    $cursusUsersToDelete,
                    $removableUserAncestors
                );
            }

            $removableGroupAncestors = $this->searchRemovableCursusGroupsFromAncestors(
                $cursus,
                $group
            );

            // Merge all removable CursusGroup
            $cursusGroupsToDelete = array_merge_recursive(
                $removableGroupDescendants,
                $removableGroupAncestors
            );
        }

        $this->om->startFlushSuite();

        foreach ($cursusUsersToDelete as $cu) {
            $this->deleteCursusUser($cu);
        }

        foreach ($cursusGroupsToDelete as $cg) {
            $this->deleteCursusGroup($cg);
        }
        $this->om->endFlushSuite();
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

    private function searchRemovableCursusGroupsFromAncestors(Cursus $cursus, Group $group)
    {
        $removableCursusGroups = array();
        $parent = $cursus->getParent();

        while (!is_null($parent) && !$parent->isBlocking()) {
            $parentGroup = $this->cursusGroupRepo->findOneCursusGroupByCursusAndGroup(
                $parent,
                $group
            );

            if (is_null($parentGroup)) {
                break;
            } else {
                $childrenGroups = $this->cursusGroupRepo->findCursusGroupsOfCursusChildren(
                    $parent,
                    $group
                );

                if (count($childrenGroups) > 1) {
                    break;
                } else {
                    $removableCursusGroups[] = $parentGroup;
                    $parent = $parent->getParent();
                }
            }
        }

        return $removableCursusGroups;
    }

    public function registerUsersToSession(
        CourseSession $session,
        array $users,
        $type
    )
    {
        $registrationDate = new \DateTime();

        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $sessionUser = $this->sessionUserRepo->findOneSessionUserBySessionAndUserAndType(
                $session,
                $user,
                $type
            );

            if (is_null($sessionUser)) {
                $sessionUser = new CourseSessionUser();
                $sessionUser->setSession($session);
                $sessionUser->setUser($user);
                $sessionUser->setUserType($type);
                $sessionUser->setRegistrationDate($registrationDate);
                $this->om->persist($sessionUser);
            }
        }
        $role = null;

        if (intval($type) === 0) {
            $role = $session->getLearnerRole();
        } elseif (intval($type) === 1) {
            $role = $session->getTutorRole();
        }

        if (!is_null($role)) {
            $this->roleManager->associateRoleToMultipleSubjects($users, $role);
        }
        $this->om->endFlushSuite();
    }

    public function unregisterUsersFromSession(array $sessionUsers)
    {
        $this->om->startFlushSuite();

        foreach ($sessionUsers as $sessionUser) {
            $session = $sessionUser->getSession();
            $user = $sessionUser->getUser();
            $userType = $sessionUser->getUserType();
            $role = null;

            if ($userType === 0) {
                $role = $session->getLearnerRole();
            } elseif ($userType === 1) {
                $role = $session->getTutorRole();
            }

            if (!is_null($role)) {
                $this->roleManager->dissociateRole($user, $role);
            }
            $this->om->remove($sessionUser);
        }
        $this->om->endFlushSuite();
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

    public function getCursusUsersFromCursusAndUsers(
        array $cursus,
        array $users
    )
    {
        if (count($cursus) > 0 && count($users) > 0) {

            return $this->cursusUserRepo->findCursusUsersFromCursusAndUsers(
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

    public function getCursusGroupsFromCursusAndGroups(
        array $cursus,
        array $groups
    )
    {
        if (count($cursus) > 0 && count($groups) > 0) {

            return $this->cursusGroupRepo->findCursusGroupsFromCursusAndGroups(
                $cursus,
                $groups
            );
        } else {

            return array();
        }
    }

    public function getCursusGroupsOfCursusChildren(
        Cursus $cursus,
        Group $group,
        $executeQuery = true
    )
    {
        return $this->cursusGroupRepo->findCursusGroupsOfCursusChildren(
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


    /*********************************************
     * Access to CourseSessionRepository methods *
     *********************************************/

    public function getSessionsByCourse(
        Course $course,
        $orderedBy = 'creationDate',
        $order = 'DESC',
        $executeQuery = true
    )
    {
        return $this->courseSessionRepo->findSessionsByCourse(
            $course,
            $orderedBy,
            $order,
            $executeQuery
        );
    }

    public function getSessionsByCourseAndStatus(
        Course $course,
        $status,
        $orderedBy = 'creationDate',
        $order = 'DESC',
        $executeQuery = true
    )
    {
        return $this->courseSessionRepo->findSessionsByCourseAndStatus(
            $course,
            $status,
            $orderedBy,
            $order,
            $executeQuery
        );
    }

    public function getDefaultSessionsByCourse(
        Course $course,
        $orderedBy = 'creationDate',
        $order = 'DESC',
        $executeQuery = true
    )
    {
        return $this->courseSessionRepo->findDefaultSessionsByCourse(
            $course,
            $orderedBy,
            $order,
            $executeQuery
        );
    }


    /*************************************************
     * Access to CourseSessionUserRepository methods *
     *************************************************/

    public function getOneSessionUserBySessionAndUserAndType(
        CourseSession $session,
        User $user,
        $userType,
        $executeQuery = true
    )
    {
        return $this->sessionUserRepo->findOneSessionUserBySessionAndUserAndType(
            $session,
            $user,
            $userType,
            $executeQuery
        );
    }

    public function getSessionUsersBySession(
        CourseSession $session,
        $executeQuery = true
    )
    {
        return $this->sessionUserRepo->findSessionUsersBySession(
            $session,
            $executeQuery
        );
    }

    public function getUnregisteredUsersBySession(
        CourseSession $session,
        $userType,
        $orderedBy = 'firstName',
        $order = 'ASC',
        $page = 1,
        $max = 50,
        $executeQuery = true
    )
    {
        $users = $this->sessionUserRepo->findUnregisteredUsersBySession(
            $session,
            $userType,
            $orderedBy,
            $order,
            $executeQuery
        );

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($users, $page, $max) :
            $this->pagerFactory->createPager($users, $page, $max);
    }

    public function getSearchedUnregisteredUsersBySession(
        CourseSession $session,
        $userType,
        $search = '',
        $orderedBy = 'firstName',
        $order = 'ASC',
        $page = 1,
        $max = 50,
        $executeQuery = true
    )
    {
        $users = $this->sessionUserRepo->findSearchedUnregisteredUsersBySession(
            $session,
            $userType,
            $search,
            $orderedBy,
            $order,
            $executeQuery
        );

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($users, $page, $max) :
            $this->pagerFactory->createPager($users, $page, $max);
    }


    /**************************************************
     * Access to CourseSessionGroupRepository methods *
     **************************************************/

    public function getOneSessionGroupBySessionAndGroup(
        CourseSession $session,
        Group $group,
        $executeQuery = true
    )
    {
        return $this->sessionGroupRepo->findOneSessionGroupBySessionAndGroup(
            $session,
            $group,
            $executeQuery
        );
    }

    public function getSessionGroupsBySession(
        CourseSession $session,
        $executeQuery = true
    )
    {
        return $this->sessionGroupRepo->findSessionGroupsBySession(
            $session,
            $executeQuery
        );
    }


    /**************************************************************
     * Access to CourseSessionRegistrationQueueRepository methods *
     **************************************************************/

    public function getQueuesBySession(CourseSession $session, $executeQuery = true)
    {
        return $this->registrationQueueRepo->findQueuesBySession($session, $executeQuery);
    }

    public function getOneQueueBySessionAndUser(
        CourseSession $session,
        User $user,
        $executeQuery = true
    )
    {
        return $this->registrationQueueRepo->findOneQueueBySessionAndUser(
            $session,
            $user,
            $executeQuery
        );
    }

    public function getQueuesByCourse(Course $course, $executeQuery = true)
    {
        return $this->registrationQueueRepo->findQueuesByCourse($course, $executeQuery);
    }
}