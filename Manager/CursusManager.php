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
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Manager\ContentManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\CourseRegistrationQueue;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\CourseSessionGroup;
use Claroline\CursusBundle\Entity\CourseSessionRegistrationQueue;
use Claroline\CursusBundle\Entity\CourseSessionUser;
use Claroline\CursusBundle\Entity\CoursesWidgetConfig;
use Claroline\CursusBundle\Entity\Cursus;
use Claroline\CursusBundle\Entity\CursusGroup;
use Claroline\CursusBundle\Entity\CursusUser;
use Claroline\CursusBundle\Entity\CursusDisplayedWord;
use Claroline\CursusBundle\Event\Log\LogCourseSessionUserRegistrationEvent;
use Claroline\CursusBundle\Event\Log\LogCourseSessionUserUnregistrationEvent;
use Claroline\CursusBundle\Event\Log\LogCursusUserRegistrationEvent;
use Claroline\CursusBundle\Event\Log\LogCursusUserUnregistrationEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @DI\Service("claroline.manager.cursus_manager")
 */
class CursusManager
{
    private $container;
    private $contentManager;
    private $eventDispatcher;
    private $iconsDirectory;
    private $om;
    private $pagerFactory;
    private $roleManager;
    private $templateDir;
    private $translator;
    private $workspaceManager;

    private $courseRepo;
    private $courseQueueRepo;
    private $courseSessionRepo;
    private $coursesWidgetConfigRepo;
    private $cursusRepo;
    private $cursusGroupRepo;
    private $cursusUserRepo;
    private $cursusWordRepo;
    private $sessionGroupRepo;
    private $sessionQueueRepo;
    private $sessionUserRepo;

    /**
     * @DI\InjectParams({
     *     "container"        = @DI\Inject("service_container"),
     *     "contentManager"   = @DI\Inject("claroline.manager.content_manager"),
     *     "eventDispatcher"  = @DI\Inject("event_dispatcher"),
     *     "om"               = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory"     = @DI\Inject("claroline.pager.pager_factory"),
     *     "roleManager"      = @DI\Inject("claroline.manager.role_manager"),
     *     "templateDir"      = @DI\Inject("%claroline.param.templates_directory%"),
     *     "translator"       = @DI\Inject("translator"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        ContainerInterface $container,
        ContentManager $contentManager,
        EventDispatcherInterface $eventDispatcher,
        ObjectManager $om,
        PagerFactory $pagerFactory,
        RoleManager $roleManager,
        $templateDir,
        TranslatorInterface $translator,
        WorkspaceManager $workspaceManager
    )
    {
        $this->container = $container;
        $this->contentManager = $contentManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->iconsDirectory = $this->container->getParameter('kernel.root_dir') .
            '/../web/files/cursusbundle/icons/';
        $this->om = $om;
        $this->pagerFactory = $pagerFactory;
        $this->roleManager = $roleManager;
        $this->templateDir = $templateDir;
        $this->translator = $translator;
        $this->workspaceManager = $workspaceManager;
        $this->courseRepo =
            $om->getRepository('ClarolineCursusBundle:Course');
        $this->courseQueueRepo =
            $om->getRepository('ClarolineCursusBundle:CourseRegistrationQueue');
        $this->courseSessionRepo =
            $om->getRepository('ClarolineCursusBundle:CourseSession');
        $this->coursesWidgetConfigRepo =
            $om->getRepository('ClarolineCursusBundle:CoursesWidgetConfig');
        $this->cursusRepo =
            $om->getRepository('ClarolineCursusBundle:Cursus');
        $this->cursusGroupRepo =
            $om->getRepository('ClarolineCursusBundle:CursusGroup');
        $this->cursusUserRepo =
            $om->getRepository('ClarolineCursusBundle:CursusUser');
        $this->cursusWordRepo =
            $om->getRepository('ClarolineCursusBundle:CursusDisplayedWord');
        $this->sessionGroupRepo =
            $om->getRepository('ClarolineCursusBundle:CourseSessionGroup');
        $this->sessionQueueRepo =
            $om->getRepository('ClarolineCursusBundle:CourseSessionRegistrationQueue');
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
        $event = new LogCursusUserUnregistrationEvent($cursusUser);
        $this->eventDispatcher->dispatch('log', $event);
        $this->om->remove($cursusUser);
        $this->om->flush();
    }

    public function deleteCursusUsers(array $cursusUsers)
    {
        $this->om->startFlushSuite();

        foreach ($cursusUsers as $cursusUser) {
            $this->deleteCursusUser($cursusUser);
        }
        $this->om->endFlushSuite();
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
        $createdCursus = array();
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
            $createdCursus[] = $newCursus;
        }
        $this->om->endFlushSuite();

        return $createdCursus;
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
                $event = new LogCursusUserRegistrationEvent($cursus, $user);
                $this->eventDispatcher->dispatch('log', $event);
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
                    $event = new LogCursusUserRegistrationEvent($cursus, $user);
                    $this->eventDispatcher->dispatch('log', $event);
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
        $coursesToUnregister = array();
        $root = $cursus->getRoot();
        $cursusRoot = $this->getOneCursusById($root);

        if ($cursus->isBlocking()) {
            $toDelete = $this->getCursusUsersFromCursusAndUsers(
                array($cursus),
                $users
            );
            $course = $cursus->getCourse();

            if (!is_null($course)) {
                $coursesToUnregister[] = $course;
            }
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

            foreach ($toDelete as $cursusUser) {
                $cursus = $cursusUser->getCursus();
                $course = $cursus->getCourse();

                if (!is_null($course)) {
                    $coursesToUnregister[] = $course;
                }
            }
        }
        $sessionsToUnregister = is_null($cursusRoot) ?
            array() :
            $this->getSessionsByCursusAndCourses(
                $cursusRoot,
                $coursesToUnregister
            );
        $sessionsUsers = $this->getSessionUsersBySessionsAndUsers(
            $sessionsToUnregister,
            $users,
            0
        );
        $this->om->startFlushSuite();
        $this->unregisterUsersFromSession($sessionsUsers);

        foreach ($toDelete as $cu) {
            $this->deleteCursusUser($cu);
        }
        $this->om->endFlushSuite();
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
        $coursesToUnregister = array();
        $root = $cursus->getRoot();
        $cursusRoot = $this->getOneCursusById($root);

        if ($cursus->isBlocking()) {
            $course = $cursus->getCourse();

            if (!is_null($course)) {
                $coursesToUnregister[] = $course;
            }
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

            foreach ($cursusGroupsToDelete as $cursusGroup) {
                $cursus = $cursusGroup->getCursus();
                $course = $cursus->getCourse();

                if (!is_null($course)) {
                    $coursesToUnregister[] = $course;
                }
            }
        }
        $sessionsToUnregister = is_null($cursusRoot) ?
            array() :
            $this->getSessionsByCursusAndCourses(
                $cursusRoot,
                $coursesToUnregister
            );
        $sessionsGroups = $this->getSessionGroupsBySessionsAndGroup(
            $sessionsToUnregister,
            $group,
            0
        );

        $this->om->startFlushSuite();

        foreach ($sessionsGroups as $sessionGroup) {
            $this->unregisterGroupFromSession($sessionGroup);
        }

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
        $results = array();
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
                $results[] = $sessionUser;
                $event = new LogCourseSessionUserRegistrationEvent($session, $user);
                $this->eventDispatcher->dispatch('log', $event);
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

        return $results;
    }

    public function registerUsersToSessions(
        array $sessions,
        array $users,
        $type = 0
    )
    {
        $registrationDate = new \DateTime();

        $this->om->startFlushSuite();

        foreach ($sessions as $session) {

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
                    $event = new LogCourseSessionUserRegistrationEvent($session, $user);
                    $this->eventDispatcher->dispatch('log', $event);
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
            $event = new LogCourseSessionUserUnregistrationEvent($sessionUser);
            $this->eventDispatcher->dispatch('log', $event);
            $this->om->remove($sessionUser);
        }
        $this->om->endFlushSuite();
    }

    public function registerGroupToSessions(
        array $sessions,
        Group $group,
        $type = 0
    )
    {
        $registrationDate = new \DateTime();

        $this->om->startFlushSuite();

        foreach ($sessions as $session) {
            $users = $group->getUsers()->toArray();
            $sessionGroup = $this->sessionGroupRepo->findOneSessionGroupBySessionAndGroup(
                $session,
                $group,
                $type
            );

            if (is_null($sessionGroup)) {
                $sessionGroup = new CourseSessionGroup();
                $sessionGroup->setSession($session);
                $sessionGroup->setGroup($group);
                $sessionGroup->setGroupType($type);
                $sessionGroup->setRegistrationDate($registrationDate);
                $this->om->persist($sessionGroup);
            }

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
                $this->roleManager->associateRole($group, $role);
            }
        }
        $this->om->endFlushSuite();
    }

    public function unregisterGroupFromSession(CourseSessionGroup $sessionGroup)
    {
        $this->om->startFlushSuite();
        $session = $sessionGroup->getSession();
        $group = $sessionGroup->getGroup();
        $groupType = $sessionGroup->getGroupType();
        $role = null;
        $users = $group->getUsers()->toArray();

        if ($groupType === 0) {
            $role = $session->getLearnerRole();
        } elseif ($groupType === 1) {
            $role = $session->getTutorRole();
        }

        if (!is_null($role)) {
            $this->roleManager->dissociateRole($group, $role);
        }
        $this->om->remove($sessionGroup);

        $sessionUsers = $this->getSessionUsersBySessionAndUsers($session, $users, $groupType);
        $this->unregisterUsersFromSession($sessionUsers);
        $this->om->endFlushSuite();
    }

    public function deleteCourseSession(CourseSession $session, $withWorkspace = false)
    {
        $this->om->startFlushSuite();
        $workspace = $session->getWorkspace();

        if ($withWorkspace && !is_null($workspace)) {
            $this->workspaceManager->deleteWorkspace($workspace);
        }
        $this->om->remove($session);
        $this->om->endFlushSuite();
    }

    public function createCourseSession(
        Course $course,
        User $user,
        $sessionName = null,
        Cursus $cursus = null,
        $registrationDate = null
    )
    {
        if (is_null($registrationDate)) {
            $registrationDate = new \DateTime();
        }
        $session = new CourseSession();
        $session->setName($sessionName);
        $session->setCourse($course);
        $session->addCursus($cursus);
        $session->setCreationDate($registrationDate);
        $session->setPublicRegistration($course->getPublicRegistration());
        $session->setPublicUnregistration($course->getPublicUnregistration());
        $session->setRegistrationValidation($course->getRegistrationValidation());

        $workspace = $this->generateWorkspace(
            $course,
            $session,
            $user
        );
        $session->setWorkspace($workspace);
        $learnerRole = $this->generateRoleForSession(
            $workspace,
            $course->getLearnerRoleName(),
            0
        );
        $tutorRole = $this->generateRoleForSession(
            $workspace,
            $course->getTutorRoleName(),
            1
        );
        $session->setLearnerRole($learnerRole);
        $session->setTutorRole($tutorRole);
        $this->persistCourseSession($session);

        return $session;
    }

    public function deleteCourseSessionUsers(array $sessionUsers)
    {
        $this->om->startFlushSuite();

        foreach ($sessionUsers as $sessionUser) {
            $event = new LogCourseSessionUserUnregistrationEvent($sessionUser);
            $this->eventDispatcher->dispatch('log', $event);
            $this->om->remove($sessionUser);
        }
        $this->om->endFlushSuite();
    }

    public function generateWorkspace(Course $course, CourseSession $session, User $user)
    {
        $model = $course->getWorkspaceModel();
        $description = $course->getDescription();
        $displayable = false;
        $selfRegistration = false;
        $selfUnregistration = false;
        $registrationValidation = false;
        $name = $course->getTitle() .
            ' [' .
            $session->getName() .
            ']';
        $code = $this->generateWorkspaceCode($course->getCode());

        if (is_null($model)) {
            $ds = DIRECTORY_SEPARATOR;
            $config = Configuration::fromTemplate(
                $this->templateDir . $ds . 'default.zip'
            );
            $config->setWorkspaceName($name);
            $config->setWorkspaceCode($code);
            $config->setDisplayable($displayable);
            $config->setSelfRegistration($selfRegistration);
            $config->setSelfUnregistration($selfUnregistration);
            $config->setRegistrationValidation($registrationValidation);
            $config->setWorkspaceDescription($description);
            $workspace = $this->workspaceManager->create($config, $user);
        } else {
            $workspace = $this->workspaceManager->createWorkspaceFromModel(
                $model,
                $user,
                $name,
                $code,
                $description,
                $displayable,
                $selfRegistration,
                $selfUnregistration
            );
        }
        $workspace->setWorkspaceType(0);

        $startDate = $session->getStartDate();
        $endDate = $session->getEndDate();

        if (!is_null($startDate)) {
            $workspace->setStartDate($startDate);
        }

        if (!is_null($endDate)) {
            $workspace->setEndDate($endDate);
        }
        $this->workspaceManager->editWorkspace($workspace);

        return $workspace;
    }

    public function generateRoleForSession(Workspace $workspace, $roleName, $type)
    {
        if (empty($roleName)) {

            if ($type === 1) {
                $role = $this->roleManager->getManagerRole($workspace);
            } else {
                $role = $this->roleManager->getCollaboratorRole($workspace);
            }
        } else {
            $roles = $this->roleManager->getRolesByWorkspaceCodeAndTranslationKey(
                $workspace->getCode(),
                $roleName
            );

            if (count($roles) > 0) {
                $role = $roles[0];
            } else {
                $guid = $workspace->getGuid();
                $wsRoleName = 'ROLE_WS_' . strtoupper($roleName) . '_' . $guid;

                $role = $this->roleManager->getRoleByName($wsRoleName);

                if (is_null($role)) {
                    $role = $this->roleManager->createWorkspaceRole(
                        $wsRoleName,
                        $roleName,
                        $workspace
                    );
                }
            }
        }

        return $role;
    }

    public function associateCursusToSessions(Cursus $cursus, array $sessions)
    {
        foreach ($sessions as $session) {
            $session->addCursus($cursus);
            $this->om->persist($session);
        }
        $this->om->flush();
    }

    public function getConfirmationEmail()
    {
        return $this->contentManager->getContent(
            array('type' => 'claro_cursusbundle_mail_confirmation')
        );
    }

    public function persistConfirmationEmail($datas)
    {
        $this->contentManager->updateContent(
            $this->getConfirmationEmail(),
            $datas
        );
    }

    private function generateWorkspaceCode($code)
    {
        $workspaceCodes = $this->workspaceManager->getWorkspaceCodesWithPrefix($code);
        $existingCodes = array();

        foreach ($workspaceCodes as $wsCode) {
            $existingCodes[] = $wsCode['code'];
        }

        $index = count($existingCodes) + 1;
        $currentCode = $code . '_' . $index;
        $upperCurrentCode = strtoupper($currentCode);

        while (in_array($upperCurrentCode, $existingCodes)) {
            $index++;
            $currentCode = $code . '_' . $index;
            $upperCurrentCode = strtoupper($currentCode);
        }

        return $currentCode;
    }

    public function saveIcon(UploadedFile $tmpFile)
    {
        $extension = $tmpFile->getClientOriginalExtension();
        $hashName = $this->container->get('claroline.utilities.misc')->generateGuid() .
            '.' .
            $extension;
        $tmpFile->move($this->iconsDirectory, $hashName);

        return $hashName;
    }

    public function changeIcon(Course $course, UploadedFile $tmpFile)
    {
        $icon = $course->getIcon();

        if (!is_null($icon)) {
            $iconPath = $this->iconsDirectory . $icon;

            try {
                unlink($iconPath);
            } catch(\Exception $e) {}
        }

        return $this->saveIcon($tmpFile);
    }

    public function addUserToSessionQueue(User $user, CourseSession $session)
    {
        $sessionUser = $this->getOneSessionUserBySessionAndUserAndType(
            $session,
            $user,
            0
        );

        if (is_null($sessionUser)) {
            $queue = $this->getOneSessionQueueBySessionAndUser($session, $user);

            if (is_null($queue)) {
                $queue = new CourseSessionRegistrationQueue();
                $queue->setSession($session);
                $queue->setUser($user);
                $queue->setApplicationDate(new \DateTime());
                $this->om->persist($queue);
                $this->om->flush();
            }
        }
    }

    public function deleteSessionQueue(CourseSessionRegistrationQueue $queue)
    {
        $this->om->remove($queue);
        $this->om->flush();
    }

    public function addUserToCourseQueue(User $user, Course $course)
    {
        $queue = $this->getOneCourseQueueByCourseAndUser(
            $course,
            $user
        );

        if (is_null($queue)) {
            $queue = new CourseRegistrationQueue();
            $queue->setCourse($course);
            $queue->setUser($user);
            $queue->setApplicationDate(new \DateTime());
            $this->om->persist($queue);
            $this->om->flush();
        }
    }

    public function removeUserFromCourseQueue(User $user, Course $course)
    {
        $queue = $this->getOneCourseQueueByCourseAndUser(
            $course,
            $user
        );

        if (!is_null($queue)) {
            $this->deleteCourseQueue($queue);
        }
    }

    public function deleteCourseQueue(CourseRegistrationQueue $queue)
    {
        $this->om->remove($queue);
        $this->om->flush();
    }

    public function transferQueuedUserToSession(
        CourseRegistrationQueue $queue,
        CourseSession $session
    )
    {
        $user = $queue->getUser();
        $this->om->startFlushSuite();
        $this->registerUsersToSession($session, array($user), 0);
        $this->om->remove($queue);
        $this->om->endFlushSuite();
    }

    public function getCoursesWidgetConfiguration(WidgetInstance $widgetInstance)
    {
        $config = $this->coursesWidgetConfigRepo->findOneBy(
            array('widgetInstance' => $widgetInstance->getId())
        );

        if (is_null($config)) {
            $config = new CoursesWidgetConfig();
            $config->setWidgetInstance($widgetInstance);
            $this->persistCoursesWidgetConfiguration($config);
        }

        return $config;
    }

    public function persistCoursesWidgetConfiguration(CoursesWidgetConfig $config)
    {
        $this->om->persist($config);
        $this->om->flush();
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
        $search = '',
        $orderedBy = 'cursusOrder',
        $order = 'ASC',
        $withPager = false,
        $page = 1,
        $max = 50
    )
    {
        $cursus = empty($search) ?
            $this->cursusRepo->findAllCursus($orderedBy, $order) :
            $this->cursusRepo->findSearchedCursus($search, $orderedBy, $order);

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($cursus, $page, $max) :
            $cursus;
    }

    public function getAllRootCursus(
        $search = '',
        $orderedBy = 'id',
        $order = 'ASC',
        $withPager = false,
        $page = 1,
        $max = 50
    )
    {
        $cursus = empty($search) ?
            $this->cursusRepo->findAllRootCursus($orderedBy, $order) :
            $this->cursusRepo->findSearchedRootCursus($search, $orderedBy, $order);

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($cursus, $page, $max) :
            $cursus;
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
            $executeQuery
        );
    }

    public function getCursusByIds(array $ids, $executeQuery = true)
    {
        return count($ids) > 0 ?
            $this->cursusRepo->findCursusByIds($ids, $executeQuery)
            : array();
    }

    public function getOneCursusById($cursusId, $executeQuery = true)
    {
        return $this->cursusRepo->findOneCursusById($cursusId, $executeQuery);
    }

    public function getCursusByGroup(Group $group, $executeQuery = true)
    {
        return $this->cursusRepo->findCursusByGroup($group, $executeQuery);
    }


    /**************************************
     * Access to CourseRepository methods *
     **************************************/

    public function getAllCourses(
        $search = '',
        $orderedBy = 'title',
        $order = 'ASC',
        $withPager = true,
        $page = 1,
        $max = 50
    )
    {
        $courses = empty($search) ?
            $this->courseRepo->findAllCourses(
                $orderedBy,
                $order
            ) :
            $this->courseRepo->findSearchedCourses(
                $search,
                $orderedBy,
                $order
            );

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($courses, $page, $max) :
            $courses;
    }

    public function getUnmappedCoursesByCursus(
        Cursus $cursus,
        $search = '',
        $orderedBy = 'title',
        $order = 'ASC',
        $withPager = true,
        $page = 1,
        $max = 50
    )
    {
        $courses = empty($search) ?
            $this->courseRepo->findUnmappedCoursesByCursus(
                $cursus,
                $orderedBy,
                $order
            ) :
            $this->courseRepo->findUnmappedSearchedCoursesByCursus(
                $cursus,
                $search,
                $orderedBy,
                $order
            );

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($courses, $page, $max) :
            $courses;
    }

    public function getDescendantCoursesByCursus(
        Cursus $cursus,
        $search = '',
        $orderedBy = 'title',
        $order = 'ASC',
        $withPager = true,
        $page = 1,
        $max = 50
    )
    {
        $courses = empty($search) ?
            $this->courseRepo->findDescendantCoursesByCursus(
                $cursus,
                $orderedBy,
                $order
            ) :
            $this->courseRepo->findDescendantSearchedCoursesByCursus(
                $cursus,
                $search,
                $orderedBy,
                $order
            );

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($courses, $page, $max) :
            $courses;
    }

    public function getCoursesByUser(
        User $user,
        $search = '',
        $orderedBy = 'title',
        $order = 'ASC',
        $withPager = true,
        $page = 1,
        $max = 20
    )
    {
        $courses = empty($search) ?
            $this->courseRepo->findCoursesByUser(
                $user,
                $orderedBy,
                $order
            ) :
            $this->courseRepo->findSearchedCoursesByUser(
                $user,
                $search,
                $orderedBy,
                $order
            );

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($courses, $page, $max) :
            $courses;
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
        $search = '',
        $orderedBy = 'username',
        $order = 'ASC',
        $withPager = true,
        $page = 1,
        $max = 50
    )
    {
        $users = empty($search) ?
            $this->cursusUserRepo->findUnregisteredUsersByCursus(
                $cursus,
                $orderedBy,
                $order
            ) :
            $this->cursusUserRepo->findSearchedUnregisteredUsersByCursus(
                $cursus,
                $search,
                $orderedBy,
                $order
            );

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($users, $page, $max) :
            $users;
    }

    public function getUsersByCursus(
        Cursus $cursus,
        $search = '',
        $orderedBy = 'firstName',
        $order = 'ASC',
        $withPager = false,
        $page = 1,
        $max = 50
    )
    {
        $users = empty($search) ?
            $this->cursusUserRepo->findUsersByCursus(
                $cursus,
                $orderedBy,
                $order
            ) :
            $this->cursusUserRepo->findSearchedUsersByCursus(
                $cursus,
                $search,
                $orderedBy,
                $order
            );

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($users, $page, $max) :
            $users;
    }


    /*******************************************
     * Access to CursusGroupRepository methods *
     *******************************************/

    public function getCursusGroupsByCursus(Cursus $cursus, $executeQuery = true)
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

    public function getCursusGroupsFromCursusAndGroups(array $cursus, array $groups)
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
        $search = '',
        $orderedBy = 'name',
        $order = 'ASC',
        $withPager = true,
        $page = 1,
        $max = 50
    )
    {
        $groups = empty($search) ?
            $this->cursusGroupRepo->findUnregisteredGroupsByCursus(
                $cursus,
                $orderedBy,
                $order
            ) :
            $this->cursusGroupRepo->findSearchedUnregisteredGroupsByCursus(
                $cursus,
                $search,
                $orderedBy,
                $order
            );

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($groups, $page, $max) :
            $groups;
    }

    public function getGroupsByCursus(
        Cursus $cursus,
        $search = '',
        $orderedBy = 'name',
        $order = 'ASC',
        $withPager = false,
        $page = 1,
        $max = 50
    )
    {
        $groups = empty($search) ?
            $this->cursusGroupRepo->findGroupsByCursus(
                $cursus,
                $orderedBy,
                $order
            ) :
            $this->cursusGroupRepo->findSearchedGroupsByCursus(
                $cursus,
                $search,
                $orderedBy,
                $order
            );

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($groups, $page, $max) :
            $groups;
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

    public function getSessionsByCourses(
        array $courses,
        $orderedBy = 'creationDate',
        $order = 'DESC',
        $executeQuery = true
    )
    {
        if (count($courses) > 0) {

            return $this->courseSessionRepo->findSessionsByCourses(
                $courses,
                $orderedBy,
                $order,
                $executeQuery
            );
        } else {

            return array();
        }
    }

    public function getSessionsByCursusAndCourses(
        Cursus $cursus,
        array $courses,
        $orderedBy = 'creationDate',
        $order = 'DESC',
        $executeQuery = true
    )
    {
        if (count($courses) > 0) {

            return $this->courseSessionRepo->findSessionsByCursusAndCourses(
                $cursus,
                $courses,
                $orderedBy,
                $order,
                $executeQuery
            );
        } else {

            return array();
        }
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

    public function getSessionUsersBySessionAndUsers(
        CourseSession $session,
        array $users,
        $userType,
        $executeQuery = true
    )
    {
        if (count($users) > 0) {

            return $this->sessionUserRepo->findSessionUsersBySessionAndUsers(
                $session,
                $users,
                $userType,
                $executeQuery
            );
        } else {

            return array();
        }
    }

    public function getSessionUsersBySessionsAndUsers(
        array $sessions,
        array $users,
        $userType,
        $executeQuery = true
    )
    {
        if (count($users) > 0 && count($sessions) > 0) {

            return $this->sessionUserRepo->findSessionUsersBySessionsAndUsers(
                $sessions,
                $users,
                $userType,
                $executeQuery
            );
        } else {

            return array();
        }
    }

    public function getUnregisteredUsersBySession(
        CourseSession $session,
        $userType,
        $search = '',
        $orderedBy = 'firstName',
        $order = 'ASC',
        $withPager = true,
        $page = 1,
        $max = 50
    )
    {
        $users = empty($search) ?
            $this->sessionUserRepo->findUnregisteredUsersBySession(
                $session,
                $userType,
                $orderedBy,
                $order
            ) :
            $this->sessionUserRepo->findSearchedUnregisteredUsersBySession(
                $session,
                $userType,
                $search,
                $orderedBy,
                $order
            );

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($users, $page, $max) :
            $users;
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

    public function getSessionGroupsBySessionsAndGroup(
        array $sessions,
        Group $group,
        $groupType,
        $executeQuery = true
    )
    {
        return $this->sessionGroupRepo->findSessionGroupsBySessionsAndGroup(
            $sessions,
            $group,
            $groupType,
            $executeQuery
        );
    }

    public function getSessionGroupsByGroup(
        Group $group,
        $executeQuery = true
    )
    {
        return $this->sessionGroupRepo->findSessionGroupsByGroup(
            $group,
            $executeQuery
        );
    }


    /**************************************************************
     * Access to CourseSessionRegistrationQueueRepository methods *
     **************************************************************/

    public function getSessionQueuesBySession(CourseSession $session, $executeQuery = true)
    {
        return $this->sessionQueueRepo->findSessionQueuesBySession(
            $session,
            $executeQuery
        );
    }

    public function getSessionQueuesByUser(User $user, $executeQuery = true)
    {
        return $this->sessionQueueRepo->findSessionQueuesByUser(
            $user,
            $executeQuery
        );
    }

    public function getOneSessionQueueBySessionAndUser(
        CourseSession $session,
        User $user,
        $executeQuery = true
    )
    {
        return $this->sessionQueueRepo->findOneSessionQueueBySessionAndUser(
            $session,
            $user,
            $executeQuery
        );
    }

    public function getSessionQueuesByCourse(Course $course, $executeQuery = true)
    {
        return $this->sessionQueueRepo->findSessionQueuesByCourse(
            $course,
            $executeQuery
        );
    }


    /*******************************************************
     * Access to CourseRegistrationQueueRepository methods *
     *******************************************************/

    public function getCourseQueuesByCourse(Course $course, $executeQuery = true)
    {
        return $this->courseQueueRepo->findCourseQueuesByCourse(
            $course,
            $executeQuery
        );
    }

    public function getCourseQueuesByUser(User $user, $executeQuery = true)
    {
        return $this->courseQueueRepo->findCourseQueuesByUser(
            $user,
            $executeQuery
        );
    }

    public function getOneCourseQueueByCourseAndUser(
        Course $course,
        User $user,
        $executeQuery = true
    )
    {
        return $this->courseQueueRepo->findOneCourseQueueByCourseAndUser(
            $course,
            $user,
            $executeQuery
        );
    }
}
