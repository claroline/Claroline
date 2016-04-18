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

use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Manager\ContentManager;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\UserManager;
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
use Claroline\CursusBundle\Event\Log\LogCourseQueueCreateEvent;
use Claroline\CursusBundle\Event\Log\LogCourseQueueDeclineEvent;
use Claroline\CursusBundle\Event\Log\LogCourseQueueOrganizationValidateEvent;
use Claroline\CursusBundle\Event\Log\LogCourseQueueTransferEvent;
use Claroline\CursusBundle\Event\Log\LogCourseQueueUserValidateEvent;
use Claroline\CursusBundle\Event\Log\LogCourseQueueValidatorValidateEvent;
use Claroline\CursusBundle\Event\Log\LogCourseSessionUserRegistrationEvent;
use Claroline\CursusBundle\Event\Log\LogCourseSessionUserUnregistrationEvent;
use Claroline\CursusBundle\Event\Log\LogCursusUserRegistrationEvent;
use Claroline\CursusBundle\Event\Log\LogCursusUserUnregistrationEvent;
use Claroline\CursusBundle\Event\Log\LogSessionQueueCreateEvent;
use Claroline\CursusBundle\Event\Log\LogSessionQueueDeclineEvent;
use Claroline\CursusBundle\Event\Log\LogSessionQueueOrganizationValidateEvent;
use Claroline\CursusBundle\Event\Log\LogSessionQueueUserValidateEvent;
use Claroline\CursusBundle\Event\Log\LogSessionQueueValidatorValidateEvent;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("claroline.manager.cursus_manager")
 */
class CursusManager
{
    private $archiveDir;
    private $authorization;
    private $container;
    private $contentManager;
    private $eventDispatcher;
    private $iconsDirectory;
    private $mailManager;
    private $om;
    private $pagerFactory;
    private $platformConfigHandler;
    private $roleManager;
    private $router;
    private $serializer;
    private $templateDir;
    private $templating;
    private $tokenStorage;
    private $toolManager;
    private $translator;
    private $ut;
    private $utils;
    private $userManager;
    private $workspaceManager;
    private $clarolineDispatcher;

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
 *     "authorization"         = @DI\Inject("security.authorization_checker"),
 *     "container"             = @DI\Inject("service_container"),
 *     "contentManager"        = @DI\Inject("claroline.manager.content_manager"),
 *     "eventDispatcher"       = @DI\Inject("event_dispatcher"),
 *     "clarolineDispatcher"   = @DI\Inject("claroline.event.event_dispatcher"),
 *     "mailManager"           = @DI\Inject("claroline.manager.mail_manager"),
 *     "om"                    = @DI\Inject("claroline.persistence.object_manager"),
 *     "pagerFactory"          = @DI\Inject("claroline.pager.pager_factory"),
 *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
 *     "roleManager"           = @DI\Inject("claroline.manager.role_manager"),
 *     "router"                = @DI\Inject("router"),
 *     "serializer"            = @DI\Inject("jms_serializer"),
 *     "templateDir"           = @DI\Inject("%claroline.param.templates_directory%"),
 *     "templating"            = @DI\Inject("templating"),
 *     "tokenStorage"          = @DI\Inject("security.token_storage"),
 *     "toolManager"           = @DI\Inject("claroline.manager.tool_manager"),
 *     "translator"            = @DI\Inject("translator"),
 *     "userManager"           = @DI\Inject("claroline.manager.user_manager"),
 *     "ut"                    = @DI\Inject("claroline.utilities.misc"),
 *     "utils"                 = @DI\Inject("claroline.security.utilities"),
 *     "workspaceManager"      = @DI\Inject("claroline.manager.workspace_manager")
 * })
 */
    // why no claroline dispatcher ?
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ContainerInterface $container,
        ContentManager $contentManager,
        EventDispatcherInterface $eventDispatcher,
        MailManager $mailManager,
        ObjectManager $om,
        PagerFactory $pagerFactory,
        PlatformConfigurationHandler $platformConfigHandler,
        RoleManager $roleManager,
        UrlGeneratorInterface $router,
        Serializer $serializer,
        $templateDir,
        TwigEngine $templating,
        TokenStorageInterface $tokenStorage,
        ToolManager $toolManager,
        TranslatorInterface $translator,
        UserManager $userManager,
        ClaroUtilities $ut,
        Utilities $utils,
        WorkspaceManager $workspaceManager,
        $clarolineDispatcher
    ) {
        $this->archiveDir = $container->getParameter('claroline.param.platform_generated_archive_path');
        $this->authorization = $authorization;
        $this->container = $container;
        $this->contentManager = $contentManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->iconsDirectory = $this->container->getParameter('claroline.param.thumbnails_directory').'/';
        $this->mailManager = $mailManager;
        $this->om = $om;
        $this->pagerFactory = $pagerFactory;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->roleManager = $roleManager;
        $this->router = $router;
        $this->serializer = $serializer;
        $this->templateDir = $templateDir;
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
        $this->toolManager = $toolManager;
        $this->translator = $translator;
        $this->userManager = $userManager;
        $this->ut = $ut;
        $this->utils = $utils;
        $this->workspaceManager = $workspaceManager;
        $this->clarolineDispatcher = $clarolineDispatcher;

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
                $this->translator->trans($word, array(), 'cursus') :
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
            ++$lastOrder;
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

    public function registerUserToCursus(Cursus $cursus, User $user, $withWorkspace = true)
    {
        $cursusUser = $this->cursusUserRepo->findOneCursusUserByCursusAndUser(
            $cursus,
            $user
        );

        if (is_null($cursusUser)) {
            $this->om->startFlushSuite();
            $registrationDate = new \DateTime();
            $cursusUser = new CursusUser();
            $cursusUser->setCursus($cursus);
            $cursusUser->setUser($user);
            $cursusUser->setRegistrationDate($registrationDate);
            $this->persistCursusUser($cursusUser);

            if ($withWorkspace) {
                $this->registerToCursusWorkspace($user, $cursus);
            }
            $this->om->endFlushSuite();
        }
    }

    public function registerUserToMultipleCursus(
        array $multipleCursus,
        User $user,
        $withWorkspace = true,
        $withCourse = false
    ) {
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

                if ($withWorkspace) {
                    $this->registerToCursusWorkspace($user, $cursus);
                }

                if ($withCourse) {
                    $course = $cursus->getCourse();

                    if (!is_null($course)) {
                        $this->registerUserToCourse($user, $course);
                    }
                }
            }
        }
        $this->om->endFlushSuite();
    }

    public function registerUsersToMultipleCursus(
        array $multipleCursus,
        array $users,
        $withWorkspace = true
    ) {
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

                    if ($withWorkspace) {
                        $this->registerToCursusWorkspace($user, $cursus);
                    }
                }
            }
        }
        $this->om->endFlushSuite();
    }

    public function unregisterUserFromCursus(Cursus $cursus, User $user)
    {
        $this->unregisterUsersFromCursus($cursus, array($user));
    }

    public function registerUsersToCursus(Cursus $cursus, array $users, $withWorkspace = true)
    {
        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $this->registerUserToCursus($cursus, $user, $withWorkspace);
        }
        $this->om->endFlushSuite();
    }

    public function unregisterUsersFromCursus(Cursus $cursus, array $users)
    {
        $this->checkCursusToolRegistrationAccess();
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

    public function registerGroupToMultipleCursus(
        array $multipleCursus,
        Group $group,
        $withWorkspace = true
    ) {
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

                if ($withWorkspace) {
                    $this->registerToCursusWorkspace($group, $cursus);
                }
                $users = $group->getUsers();
                $this->registerUsersToCursus($cursus, $users->toArray(), false);
            }
        }
        $this->om->endFlushSuite();
    }

    public function unregisterGroupFromCursus(Cursus $cursus, Group $group)
    {
        $this->checkCursusToolRegistrationAccess();
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

    public function unregisterGroupsFromCursus(array $cursusGroups)
    {
        $this->checkCursusToolRegistrationAccess();
        $this->om->startFlushSuite();

        foreach ($cursusGroups as $cursusGroup) {
            $this->unregisterGroupFromCursus(
                $cursusGroup->getCursus(),
                $cursusGroup->getGroup()
            );
        }
        $this->om->endFlushSuite();
    }

    public function updateCursusParentAndOrder(
        Cursus $cursus,
        Cursus $parent = null,
        $cursusOrder = -1
    ) {
        if ($cursus->getParent() !== $parent || $cursus->getCursusOrder() !== $cursusOrder) {
            $cursusList = is_null($parent) ?
                $this->getAllRootCursus('', 'cursusOrder', 'ASC') :
                $this->getCursusByParent($parent);
            $cursus->setParent($parent);
            $i = 1;
            $updated = false;

            $this->om->startFlushSuite();

            foreach ($cursusList as $oneCursus) {
                if ($oneCursus->getId() === $cursus->getId()) {
                    continue;
                } else {
                    $currentOrder = $oneCursus->getCursusOrder();

                    if ($currentOrder === $cursusOrder) {
                        $cursus->setCursusOrder($i);
                        $this->om->persist($cursus);
                        $updated = true;
                        ++$i;
                    }
                    $oneCursus->setCursusOrder($i);
                    $this->om->persist($oneCursus);
                    ++$i;
                }
            }

            if (!$updated) {
                $cursus->setCursusOrder($i);
                $this->om->persist($cursus);
            }
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
    ) {
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
    ) {
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

    public function getSessionRemainingPlace(CourseSession $session)
    {
        $remaingPlace = null;
        $maxUsers = $session->getMaxUsers();

        if (!is_null($maxUsers)) {
            $remaingPlace = $maxUsers;
            $sessionUsers = $this->getSessionUsersBySession($session);

            foreach ($sessionUsers as $sessionUser) {
                if ($sessionUser->getUserType() === CourseSessionUser::LEARNER ||
                    $sessionUser->getUserType() === CourseSessionUser::PENDING_LEARNER) {
                    --$remaingPlace;
                }
            }
        }

        return $remaingPlace;
    }

    public function registerUsersToSession(
        CourseSession $session,
        array $users,
        $type
    ) {
        $results = array('status' => 'success', 'datas' => array());
        $registrationDate = new \DateTime();
        $course = $session->getCourse();
        $remainingPlaces = (intval($type) === CourseSessionUser::LEARNER) ?
            $this->getSessionRemainingPlace($session) :
            null;

        if (!is_null($remainingPlaces) && ($remainingPlaces < count($users))) {
            $results['status'] = 'failed';
            $results['datas']['remainingPlaces'] = $remainingPlaces;
            $results['datas']['requiredPlaces'] = count($users);
            $results['datas']['sessionId'] = $session->getId();
            $results['datas']['sessionName'] = $session->getName();
            $results['datas']['courseId'] = $course->getId();
            $results['datas']['courseTitle'] = $course->getTitle();
            $results['datas']['courseCode'] = $course->getCode();
        } else {
            $sessionUsers = array();
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
                    $sessionUsers[] = $sessionUser;
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

            foreach ($sessionUsers as $su) {
                $user = $su->getUser();
                $results['datas'][] = array(
                    'id' => $su->getId(),
                    'user_type' => $su->getUserType(),
                    'user_id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'user_first_name' => $user->getFirstName(),
                    'user_last_name' => $user->getLastName(),
                    'sessionId' => $session->getId(),
                    'sessionName' => $session->getName(),
                    'courseId' => $course->getId(),
                    'courseTitle' => $course->getTitle(),
                    'courseCode' => $course->getCode(),
                );
            }
        }

        return $results;
    }

    public function registerUsersToSessions(
        array $sessions,
        array $users,
        $type = 0
    ) {
        $results = array('status' => 'success', 'datas' => array());

        if (intval($type) === CourseSessionUser::LEARNER) {
            foreach ($sessions as $session) {
                $course = $session->getCourse();
                $remainingPlaces = $this->getSessionRemainingPlace($session);

                if (!is_null($remainingPlaces) && ($remainingPlaces < count($users))) {
                    $results['status'] = 'failed';
                    $results['datas'][] = array(
                        'sessionId' => $session->getId(),
                        'sessionName' => $session->getName(),
                        'courseId' => $course->getId(),
                        'courseTitle' => $course->getTitle(),
                        'courseCode' => $course->getCode(),
                        'remainingPlaces' => $remainingPlaces,
                    );
                }
            }
        }

        if ($results['status'] === 'success') {
            $this->om->startFlushSuite();
            $registrationDate = new \DateTime();

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

        return $results;
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
    ) {
        $users = $group->getUsers()->toArray();
        $results = array('status' => 'success', 'datas' => array());

        if (intval($type) === CourseSessionUser::LEARNER) {
            foreach ($sessions as $session) {
                $course = $session->getCourse();
                $remainingPlaces = $this->getSessionRemainingPlace($session);

                if (!is_null($remainingPlaces) && ($remainingPlaces < count($users))) {
                    $results['status'] = 'failed';
                    $results['datas'][] = array(
                        'sessionId' => $session->getId(),
                        'sessionName' => $session->getName(),
                        'courseId' => $course->getId(),
                        'courseTitle' => $course->getTitle(),
                        'courseCode' => $course->getCode(),
                        'remainingPlaces' => $remainingPlaces,
                    );
                }
            }
        }

        if ($results['status'] === 'success') {
            $this->om->startFlushSuite();
            $registrationDate = new \DateTime();

            foreach ($sessions as $session) {
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

        return $results;
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
        $registrationDate = null,
        $startDate = null,
        $endDate = null
    ) {
        if (is_null($registrationDate)) {
            $registrationDate = new \DateTime();
        }
        $session = new CourseSession();

        if ($sessionName) {
            $session->setName($sessionName);
        }

        if ($cursus) {
            $session->addCursus($cursus);
        }
        $session->setCreationDate($registrationDate);
        $session->setPublicRegistration($course->getPublicRegistration());
        $session->setPublicUnregistration($course->getPublicUnregistration());
        $session->setRegistrationValidation($course->getRegistrationValidation());

        if ($startDate) {
            $session->setStartDate($startDate);
        }

        if ($endDate) {
            $session->setEndDate($endDate);
        }
        $this->createCourseSessionFromSession($session, $course, $user);

        return $session;
    }

    public function createCourseSessionFromSession(CourseSession $session, Course $course, User $user)
    {
        $session->setCourse($course);
        $workspace = $course->getWorkspace();

        if (is_null($workspace)) {
            $workspace = $this->generateWorkspace(
                $course,
                $session,
                $user
            );
        }
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

        //the event will be listened by FormaLibreBulletinBundle (it adds some MatiereOptions)
        $this->clarolineDispatcher->dispatch('create_course_session', 'Claroline\CursusBundle\Event\CreateCourseSessionEvent', array($session));
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
        $name = $course->getTitle().
            ' ['.
            $session->getName().
            ']';
        $code = $this->generateWorkspaceCode($course->getCode());

        if (is_null($model)) {
            $ds = DIRECTORY_SEPARATOR;
            $config = Configuration::fromTemplate(
                $this->templateDir.$ds.'default.zip'
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
                $wsRoleName = 'ROLE_WS_'.strtoupper($roleName).'_'.$guid;

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
            $session->addCursu($cursus);
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
        $currentCode = $code.'_'.$index;
        $upperCurrentCode = strtoupper($currentCode);

        while (in_array($upperCurrentCode, $existingCodes)) {
            ++$index;
            $currentCode = $code.'_'.$index;
            $upperCurrentCode = strtoupper($currentCode);
        }

        return $currentCode;
    }

    public function saveIcon(UploadedFile $tmpFile)
    {
        $extension = $tmpFile->getClientOriginalExtension();
        $hashName = $this->container->get('claroline.utilities.misc')->generateGuid().
            '.'.
            $extension;
        $tmpFile->move($this->iconsDirectory, $hashName);

        return $hashName;
    }

    public function changeIcon(Course $course, UploadedFile $tmpFile)
    {
        $icon = $course->getIcon();

        if (!is_null($icon)) {
            $iconPath = $this->iconsDirectory.$icon;

            try {
                unlink($iconPath);
            } catch (\Exception $e) {
            }
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
                $status = 0;

                $validators = $session->getValidators();

                if ($session->getUserValidation()) {
                    $status += CourseRegistrationQueue::WAITING_USER;
                }

                if ($session->getOrganizationValidation()) {
                    $status += CourseRegistrationQueue::WAITING_ORGANIZATION;
                }

                if (count($validators) > 0) {
                    $status += CourseRegistrationQueue::WAITING_VALIDATOR;
                } elseif ($session->getRegistrationValidation()) {
                    $status += CourseRegistrationQueue::WAITING;
                }
                $queue->setStatus($status);
                $this->om->persist($queue);
                $this->om->flush();

                $event = new LogSessionQueueCreateEvent($queue);
                $this->eventDispatcher->dispatch('log', $event);

                if (($status & CourseRegistrationQueue::WAITING_USER) === CourseRegistrationQueue::WAITING_USER) {
                    $this->sendSessionQueueRequestConfirmationMail($queue);
                }
            }
        }
    }

    public function deleteSessionQueue(CourseSessionRegistrationQueue $queue)
    {
        $session = $queue->getSession();
        $course = $session->getCourse();
        $user = $queue->getUser();
        $queueDatas = array(
            'id' => $queue->getId(),
            'courseId' => $course->getId(),
            'sessionId' => $session->getId(),
            'userId' => $user->getId(),
            'username' => $user->getUsername(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
        );
        $this->om->remove($queue);
        $this->om->flush();

        return $queueDatas;
    }

    public function declineSessionQueue(CourseSessionRegistrationQueue $queue)
    {
        $this->checkCursusToolRegistrationAccess();
        $queueDatas = $this->deleteSessionQueue($queue);
        $event = new LogSessionQueueDeclineEvent($queue);
        $this->eventDispatcher->dispatch('log', $event);

        return $queueDatas;
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
            $status = 0;

            $validators = $course->getValidators();

            if ($course->getUserValidation()) {
                $status += CourseRegistrationQueue::WAITING_USER;
            }

            if ($course->getOrganizationValidation()) {
                $status += CourseRegistrationQueue::WAITING_ORGANIZATION;
            }

            if (count($validators) > 0) {
                $status += CourseRegistrationQueue::WAITING_VALIDATOR;
            } elseif ($course->getRegistrationValidation()) {
                $status += CourseRegistrationQueue::WAITING;
            }
            $queue->setStatus($status);
            $this->om->persist($queue);
            $this->om->flush();

            $event = new LogCourseQueueCreateEvent($queue);
            $this->eventDispatcher->dispatch('log', $event);

            if (($status & CourseRegistrationQueue::WAITING_USER) === CourseRegistrationQueue::WAITING_USER) {
                $this->sendCourseQueueRequestConfirmationMail($queue);
            }
        }
    }

    public function sendCourseQueueRequestConfirmationMail(CourseRegistrationQueue $queue)
    {
        $user = $queue->getUser();
        $title = $this->translator->trans('course_registration_request_confirmation', array(), 'cursus');
        $link = $this->router->generate(
            'claro_cursus_course_registration_queue_user_validate',
            array('queue' => $queue->getId()),
            true
        );
        $content = $this->templating->render(
            'ClarolineCursusBundle:CursusRegistration:courseRequestConfirmationMail.html.twig',
            array('queue' => $queue, 'link' => $link)
        );

        $this->mailManager->send($title, $content, array($user));
    }

    public function sendSessionQueueRequestConfirmationMail(CourseSessionRegistrationQueue $queue)
    {
        $user = $queue->getUser();
        $title = $this->translator->trans('session_registration_request_confirmation', array(), 'cursus');
        $link = $this->router->generate(
            'claro_cursus_session_registration_queue_user_validate',
            array('queue' => $queue->getId()),
            true
        );
        $content = $this->templating->render(
            'ClarolineCursusBundle:CursusRegistration:sessionRequestConfirmationMail.html.twig',
            array('queue' => $queue, 'link' => $link)
        );

        $this->mailManager->send($title, $content, array($user));
    }

    public function removeUserFromCourseQueue(User $user, Course $course)
    {
        $queue = $this->getOneCourseQueueByCourseAndUser(
            $course,
            $user
        );

        if (!is_null($queue)) {
            $this->declineCourseQueue($queue);
        }
    }

    public function deleteCourseQueue(CourseRegistrationQueue $queue)
    {
        $course = $queue->getCourse();
        $user = $queue->getUser();
        $queueDatas = array(
            'id' => $queue->getId(),
            'courseId' => $course->getId(),
            'userId' => $user->getId(),
            'username' => $user->getUsername(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
        );
        $this->om->remove($queue);
        $this->om->flush();

        return $queueDatas;
    }

    public function declineCourseQueue(CourseRegistrationQueue $queue)
    {
        $this->checkCursusToolRegistrationAccess();
        $queueDatas = $this->deleteCourseQueue($queue);
        $event = new LogCourseQueueDeclineEvent($queue);
        $this->eventDispatcher->dispatch('log', $event);

        return $queueDatas;
    }

    public function transferQueuedUserToSession(
        CourseRegistrationQueue $queue,
        CourseSession $session
    ) {
        $this->checkCursusToolRegistrationAccess();
        $user = $queue->getUser();
        $this->om->startFlushSuite();
        $results = $this->registerUsersToSession($session, array($user), 0);

        if ($results['status'] === 'success') {
            $event = new LogCourseQueueTransferEvent($queue, $session);
            $this->eventDispatcher->dispatch('log', $event);
            $this->om->remove($queue);
        }
        $this->om->endFlushSuite();

        return $results;
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

    public function registerToCursusWorkspace(AbstractRoleSubject $ars, Cursus $cursus)
    {
        $workspace = $cursus->getWorkspace();

        if (!is_null($workspace)) {
            $collaborator = $this->roleManager->getCollaboratorRole($workspace);
            $this->roleManager->associateRole($ars, $collaborator);
        }
    }

    public function registerUserToCourse(User $user, Course $course)
    {
        $results = array('status' => 'success', 'datas' => array());
        $sessions = $this->getDefaultPublicSessionsByCourse($course);

        if (count($sessions) > 0) {
            $session = $sessions[0];

            if ($session->hasValidation()) {
                $this->addUserToSessionQueue($user, $session);
            } else {
                $results = $this->registerUsersToSession($session, array($user), 0);
            }
        } elseif ($course->getPublicRegistration()) {
            $this->addUserToCourseQueue($user, $course);
        }

        return $results;
    }

    public function unlockedHierarchy(
        Cursus $cursus,
        array $hierarchy,
        array &$lockedHierarchy,
        array &$unlockedCursus
    ) {
        $lockedHierarchy[$cursus->getId()] = false;
        $unlockedCursus[] = $cursus;

        if (!$cursus->isBlocking()) {
            // Unlock parents
            $parent = $cursus->getParent();

            while (!is_null($parent) && !$parent->isBlocking()) {
                $lockedHierarchy[$parent->getId()] = 'up';
                $unlockedCursus[] = $parent;
                $parent = $parent->getParent();
            }
            // Unlock children
            $this->unlockedChildrenHierarchy(
                $cursus,
                $hierarchy,
                $lockedHierarchy,
                $unlockedCursus
            );
        }
    }

    private function unlockedChildrenHierarchy(
        Cursus $cursus,
        array $hierarchy,
        array &$lockedHierarchy,
        array &$unlockedCursus
    ) {
        $cursusId = $cursus->getId();

        if (isset($hierarchy[$cursusId])) {
            foreach ($hierarchy[$cursusId] as $child) {
                if (!$child->isBlocking()) {
                    $lockedHierarchy[$child->getId()] = 'down';
                    $unlockedCursus[] = $child;
                    $this->unlockedChildrenHierarchy(
                        $child,
                        $hierarchy,
                        $lockedHierarchy,
                        $unlockedCursus
                    );
                }
            }
        }
    }

    public function zipDatas(array $datas, $type)
    {
        $archive = new \ZipArchive();
        $pathArch = sys_get_temp_dir().DIRECTORY_SEPARATOR.$this->ut->generateGuid().'.zip';
        $archive->open($pathArch, \ZipArchive::CREATE);

        if ($type === 'cursus') {
            $this->zipCursus($datas, $archive);
        } elseif ($type === 'course') {
            $this->zipCourses($datas, $archive);
        }
        $archive->close();
        file_put_contents($this->archiveDir, $pathArch."\n", FILE_APPEND);

        return $pathArch;
    }

    public function zipCourses(array $courses, \ZipArchive $archive)
    {
        $json = $this->serializer->serialize(
            $courses,
            'json',
            SerializationContext::create()->setGroups(array('api_cursus'))
        );
        $archive->addFromString('courses.json', $json);

        foreach ($courses as $course) {
            $icon = $course->getIcon();

            if (!is_null($icon)) {
                $path = $this->iconsDirectory.$icon;
                $archive->addFile(
                    $path,
                    'icons'.DIRECTORY_SEPARATOR.$icon
                );
            }
        }
    }

    public function zipCursus(array $cursusList, \ZipArchive $archive)
    {
        $cursusJson = $this->serializer->serialize(
            $cursusList,
            'json',
            SerializationContext::create()->setGroups(array('api_cursus'))
        );
        $archive->addFromString('cursus.json', $cursusJson);

        $courses = array();

        foreach ($cursusList as $cursus) {
            $course = $cursus->getCourse();

            if (!is_null($course)) {
                $courses[$course->getId()] = $course;
            }
        }
        $this->zipCourses($courses, $archive);
    }

    public function importCourses(array $datas)
    {
        $courses = array();
        $i = 0;
        $usedCodes = $this->getAllCoursesCodes();
        $this->om->startFlushSuite();

        foreach ($datas as $data) {
            $course = new Course();
            $code = $this->generateValidCode($data['code'], $usedCodes);
            $course->setCode($code);
            $course->setTitle($data['title']);
            $course->setDescription($data['description']);
            $course->setPublicRegistration($data['publicRegistration']);
            $course->setPublicUnregistration($data['publicUnregistration']);
            $course->setRegistrationValidation($data['registrationValidation']);

            if (isset($data['icon'])) {
                $course->setIcon($data['icon']);
            }
            $this->om->persist($course);
            $courses[$data['id']] = $course;
            ++$i;

            if ($i % 50 === 0) {
                $this->om->forceFlush();
            }
        }
        $this->om->endFlushSuite();

        return $courses;
    }

    public function importCursus(array $datas, array $courses = array())
    {
        $roots = array();
        $cursusChildren = array();

        foreach ($datas as $cursus) {
            $root = $cursus['root'];
            $lvl = $cursus['lvl'];
            $id = $cursus['id'];

            if ($lvl === 0) {
                $roots[$id] = array(
                    'id' => $id,
                    'code' => isset($cursus['code']) ? $cursus['code'] : null,
                    'description' => isset($cursus['description']) ?
                        $cursus['description'] :
                        null,
                    'title' => $cursus['title'],
                    'blocking' => $cursus['blocking'],
                    'cursus_order' => $cursus['cursusOrder'],
                    'root' => $root,
                    'lvl' => $lvl,
                    'lft' => $cursus['lft'],
                    'rgt' => $cursus['rgt'],
                    'details' => $cursus['details'],
                    'course' => isset($cursus['course']) && isset($cursus['course']['id']) ?
                        $cursus['course']['id'] :
                        null,
                );
            } else {
                $parentId = $cursus['parentId'];

                if (!isset($cursusChildren[$parentId])) {
                    $cursusChildren[$parentId] = array();
                }
                $cursusChildren[$parentId][$id] = array(
                    'id' => $id,
                    'code' => isset($cursus['code']) ? $cursus['code'] : null,
                    'description' => isset($cursus['description']) ?
                        $cursus['description'] :
                        null,
                    'title' => $cursus['title'],
                    'blocking' => $cursus['blocking'],
                    'cursus_order' => $cursus['cursusOrder'],
                    'root' => $root,
                    'lvl' => $lvl,
                    'lft' => $cursus['lft'],
                    'rgt' => $cursus['rgt'],
                    'details' => $cursus['details'],
                    'course' => isset($cursus['course']) && isset($cursus['course']['id']) ?
                        $cursus['course']['id'] :
                        null,
                );
            }
        }
        $this->importRootCursus($roots, $cursusChildren, $courses);
    }

    private function getAllCoursesCodes()
    {
        $codes = array();
        $courses = $this->getAllCourses('', 'id', 'ASC', false);

        foreach ($courses as $course) {
            $codes[$course->getCode()] = true;
        }

        return $codes;
    }

    private function getAllCursusCodes()
    {
        $codes = array();
        $allCursus = $this->getAllCursus();

        foreach ($allCursus as $cursus) {
            $code = $cursus->getCode();

            if (!empty($code)) {
                $codes[$code] = true;
            }
        }

        return $codes;
    }

    private function generateValidCode($code, array $existingCodes)
    {
        $result = $code;

        if (isset($existingCodes[$code])) {
            $i = 0;

            do {
                ++$i;
                $result = $code.'_'.$i;
            } while (isset($existingCodes[$result]));
        }

        return $result;
    }

    private function importRootCursus(array $roots, array $children, array $courses)
    {
        $this->om->startFlushSuite();
        $codes = $this->getAllCursusCodes();
        $createdCursus = array();

        $index = 0;

        foreach ($roots as $root) {
            $cursus = new Cursus();
            $cursus->setTitle($root['title']);
            $cursus->setDescription($root['description']);
            $cursus->setBlocking($root['blocking']);
            $cursus->setCursusOrder($root['cursus_order']);
            $cursus->setDetails($root['details']);

            if (!empty($root['course']) && isset($courses[$root['course']])) {
                $cursus->setCourse($courses[$root['course']]);
            }

            if (!empty($root['code'])) {
                $code = $this->generateValidCode($root['code'], $codes);
                $cursus->setCode($code);
            }
            $this->om->persist($cursus);
            $createdCursus[$root['id']] = $cursus;
            ++$index;

            if ($index % 50 === 0) {
                $this->om->forceFlush();
            }

            if (isset($children[$root['id']])) {
                $this->importCursusChildren(
                    $root,
                    $children,
                    $courses,
                    $codes,
                    $createdCursus,
                    $index
                );
            }
        }
        $this->om->endFlushSuite();
    }

    private function importCursusChildren(
        array $parent,
        array $children,
        array $courses,
        array $codes,
        array &$createdCursus,
        &$index
    ) {
        if (isset($parent['id']) && isset($children[$parent['id']])) {
            foreach ($children[$parent['id']] as $child) {
                $cursus = new Cursus();
                $cursus->setTitle($child['title']);
                $cursus->setDescription($child['description']);
                $cursus->setBlocking($child['blocking']);
                $cursus->setCursusOrder($child['cursus_order']);
                $cursus->setDetails($child['details']);

                if (isset($createdCursus[$parent['id']])) {
                    $cursus->setParent($createdCursus[$parent['id']]);
                }

                if (!empty($child['course']) && isset($courses[$child['course']])) {
                    $cursus->setCourse($courses[$child['course']]);
                }

                if (!empty($child['code'])) {
                    $code = $this->generateValidCode($child['code'], $codes);
                    $cursus->setCode($code);
                }
                $this->om->persist($cursus);
                $createdCursus[$child['id']] = $cursus;
                ++$index;

                if ($index % 50 === 0) {
                    $this->om->forceFlush();
                }

                if (isset($children[$child['id']])) {
                    $this->importCursusChildren(
                        $child,
                        $children,
                        $courses,
                        $codes,
                        $createdCursus,
                        $index
                    );
                }
            }
        }
    }

    public function getSessionsDatas($search = '', $withPager = true, $page = 1, $max = 50)
    {
        $sessionsDatas = array();
        $sessions = empty($search) ?
            $this->courseSessionRepo->findAllUnclosedSessions() :
            $this->courseSessionRepo->findSearchedlUnclosedSessions($search);

        foreach ($sessions as $session) {
            $course = $session->getCourse();
            $courseCode = $course->getCode();

            if (!isset($sessionsDatas[$courseCode])) {
                $sessionsDatas[$courseCode] = array();
                $sessionsDatas[$courseCode]['course'] = $course;
                $sessionsDatas[$courseCode]['sessions'] = array();
            }
            $sessionsDatas[$courseCode]['sessions'][] = $session;
        }

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($sessionsDatas, $page, $max) :
            $sessionsDatas;
    }

    public function getCursusDatasForCursusRegistration(Cursus $cursus)
    {
        $hierarchy = array();
        $lockedHierarchy = array();
        $unlockedCursus = array();
        $hierarchyArray = array();
        $unlockedArray = array();
        $allRelatedCursus = $this->getRelatedHierarchyByCursus($cursus);

        foreach ($allRelatedCursus as $oneCursus) {
            $parent = $oneCursus->getParent();
            $lockedHierarchy[$oneCursus->getId()] = 'blocked';

            if (is_null($parent)) {
                if (!isset($hierarchy['root'])) {
                    $hierarchy['root'] = array();
                }
                $hierarchy['root'][] = $oneCursus;
            } else {
                $parentId = $parent->getId();

                if (!isset($hierarchy[$parentId])) {
                    $hierarchy[$parentId] = array();
                }
                $hierarchy[$parentId][] = $oneCursus;
            }
        }
        $this->unlockedHierarchy(
            $cursus,
            $hierarchy,
            $lockedHierarchy,
            $unlockedCursus
        );

        foreach ($hierarchy as $key => $values) {
            $hierarchyArray[$key] = array();

            foreach ($values as $value) {
                $course = $value->getCourse();
                $valueEntry = array(
                    'id' => $value->getId(),
                    'code' => $value->getCode(),
                    'title' => $value->getTitle(),
                    'cursusOrder' => $value->getCursusOrder(),
                    'blocking' => $value->isBlocking(),
                    'description' => $value->getDescription(),
                    'details' => $value->getDetails(),
                    'lft' => $value->getLft(),
                    'rgt' => $value->getRgt(),
                    'lvl' => $value->getLvl(),
                    'root' => $value->getRoot(),
                    'course' => is_null($course) ? null : $course->getId(),
                );
                $hierarchyArray[$key][] = $valueEntry;
            }
        }

        foreach ($unlockedCursus as $unlocked) {
            $unlockedArray[] = $unlocked->getId();
        }

        return array(
            'hierarchy' => $hierarchyArray,
            'lockedHierarchy' => $lockedHierarchy,
            'unlockedCursus' => $unlockedArray,
        );
    }

    public function getCursusUsersForCursusRegistration(Cursus $cursus)
    {
        $this->checkCursusToolRegistrationAccess();
        $usersArray = array();
        $cursusUsers = $this->getCursusUsersByCursus($cursus);

        foreach ($cursusUsers as $cursusUser) {
            $user = $cursusUser->getUser();
            $userEntry = array(
                'id' => $cursusUser->getId(),
                'userType' => $cursusUser->getUserType(),
                'registrationDate' => $cursusUser->getRegistrationDate(),
                'userId' => $user->getId(),
                'username' => $user->getUsername(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
            );
            $usersArray[] = $userEntry;
        }

        return $usersArray;
    }

    public function getCursusGroupsForCursusRegistration(Cursus $cursus)
    {
        $this->checkCursusToolRegistrationAccess();
        $groupsArray = array();
        $cursusGroups = $this->getCursusGroupsByCursus($cursus);

        foreach ($cursusGroups as $cursusGroup) {
            $group = $cursusGroup->getGroup();
            $groupEntry = array(
                'id' => $cursusGroup->getId(),
                'groupType' => $cursusGroup->getGroupType(),
                'registrationDate' => $cursusGroup->getRegistrationDate(),
                'groupId' => $group->getId(),
                'groupName' => $group->getName(),
            );
            $groupsArray[] = $groupEntry;
        }

        return $groupsArray;
    }

    public function getDatasForSearchedCursusRegistration($search)
    {
        $searchedCursusList = array();
        $searchedCursus = $this->getAllCursus(
            $search,
            'title',
            'ASC'
        );
        $rootIds = array();

        foreach ($searchedCursus as $cursus) {
            $course = $cursus->getCourse();
            $root = $cursus->getRoot();

            $searchedCursusList[] = array(
                'id' => $cursus->getId(),
                'code' => $cursus->getCode(),
                'title' => $cursus->getTitle(),
                'cursusOrder' => $cursus->getCursusOrder(),
                'blocking' => $cursus->isBlocking(),
                'description' => $cursus->getDescription(),
                'details' => $cursus->getDetails(),
                'lft' => $cursus->getLft(),
                'rgt' => $cursus->getRgt(),
                'lvl' => $cursus->getLvl(),
                'root' => $root,
                'course' => is_null($course) ? null : $course->getId(),
                'courseTitle' => is_null($course) ? null : $course->getTitle(),
                'courseCode' => is_null($course) ? null : $course->getCode(),
                'courseDescription' => is_null($course) ? null : $course->getDescription(),
            );

            if (!in_array($root, $rootIds)) {
                $rootIds[] = $root;
            }
        }
        $cursusRoots = $this->getCursusByIds($rootIds);
        $roots = array();

        foreach ($cursusRoots as $cursusRoot) {
            $roots[$cursusRoot->getId()] = array(
                'id' => $cursusRoot->getId(),
                'code' => $cursusRoot->getCode(),
                'title' => $cursusRoot->getTitle(),
            );
        }

        return array(
            'searchedCursus' => $searchedCursusList,
            'roots' => $roots,
        );
    }

    public function getDatasForCursusHierarchy(Cursus $cursus)
    {
        $hierarchy = array();
        $allCursus = $this->getRelatedHierarchyByCursus($cursus);

        foreach ($allCursus as $oneCursus) {
            $parent = $oneCursus->getParent();
            $course = $oneCursus->getCourse();

            if (is_null($parent)) {
                if (!isset($hierarchy['root'])) {
                    $hierarchy['root'] = array();
                }
                $hierarchy['root'][] = array(
                    'id' => $oneCursus->getId(),
                    'code' => $oneCursus->getCode(),
                    'title' => $oneCursus->getTitle(),
                    'cursusOrder' => $oneCursus->getCursusOrder(),
                    'blocking' => $oneCursus->isBlocking(),
                    'description' => $oneCursus->getDescription(),
                    'details' => $oneCursus->getDetails(),
                    'lft' => $oneCursus->getLft(),
                    'rgt' => $oneCursus->getRgt(),
                    'lvl' => $oneCursus->getLvl(),
                    'root' => $oneCursus->getRoot(),
                    'course' => is_null($course) ? null : $course->getId(),
                );
            } else {
                $parentId = $parent->getId();

                if (!isset($hierarchy[$parentId])) {
                    $hierarchy[$parentId] = array();
                }
                $hierarchy[$parentId][] = array(
                    'id' => $oneCursus->getId(),
                    'code' => $oneCursus->getCode(),
                    'title' => $oneCursus->getTitle(),
                    'cursusOrder' => $oneCursus->getCursusOrder(),
                    'blocking' => $oneCursus->isBlocking(),
                    'description' => $oneCursus->getDescription(),
                    'details' => $oneCursus->getDetails(),
                    'lft' => $oneCursus->getLft(),
                    'rgt' => $oneCursus->getRgt(),
                    'lvl' => $oneCursus->getLvl(),
                    'root' => $oneCursus->getRoot(),
                    'course' => is_null($course) ? null : $course->getId(),
                );
            }
        }

        return $hierarchy;
    }

    public function getCursusFromCursusIdsTxt($cursusIdsTxt)
    {
        $cursusIds = array();
        $cursusIdsArray = explode(',', $cursusIdsTxt);

        foreach ($cursusIdsArray as $cursusId) {
            if (!empty($cursusId)) {
                $cursusIds[] = intval($cursusId);
            }
        }
        $multipleCursus = $this->getCursusByIds($cursusIds);

        return $multipleCursus;
    }

    public function getSessionsFromSessionsIdsTxt($sessionsIdsTxt)
    {
        $sessionIds = array();
        $sessionsIdsArray = explode(',', $sessionsIdsTxt);

        foreach ($sessionsIdsArray as $sessionId) {
            $id = intval($sessionId);

            if (!empty($id)) {
                $sessionIds[] = $id;
            }
        }
        $sessions = $this->getSessionsByIds($sessionIds);

        return $sessions;
    }

    public function getUsersFromUsersIdsTxt($usersIdsTxt)
    {
        $userIds = array();
        $usersIdsArray = explode(',', $usersIdsTxt);

        foreach ($usersIdsArray as $userId) {
            if (!empty($userId)) {
                $userIds[] = intval($userId);
            }
        }
        $users = $this->userManager->getUsersByIds($userIds);

        return $users;
    }

    public function getCursusGroupsFromCursusGroupsIdsTxt($cursusGroupsIdsTxt)
    {
        $cursusGroupsIds = array();
        $cursusGroupsIdsArray = explode(',', $cursusGroupsIdsTxt);

        foreach ($cursusGroupsIdsArray as $cursusGroupId) {
            if (!empty($cursusGroupId)) {
                $cursusGroupsIds[] = intval($cursusGroupId);
            }
        }
        $cursusGroups = $this->getCursusGroupsByIds($cursusGroupsIds);

        return $cursusGroups;
    }

    public function getSessionsInfosFromCursusList($cursusList)
    {
        $sessionsInfos = array();
        $courses = array();

        foreach ($cursusList as $cursus) {
            $course = $cursus->getCourse();

            if (!is_null($course)) {
                $courses[] = $course;
            }
        }
        $sessions = $this->getSessionsByCourses($courses);

        foreach ($courses as $course) {
            $courseId = $course->getId();

            if (!isset($sessionsInfos[$courseId])) {
                $sessionsInfos[$courseId] = array();
                $sessionsInfos[$courseId]['courseId'] = $course->getId();
                $sessionsInfos[$courseId]['courseTitle'] = $course->getTitle();
                $sessionsInfos[$courseId]['courseCode'] = $course->getCode();
                $sessionsInfos[$courseId]['sessions'] = array();
            }
        }

        foreach ($sessions as $session) {
            if ($session->getSessionStatus() !== 2) {
                $courseId = $session->getCourse()->getId();

                $sessionsInfos[$courseId]['sessions'][] = array(
                    'sessionId' => $session->getId(),
                    'sessionName' => $session->getName(),
                    'sessionStatus' => $session->getSessionStatus(),
                );
            }
        }

        return $sessionsInfos;
    }

    public function registerGroupToCursusAndSessions(
        Group $group,
        array $multipleCursus,
        array $sessions
    ) {
        $this->checkCursusToolRegistrationAccess();
        $authenticatedUser = $this->tokenStorage->getToken()->getUser();
        $coursesWithSession = array();
        $sessionsToCreate = array();
        $root = 0;
        $cursusRoot = null;
        $registrationDate = new \DateTime();
        $configStartDate = $this->platformConfigHandler
            ->getParameter('cursusbundle_default_session_start_date');
        $configEndDate = $this->platformConfigHandler
            ->getParameter('cursusbundle_default_session_end_date');
        $startDate = empty($configStartDate) ?
            null :
            new \DateTime($configStartDate);
        $endDate = empty($configEndDate) ?
            null :
            new \DateTime($configEndDate);

        foreach ($sessions as $session) {
            $course = $session->getCourse();
            $coursesWithSession[$course->getId()] = true;
        }

        foreach ($multipleCursus as $cursus) {
            $root = $cursus->getRoot();
            $course = $cursus->getCourse();

            if (!is_null($course) &&
                !isset($coursesWithSession[$course->getId()]) &&
                !in_array($course, $sessionsToCreate)) {
                $sessionsToCreate[] = $course;
            }
        }

        if ($root > 0) {
            $cursusRoot = $this->getOneCursusById($root);
            $this->associateCursusToSessions($cursusRoot, $sessions);
        }
        // Generate the list of sessions where the user will be register
        foreach ($sessionsToCreate as $course) {
            $sessionName = $group->getName();
            $session = $this->createCourseSession(
                $course,
                $authenticatedUser,
                $sessionName,
                $cursusRoot,
                $registrationDate,
                $startDate,
                $endDate
            );
            $sessions[] = $session;
        }
        $results = $this->registerGroupToSessions($sessions, $group);

        if ($results['status'] === 'success') {
            $this->registerGroupToMultipleCursus($multipleCursus, $group);
        }

        return $results;
    }

    public function registerUsersToCursusAndSessions(
        array $users,
        array $multipleCursus,
        array $sessions
    ) {
        $this->checkCursusToolRegistrationAccess();
        $authenticatedUser = $this->tokenStorage->getToken()->getUser();
        $coursesWithSession = array();
        $sessionsToCreate = array();
        $root = 0;
        $cursusRoot = null;
        $registrationDate = new \DateTime();
        $configStartDate = $this->platformConfigHandler
            ->getParameter('cursusbundle_default_session_start_date');
        $configEndDate = $this->platformConfigHandler
            ->getParameter('cursusbundle_default_session_end_date');
        $startDate = empty($configStartDate) ?
            null :
            new \DateTime($configStartDate);
        $endDate = empty($configEndDate) ?
            null :
            new \DateTime($configEndDate);

        foreach ($sessions as $session) {
            $course = $session->getCourse();
            $coursesWithSession[$course->getId()] = true;
        }

        foreach ($multipleCursus as $cursus) {
            $root = $cursus->getRoot();
            $course = $cursus->getCourse();

            if (!is_null($course) &&
                !isset($coursesWithSession[$course->getId()]) &&
                !in_array($course, $sessionsToCreate)) {
                $sessionsToCreate[] = $course;
            }
        }

        if ($root > 0) {
            $cursusRoot = $this->getOneCursusById($root);
            $this->associateCursusToSessions($cursusRoot, $sessions);
        }
        // Generate the list of sessions where the user will be register
        foreach ($sessionsToCreate as $course) {
            if (is_null($cursusRoot)) {
                $sessionName = 'Session';
            } else {
                $sessionName = $cursusRoot->getTitle();
            }
            $sessionName .= ' ('.$registrationDate->format('d/m/Y H:i').')';
            $session = $this->createCourseSession(
                $course,
                $authenticatedUser,
                $sessionName,
                $cursusRoot,
                $registrationDate,
                $startDate,
                $endDate
            );
            $sessions[] = $session;
        }
        $results = $this->registerUsersToSessions($sessions, $users);

        if ($results['status'] === 'success') {
            $this->registerUsersToMultipleCursus($multipleCursus, $users);
        }

        return $results;
    }

    public function getValidatorsRoles()
    {
        $roles = array();
        $registrationTool = $this->toolManager
            ->getAdminToolByName('claroline_cursus_tool_registration');

        if (!is_null($registrationTool)) {
            $roles = $registrationTool->getRoles()->toArray();
        }
        $adminRole = $this->roleManager->getRoleByName('ROLE_ADMIN');
        $roles[] = $adminRole;

        return $roles;
    }

    public function getRegistrationQueuesDatasByValidator($search = '')
    {
        $this->checkCursusToolRegistrationAccess();
        $datas = array();
        $authenticatedUser = $this->tokenStorage->getToken()->getUser();
        $isAdmin = $this->authorization->isGranted('ROLE_ADMIN');

        if ($isAdmin) {
            $coursesQueues = empty($search) ?
                $this->getAllUnvalidatedCourseQueues() :
                $this->getAllSearchedUnvalidatedCourseQueues($search);
            $sessionsQueues = empty($search) ?
                $this->getAllUnvalidatedSessionQueues() :
                $this->getAllSearchedUnvalidatedSessionQueues($search);

            $datas['coursesQueues'] = $this->getCoursesQueuesDatasFromQueues($coursesQueues);
            $datas['sessionsQueues'] = $this->getSessionsQueuesDatasFromQueues($sessionsQueues);
        } else {
            $validatorCoursesQueues = empty($search) ?
                $this->getUnvalidatedCourseQueuesByValidator($authenticatedUser) :
                $this->getUnvalidatedSearchedCourseQueuesByValidator($authenticatedUser, $search);
            $orgaCoursesQueues = empty($search) ?
                $this->getUnvalidatedCourseQueuesByOrganization($authenticatedUser) :
                $this->getUnvalidatedSearchedCourseQueuesByOrganization($authenticatedUser, $search);
            $simpleCoursesQueues = empty($search) ?
                $this->getUnvalidatedCourseQueues() :
                $this->getUnvalidatedSearchedCourseQueues($search);

            $validatorSessionsQueues = empty($search) ?
                $this->getUnvalidatedSessionQueuesByValidator($authenticatedUser) :
                $this->getUnvalidatedSearchedSessionQueuesByValidator($authenticatedUser, $search);
            $orgaSessionsQueues = empty($search) ?
                $this->getUnvalidatedSessionQueuesByOrganization($authenticatedUser) :
                $this->getUnvalidatedSearchedSessionQueuesByOrganization($authenticatedUser, $search);
            $simpleSessionsQueues = empty($search) ?
                $this->getUnvalidatedSessionQueues() :
                $this->getUnvalidatedSearchedSessionQueues($search);

            $coursesQueues = $this->mergeCourseQueues(
                $validatorCoursesQueues,
                $orgaCoursesQueues,
                $simpleCoursesQueues
            );
            $sessionsQueues = $this->mergeSessionQueues(
                $validatorSessionsQueues,
                $orgaSessionsQueues,
                $simpleSessionsQueues
            );
            $datas['coursesQueues'] = $this->computeCoursesQueuesDatas(
                $validatorCoursesQueues,
                $orgaCoursesQueues,
                $simpleCoursesQueues
            );
            $datas['sessionsQueues'] = $this->computeSessionsQueuesDatas(
                $validatorSessionsQueues,
                $orgaSessionsQueues,
                $simpleSessionsQueues
            );
        }
        $datas['courses'] = $this->getCoursesDatasFromQueues($coursesQueues, $sessionsQueues);

        return $datas;
    }

    public function getCoursesDatasFromQueues(array $coursesQueues, array $sessionsQueues)
    {
        $datas = array();
        $courseIds = array();

        foreach ($coursesQueues as $queue) {
            $course = $queue->getCourse();
            $courseId = $course->getId();
            $courseIds[$courseId] = $courseId;
        }

        foreach ($sessionsQueues as $queue) {
            $session = $queue->getSession();
            $course = $session->getCourse();
            $courseId = $course->getId();
            $courseIds[$courseId] = $courseId;
        }
        $courses = $this->getCoursesByIds($courseIds);

        foreach ($courses as $course) {
            $courseDatas = array(
                'id' => $course->getId(),
                'code' => $course->getCode(),
                'title' => $course->getTitle(),
                'description' => $course->getDescription(),
                'publicRegistration' => $course->getPublicRegistration(),
                'publicUnregistration' => $course->getPublicUnregistration(),
                'registrationValidation' => $course->getRegistrationValidation(),
                'userValidation' => $course->getUserValidation(),
                'maxUsers' => $course->getMaxUsers(),
                'validators' => array(),
            );
            $validators = $course->getValidators();

            foreach ($validators as $validator) {
                $validatorsDatas = array(
                    'id' => $validator->getId(),
                    'username' => $validator->getUsername(),
                    'firstName' => $validator->getFirstName(),
                    'lastName' => $validator->getLastName(),
                );
                $courseDatas['validators'][] = $validatorsDatas;
            }
            $datas[] = $courseDatas;
        }

        return $datas;
    }

    public function getCoursesQueuesDatasFromQueues(array $queues)
    {
        $datas = array();

        foreach ($queues as $queue) {
            $user = $queue->getUser();
            $course = $queue->getCourse();
            $courseId = $course->getId();

            if (!isset($datas[$courseId])) {
                $datas[$courseId] = array();
            }

            $datas[$courseId][] = array(
                'id' => $queue->getId(),
                'courseId' => $courseId,
                'courseTitle' => $course->getTitle(),
                'applicationDate' => $queue->getApplicationDate()->format('Y-m-d H:i'),
                'status' => $queue->getStatus(),
                'userId' => $user->getId(),
                'username' => $user->getUsername(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'rights' => 0,
            );
        }

        return $datas;
    }

    public function getSessionsQueuesDatasFromQueues(array $queues)
    {
        $datas = array();

        foreach ($queues as $queue) {
            $user = $queue->getUser();
            $session = $queue->getSession();
            $course = $session->getCourse();
            $courseId = $course->getId();

            if (!isset($datas[$courseId])) {
                $datas[$courseId] = array();
            }

            $datas[$courseId][] = array(
                'id' => $queue->getId(),
                'courseId' => $courseId,
                'sessionId' => $session->getId(),
                'sessionName' => $session->getName(),
                'sessionMaxUsers' => $session->getMaxUsers(),
                'sessionUserValidation' => $session->getUserValidation(),
                'sessionStatus' => $session->getSessionStatus(),
                'sessionType' => $session->getType(),
                'applicationDate' => $queue->getApplicationDate()->format('Y-m-d H:i'),
                'status' => $queue->getStatus(),
                'userId' => $user->getId(),
                'username' => $user->getUsername(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'rights' => 0,
            );
        }

        return $datas;
    }

    public function mergeCourseQueues($validatorQueues, $orgaQueues, $simpleQueues)
    {
        $courseQueues = array();

        foreach ($validatorQueues as $queue) {
            $queueId = $queue->getId();
            $courseQueues[$queueId] = $queue;
        }

        foreach ($orgaQueues as $queue) {
            $queueId = $queue->getId();

            if (!isset($courseQueues[$queueId])) {
                $courseQueues[$queueId] = $queue;
            }
        }

        foreach ($simpleQueues as $queue) {
            $queueId = $queue->getId();

            if (!isset($courseQueues[$queueId])) {
                $courseQueues[$queueId] = $queue;
            }
        }

        return $courseQueues;
    }

    public function mergeSessionQueues($validatorQueues, $orgaQueues, $simpleQueues)
    {
        $sessionQueues = array();

        foreach ($validatorQueues as $queue) {
            $queueId = $queue->getId();
            $sessionQueues[$queueId] = $queue;
        }

        foreach ($orgaQueues as $queue) {
            $queueId = $queue->getId();

            if (!isset($sessionQueues[$queueId])) {
                $sessionQueues[$queueId] = $queue;
            }
        }

        foreach ($simpleQueues as $queue) {
            $queueId = $queue->getId();

            if (!isset($sessionQueues[$queueId])) {
                $sessionQueues[$queueId] = $queue;
            }
        }

        return $sessionQueues;
    }

    public function computeCoursesQueuesDatas($validatorQueues, $orgaQueues, $simpleQueues)
    {
        $datas = array();
        $queuesDatas = array();

        foreach ($validatorQueues as $queue) {
            $queueId = $queue->getId();
            $user = $queue->getUser();
            $course = $queue->getCourse();

            $queuesDatas[$queueId] = array(
                'id' => $queue->getId(),
                'courseId' => $course->getId(),
                'courseTitle' => $course->getTitle(),
                'applicationDate' => $queue->getApplicationDate()->format('Y-m-d H:i'),
                'status' => $queue->getStatus(),
                'userId' => $user->getId(),
                'username' => $user->getUsername(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'rights' => CourseRegistrationQueue::WAITING_VALIDATOR,
            );
        }

        foreach ($orgaQueues as $queue) {
            $queueId = $queue->getId();

            if (isset($queuesDatas[$queueId])) {
                $queuesDatas[$queueId]['rights'] += CourseRegistrationQueue::WAITING_ORGANIZATION;
            } else {
                $user = $queue->getUser();
                $course = $queue->getCourse();

                $queuesDatas[$queueId] = array(
                    'id' => $queue->getId(),
                    'courseId' => $course->getId(),
                    'courseTitle' => $course->getTitle(),
                    'applicationDate' => $queue->getApplicationDate()->format('Y-m-d H:i'),
                    'status' => $queue->getStatus(),
                    'userId' => $user->getId(),
                    'username' => $user->getUsername(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'rights' => CourseRegistrationQueue::WAITING_ORGANIZATION,
                );
            }
        }

        foreach ($simpleQueues as $queue) {
            $queueId = $queue->getId();

            if (isset($queuesDatas[$queueId])) {
                $queuesDatas[$queueId]['rights'] += CourseRegistrationQueue::WAITING;
            } else {
                $user = $queue->getUser();
                $course = $queue->getCourse();

                $queuesDatas[$queueId] = array(
                    'id' => $queue->getId(),
                    'courseId' => $course->getId(),
                    'courseTitle' => $course->getTitle(),
                    'applicationDate' => $queue->getApplicationDate()->format('Y-m-d H:i'),
                    'status' => $queue->getStatus(),
                    'userId' => $user->getId(),
                    'username' => $user->getUsername(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'rights' => CourseRegistrationQueue::WAITING,
                );
            }
        }

        foreach ($queuesDatas as $queueData) {
            $courseId = $queueData['courseId'];

            if (!isset($datas[$courseId])) {
                $datas[$courseId] = array();
            }
            $datas[$courseId][] = $queueData;
        }

        return $datas;
    }

    public function computeSessionsQueuesDatas($validatorQueues, $orgaQueues, $simpleQueues)
    {
        $datas = array();
        $queuesDatas = array();

        foreach ($validatorQueues as $queue) {
            $queueId = $queue->getId();
            $user = $queue->getUser();
            $session = $queue->getSession();
            $course = $session->getCourse();

            $queuesDatas[$queueId] = array(
                'id' => $queue->getId(),
                'courseId' => $course->getId(),
                'sessionId' => $session->getId(),
                'sessionName' => $session->getName(),
                'sessionMaxUsers' => $session->getMaxUsers(),
                'sessionUserValidation' => $session->getUserValidation(),
                'sessionStatus' => $session->getSessionStatus(),
                'sessionType' => $session->getType(),
                'applicationDate' => $queue->getApplicationDate()->format('Y-m-d H:i'),
                'status' => $queue->getStatus(),
                'userId' => $user->getId(),
                'username' => $user->getUsername(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'rights' => CourseRegistrationQueue::WAITING_VALIDATOR,
            );
        }

        foreach ($orgaQueues as $queue) {
            $queueId = $queue->getId();

            if (isset($queuesDatas[$queueId])) {
                $queuesDatas[$queueId]['rights'] += CourseRegistrationQueue::WAITING_ORGANIZATION;
            } else {
                $user = $queue->getUser();
                $session = $queue->getSession();
                $course = $session->getCourse();

                $queuesDatas[$queueId] = array(
                    'id' => $queue->getId(),
                    'courseId' => $course->getId(),
                    'sessionId' => $session->getId(),
                    'sessionName' => $session->getName(),
                    'sessionMaxUsers' => $session->getMaxUsers(),
                    'sessionUserValidation' => $session->getUserValidation(),
                    'sessionStatus' => $session->getSessionStatus(),
                    'sessionType' => $session->getType(),
                    'applicationDate' => $queue->getApplicationDate()->format('Y-m-d H:i'),
                    'status' => $queue->getStatus(),
                    'userId' => $user->getId(),
                    'username' => $user->getUsername(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'rights' => CourseRegistrationQueue::WAITING_ORGANIZATION,
                );
            }
        }

        foreach ($simpleQueues as $queue) {
            $queueId = $queue->getId();

            if (isset($queuesDatas[$queueId])) {
                $queuesDatas[$queueId]['rights'] += CourseRegistrationQueue::WAITING;
            } else {
                $user = $queue->getUser();
                $session = $queue->getSession();
                $course = $session->getCourse();

                $queuesDatas[$queueId] = array(
                    'id' => $queue->getId(),
                    'courseId' => $course->getId(),
                    'sessionId' => $session->getId(),
                    'sessionName' => $session->getName(),
                    'sessionMaxUsers' => $session->getMaxUsers(),
                    'sessionUserValidation' => $session->getUserValidation(),
                    'sessionStatus' => $session->getSessionStatus(),
                    'sessionType' => $session->getType(),
                    'applicationDate' => $queue->getApplicationDate()->format('Y-m-d H:i'),
                    'status' => $queue->getStatus(),
                    'userId' => $user->getId(),
                    'username' => $user->getUsername(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'rights' => CourseRegistrationQueue::WAITING,
                );
            }
        }

        foreach ($queuesDatas as $queueData) {
            $courseId = $queueData['courseId'];

            if (!isset($datas[$courseId])) {
                $datas[$courseId] = array();
            }
            $datas[$courseId][] = $queueData;
        }

        return $datas;
    }

    public function canValidateCourseQueue(CourseRegistrationQueue $queue)
    {
        $canValidate = false;

        if ($this->authorization->isGranted('ROLE_ADMIN')) {
            $canValidate = true;
        }

        if (!$canValidate) {
            $authenticatedUser = $this->tokenStorage->getToken()->getUser();
            $status = $queue->getStatus();
            $course = $queue->getCourse();
            $user = $queue->getUser();
            $userValidation =
                ($status & CourseRegistrationQueue::WAITING_USER) === CourseRegistrationQueue::WAITING_USER;
            $validatorValidation =
                ($status & CourseRegistrationQueue::WAITING_VALIDATOR) === CourseRegistrationQueue::WAITING_VALIDATOR;
            $organizationValidation =
                ($status & CourseRegistrationQueue::WAITING_ORGANIZATION) === CourseRegistrationQueue::WAITING_ORGANIZATION;
            $isValidator = $this->isCourseValidator($authenticatedUser, $course);
            $isOrganizationAdmin = $this->isUserOrganizationAdmin($authenticatedUser, $user);

            $canValidate = $this->hasCursusToolRegistrationAccess() &&
                !$userValidation &&
                (!$organizationValidation || $isOrganizationAdmin) &&
                ($organizationValidation || !$validatorValidation || $isValidator);
        }

        return $canValidate;
    }

    public function canValidateSessionQueue(CourseSessionRegistrationQueue $queue)
    {
        $canValidate = false;

        if ($this->authorization->isGranted('ROLE_ADMIN')) {
            $canValidate = true;
        }

        if (!$canValidate) {
            $authenticatedUser = $this->tokenStorage->getToken()->getUser();
            $status = $queue->getStatus();
            $session = $queue->getSession();
            $user = $queue->getUser();
            $userValidation =
                ($status & CourseRegistrationQueue::WAITING_USER) === CourseRegistrationQueue::WAITING_USER;
            $validatorValidation =
                ($status & CourseRegistrationQueue::WAITING_VALIDATOR) === CourseRegistrationQueue::WAITING_VALIDATOR;
            $organizationValidation =
                ($status & CourseRegistrationQueue::WAITING_ORGANIZATION) === CourseRegistrationQueue::WAITING_ORGANIZATION;
            $isValidator = $this->isSessionValidator($authenticatedUser, $session);
            $isOrganizationAdmin = $this->isUserOrganizationAdmin($authenticatedUser, $user);

            $canValidate = $this->hasCursusToolRegistrationAccess() &&
                !$userValidation &&
                (!$organizationValidation || $isOrganizationAdmin) &&
                ($organizationValidation || !$validatorValidation || $isValidator);
        }

        return $canValidate;
    }

    public function isUserOrganizationAdmin(User $authenticatedUser, User $user)
    {
        $isOrganizationAdmin = false;
        $organizations = $user->getOrganizations();

        foreach ($organizations as $organization) {
            if ($isOrganizationAdmin) {
                break;
            } else {
                $admins = $organization->getAdministrators();

                foreach ($admins as $admin) {
                    if ($admin === $authenticatedUser) {
                        $isOrganizationAdmin = true;
                        break;
                    }
                }
            }
        }

        return $isOrganizationAdmin;
    }

    public function isCourseValidator(User $user, Course $course)
    {
        $isValidator = false;
        $validators = $course->getValidators();

        foreach ($validators as $validator) {
            if ($validator->getId() === $user->getId()) {
                $isValidator = true;
                break;
            }
        }

        return $isValidator;
    }

    public function isSessionValidator(User $user, CourseSession $session)
    {
        $isValidator = false;
        $validators = $session->getValidators();

        foreach ($validators as $validator) {
            if ($validator === $user) {
                $isValidator = true;
                break;
            }
        }

        return $isValidator;
    }

    public function validateUserCourseRegistrationQueue(CourseRegistrationQueue $queue)
    {
        $status = $queue->getStatus();

        if ($status & CourseRegistrationQueue::WAITING_USER) {
            $status -= CourseRegistrationQueue::WAITING_USER;

            if ($status === 0) {
                $status = CourseRegistrationQueue::WAITING;
            }
            $queue->setStatus($status);
            $queue->setUserValidationDate(new \DateTime());
            $this->persistCourseRegistrationQueue($queue);

            $event = new LogCourseQueueUserValidateEvent($queue);
            $this->eventDispatcher->dispatch('log', $event);
        }
    }

    public function validateUserSessionRegistrationQueue(CourseSessionRegistrationQueue $queue)
    {
        $results = array('status' => 'success', 'datas' => array());
        $status = $queue->getStatus();
        $user = $queue->getUser();
        $session = $queue->getSession();

        if ($status & CourseRegistrationQueue::WAITING_USER) {
            $status -= CourseRegistrationQueue::WAITING_USER;
            $queue->setStatus($status);
            $queue->setUserValidationDate(new \DateTime());
            $this->persistCourseSessionRegistrationQueue($queue);

            $event = new LogSessionQueueUserValidateEvent($queue);
            $this->eventDispatcher->dispatch('log', $event);
        }

        if ($queue->getStatus() === 0) {
            $results = $this->registerUsersToSession($session, array($user), 0);

            if ($results['status'] === 'success') {
                $this->deleteSessionQueue($queue);
            } else {
                $queue->setStatus(CourseRegistrationQueue::WAITING);
                $this->persistCourseSessionRegistrationQueue($queue);
            }
        }

        return $results;
    }

    public function validateCourseQueue(CourseRegistrationQueue $queue)
    {
        $user = $queue->getUser();
        $course = $queue->getCourse();
        $queueDatas = array(
            'type' => 'none',
            'id' => $queue->getId(),
            'courseId' => $course->getId(),
            'applicationDate' => $queue->getApplicationDate(),
            'userId' => $user->getId(),
            'username' => $user->getUsername(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'queueStatus' => $queue->getStatus(),
        );
        $canValidate = $this->canValidateCourseQueue($queue);

        if ($canValidate) {
            $status = $queue->getStatus();
            $isAdmin = $this->authorization->isGranted('ROLE_ADMIN');

            if ($status !== CourseRegistrationQueue::WAITING) {
                $authenticatedUser = $this->tokenStorage->getToken()->getUser();
                $isWaitingOrganization = $status & CourseRegistrationQueue::WAITING_ORGANIZATION;
                $isWaitingValidator = $status & CourseRegistrationQueue::WAITING_VALIDATOR;

                if ($isAdmin) {
                    $status = 0;
                    $queue->setStatus($status);
                    $now = new \DateTime();

                    if ($isWaitingOrganization) {
                        $queue->setOrganizationValidationDate($now);
                        $queue->setOrganizationAdmin($authenticatedUser);
                        $event = new LogCourseQueueOrganizationValidateEvent($queue);
                        $this->eventDispatcher->dispatch('log', $event);
                    }

                    if ($isWaitingValidator) {
                        $queue->setValidatorValidationDate($now);
                        $queue->setValidator($authenticatedUser);
                        $event = new LogCourseQueueValidatorValidateEvent($queue);
                        $this->eventDispatcher->dispatch('log', $event);
                    }
                    $this->persistCourseRegistrationQueue($queue);
                    $queueDatas['type'] = 'admin_validated';
                } elseif ($isWaitingOrganization) {
                    $status -= CourseRegistrationQueue::WAITING_ORGANIZATION;
                    $queue->setStatus($status);
                    $queue->setOrganizationValidationDate(new \DateTime());
                    $queue->setOrganizationAdmin($authenticatedUser);
                    $this->persistCourseRegistrationQueue($queue);

                    $event = new LogCourseQueueOrganizationValidateEvent($queue);
                    $this->eventDispatcher->dispatch('log', $event);

                    $queueDatas['type'] = 'organization_validated';
                    $queueDatas['queueStatus'] = $queue->getStatus();
                    $queueDatas['organizationValidationDate'] = $queue->getValidatorValidationDate();
                    $queueDatas['organizationAdminId'] = $authenticatedUser->getId();
                    $queueDatas['organizationAdminUsername'] = $authenticatedUser->getUsername();
                    $queueDatas['organizationAdminFirstName'] = $authenticatedUser->getFirstName();
                    $queueDatas['organizationAdminLastName'] = $authenticatedUser->getLastName();
                } elseif ($isWaitingValidator) {
                    $status -= CourseRegistrationQueue::WAITING_VALIDATOR;
                    $queue->setStatus($status);
                    $queue->setValidatorValidationDate(new \DateTime());
                    $queue->setValidator($authenticatedUser);
                    $this->persistCourseRegistrationQueue($queue);

                    $event = new LogCourseQueueValidatorValidateEvent($queue);
                    $this->eventDispatcher->dispatch('log', $event);

                    $queueDatas['type'] = 'validator_validated';
                    $queueDatas['queueStatus'] = $queue->getStatus();
                    $queueDatas['validatorValidationDate'] = $queue->getValidatorValidationDate();
                    $queueDatas['validatorId'] = $authenticatedUser->getId();
                    $queueDatas['validatorUsername'] = $authenticatedUser->getUsername();
                    $queueDatas['validatorFirstName'] = $authenticatedUser->getFirstName();
                    $queueDatas['validatorLastName'] = $authenticatedUser->getLastName();
                }
            }

            if ($queue->getStatus() === 0) {
                $queue->setStatus(CourseRegistrationQueue::WAITING);
                $this->persistCourseRegistrationQueue($queue);
                $queueDatas['queueStatus'] = $queue->getStatus();
            }
        } else {
            $queueDatas['type'] = 'not_authorized';
        }

        return $queueDatas;
    }

    public function validateSessionQueue(CourseSessionRegistrationQueue $queue)
    {
        $user = $queue->getUser();
        $session = $queue->getSession();
        $course = $session->getCourse();
        $queueDatas = array(
            'status' => 'success',
            'type' => 'none',
            'id' => $queue->getId(),
            'courseId' => $course->getId(),
            'sessionId' => $session->getId(),
            'applicationDate' => $queue->getApplicationDate(),
            'userId' => $user->getId(),
            'username' => $user->getUsername(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'queueStatus' => $queue->getStatus(),
        );
        $canValidate = $this->canValidateSessionQueue($queue);

        if ($canValidate) {
            $status = $queue->getStatus();

            if ($status === CourseRegistrationQueue::WAITING) {
                $queue->setStatus(0);
            } elseif ($status > 0) {
                $isAdmin = $this->authorization->isGranted('ROLE_ADMIN');
                $authenticatedUser = $this->tokenStorage->getToken()->getUser();

                if ($isAdmin) {
                    $status = 0;
                } elseif ($status & CourseRegistrationQueue::WAITING_ORGANIZATION) {
                    $status -= CourseRegistrationQueue::WAITING_ORGANIZATION;
                    $queue->setOrganizationValidationDate(new \DateTime());
                    $queue->setOrganizationAdmin($authenticatedUser);
                    $queue->setStatus($status);
                    $this->persistCourseSessionRegistrationQueue($queue);

                    $event = new LogSessionQueueOrganizationValidateEvent($queue);
                    $this->eventDispatcher->dispatch('log', $event);

                    $queueDatas['type'] = 'organization_validated';
                    $queueDatas['queueStatus'] = $queue->getStatus();
                    $queueDatas['organizationValidationDate'] = $queue->getValidatorValidationDate();
                    $queueDatas['organizationAdminId'] = $authenticatedUser->getId();
                    $queueDatas['organizationAdminUsername'] = $authenticatedUser->getUsername();
                    $queueDatas['organizationAdminFirstName'] = $authenticatedUser->getFirstName();
                    $queueDatas['organizationAdminLastName'] = $authenticatedUser->getLastName();
                } elseif ($status & CourseRegistrationQueue::WAITING_VALIDATOR) {
                    $status -= CourseRegistrationQueue::WAITING_VALIDATOR;
                    $queue->setValidatorValidationDate(new \DateTime());
                    $queue->setValidator($authenticatedUser);
                    $queue->setStatus($status);
                    $this->persistCourseSessionRegistrationQueue($queue);

                    $event = new LogSessionQueueValidatorValidateEvent($queue);
                    $this->eventDispatcher->dispatch('log', $event);

                    $queueDatas['type'] = 'validator_validated';
                    $queueDatas['queueStatus'] = $queue->getStatus();
                    $queueDatas['validatorValidationDate'] = $queue->getValidatorValidationDate();
                    $queueDatas['validatorId'] = $authenticatedUser->getId();
                    $queueDatas['validatorUsername'] = $authenticatedUser->getUsername();
                    $queueDatas['validatorFirstName'] = $authenticatedUser->getFirstName();
                    $queueDatas['validatorLastName'] = $authenticatedUser->getLastName();
                }
            }

            if ($queue->getStatus() === 0) {
                $results = $this->registerUsersToSession($session, array($user), 0);

                if ($results['status'] === 'success') {
                    $this->deleteSessionQueue($queue);
                    $queueDatas['type'] = 'registered';
                } else {
                    $queue->setStatus(CourseRegistrationQueue::WAITING);
                    $this->persistCourseSessionRegistrationQueue($queue);
                    $queueDatas['status'] = 'failed';
                    $queueDatas['queueStatus'] = 1;
                    $queueDatas['datas'] = $results['datas'];
                }
            }
        } else {
            $queueDatas['type'] = 'not_authorized';
        }

        return $queueDatas;
    }

    public function persistCourseRegistrationQueue(CourseRegistrationQueue $queue)
    {
        $this->om->persist($queue);
        $this->om->flush();
    }

    public function persistCourseSessionRegistrationQueue(CourseSessionRegistrationQueue $queue)
    {
        $this->om->persist($queue);
        $this->om->flush();
    }

    public function getWorkspacesListForCurrentUser()
    {
        $token = $this->tokenStorage->getToken();
        $roles = $this->utils->getRoles($token);
        $workspaces = $this->workspaceManager->getOpenableWorkspacesByRoles($roles);

        return $workspaces;
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
    ) {
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
    ) {
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
    ) {
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
    ) {
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
    ) {
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
    ) {
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

    public function getCursusByParent(
        Cursus $parent,
        $search = '',
        $orderedBy = 'cursusOrder',
        $order = 'ASC',
        $withPager = false,
        $page = 1,
        $max = 50
    ) {
        $cursus = empty($search) ?
            $this->cursusRepo->findCursusByParent($parent, $orderedBy, $order) :
            $this->cursusRepo->findSearchedCursusByParent($parent, $search, $orderedBy, $order);

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($cursus, $page, $max) :
            $cursus;
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
    ) {
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
    ) {
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
    ) {
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
    ) {
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

    public function getCoursesByIds(array $ids, $orderedBy = 'title', $order = 'ASC')
    {
        return count($ids) > 0 ?
            $this->courseRepo->findCoursesByIds($ids, $orderedBy, $order) :
            array();
    }

    /******************************************
     * Access to CursusUserRepository methods *
     ******************************************/

    public function getCursusUsersByCursus(
        Cursus $cursus,
        $executeQuery = true
    ) {
        return $this->cursusUserRepo->findCursusUsersByCursus(
            $cursus,
            $executeQuery
        );
    }

    public function getOneCursusUserByCursusAndUser(
        Cursus $cursus,
        User $user,
        $executeQuery = true
    ) {
        return $this->cursusUserRepo->findOneCursusUserByCursusAndUser(
            $cursus,
            $user,
            $executeQuery
        );
    }

    public function getCursusUsersFromCursusAndUsers(
        array $cursus,
        array $users
    ) {
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
    ) {
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
    ) {
        $this->checkCursusToolRegistrationAccess();
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
    ) {
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
    ) {
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
    ) {
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
    ) {
        $this->checkCursusToolRegistrationAccess();
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
    ) {
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

    public function getCursusGroupsByIds(array $ids, $executeQuery = true)
    {
        return count($ids) > 0 ?
            $this->cursusGroupRepo->findCursusGroupsByIds($ids, $executeQuery)
            : array();
    }

    /*********************************************
     * Access to CourseSessionRepository methods *
     *********************************************/

    public function getSessionsByCourse(
        Course $course,
        $orderedBy = 'creationDate',
        $order = 'ASC',
        $executeQuery = true
    ) {
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
        $order = 'ASC',
        $executeQuery = true
    ) {
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
        $order = 'ASC',
        $executeQuery = true
    ) {
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
        $order = 'ASC',
        $executeQuery = true
    ) {
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
        $order = 'ASC',
        $executeQuery = true
    ) {
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

    public function getDefaultPublicSessionsByCourse(
        Course $course,
        $orderedBy = 'creationDate',
        $order = 'ASC',
        $executeQuery = true
    ) {
        return $this->courseSessionRepo->findDefaultPublicSessionsByCourse(
            $course,
            $orderedBy,
            $order,
            $executeQuery
        );
    }

    public function getSessionsByIds(array $ids, $executeQuery = true)
    {
        return count($ids) > 0 ?
            $this->courseSessionRepo->findSessionsByIds($ids, $executeQuery)
            : array();
    }

    /*************************************************
     * Access to CourseSessionUserRepository methods *
     *************************************************/

    public function getOneSessionUserBySessionAndUserAndType(
        CourseSession $session,
        User $user,
        $userType,
        $executeQuery = true
    ) {
        return $this->sessionUserRepo->findOneSessionUserBySessionAndUserAndType(
            $session,
            $user,
            $userType,
            $executeQuery
        );
    }

    public function getSessionUsersByUser(
        User $user,
        $executeQuery = true
    ) {
        return $this->sessionUserRepo->findSessionUsersByUser(
            $user,
            $executeQuery
        );
    }

    public function getSessionUsersBySession(
        CourseSession $session,
        $executeQuery = true
    ) {
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
    ) {
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
    ) {
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
    ) {
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
    ) {
        return $this->sessionGroupRepo->findOneSessionGroupBySessionAndGroup(
            $session,
            $group,
            $executeQuery
        );
    }

    public function getSessionGroupsBySession(
        CourseSession $session,
        $executeQuery = true
    ) {
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
    ) {
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
    ) {
        return $this->sessionGroupRepo->findSessionGroupsByGroup(
            $group,
            $executeQuery
        );
    }

    public function getUnregisteredGroupsBySession(
        CourseSession $session,
        $groupType,
        $search = '',
        $orderedBy = 'name',
        $order = 'ASC',
        $withPager = true,
        $page = 1,
        $max = 50
    ) {
        $groups = empty($search) ?
            $this->sessionGroupRepo->findUnregisteredGroupsBySession(
                $session,
                $groupType,
                $orderedBy,
                $order
            ) :
            $this->sessionGroupRepo->findSearchedUnregisteredGroupsBySession(
                $session,
                $groupType,
                $search,
                $orderedBy,
                $order
            );

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($groups, $page, $max) :
            $groups;
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
    ) {
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

    public function getSessionQueuesByCourses(array $courses)
    {
        return count($courses) > 0 ?
            $this->sessionQueueRepo->findSessionQueuesByCourses($courses) :
            array();
    }

    public function getAllUnvalidatedSessionQueues()
    {
        return $this->sessionQueueRepo->findAllUnvalidatedSessionQueues();
    }

    public function getAllSearchedUnvalidatedSessionQueues($search)
    {
        return $this->sessionQueueRepo->findAllSearchedUnvalidatedSessionQueues($search);
    }

    public function getUnvalidatedSessionQueuesByValidator(User $user)
    {
        return $this->sessionQueueRepo->findUnvalidatedSessionQueuesByValidator($user);
    }

    public function getUnvalidatedSearchedSessionQueuesByValidator(User $user, $search)
    {
        return $this->sessionQueueRepo->findUnvalidatedSearchedSessionQueuesByValidator($user, $search);
    }

    public function getUnvalidatedSessionQueuesByOrganization(User $user)
    {
        return $this->sessionQueueRepo->findUnvalidatedSessionQueuesByOrganization($user);
    }

    public function getUnvalidatedSearchedSessionQueuesByOrganization(User $user, $search)
    {
        return $this->sessionQueueRepo->findUnvalidatedSearchedSessionQueuesByOrganization($user, $search);
    }

    public function getUnvalidatedSessionQueues()
    {
        return $this->sessionQueueRepo->findUnvalidatedSessionQueues();
    }

    public function getUnvalidatedSearchedSessionQueues($search)
    {
        return $this->sessionQueueRepo->findUnvalidatedSearchedSessionQueues($search);
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
    ) {
        return $this->courseQueueRepo->findOneCourseQueueByCourseAndUser(
            $course,
            $user,
            $executeQuery
        );
    }

    public function getAllUnvalidatedCourseQueues()
    {
        return $this->courseQueueRepo->findAllUnvalidatedCourseQueues();
    }

    public function getAllSearchedUnvalidatedCourseQueues($search)
    {
        return $this->courseQueueRepo->findAllSearchedUnvalidatedCourseQueues($search);
    }

    public function getUnvalidatedCourseQueuesByValidator(User $user)
    {
        return $this->courseQueueRepo->findUnvalidatedCourseQueuesByValidator($user);
    }

    public function getUnvalidatedSearchedCourseQueuesByValidator(User $user, $search)
    {
        return $this->courseQueueRepo->findUnvalidatedSearchedCourseQueuesByValidator($user, $search);
    }

    public function getUnvalidatedCourseQueuesByOrganization(User $user)
    {
        return $this->courseQueueRepo->findUnvalidatedCourseQueuesByOrganization($user);
    }

    public function getUnvalidatedSearchedCourseQueuesByOrganization(User $user, $search)
    {
        return $this->courseQueueRepo->findUnvalidatedSearchedCourseQueuesByOrganization($user, $search);
    }

    public function getUnvalidatedCourseQueues()
    {
        return $this->courseQueueRepo->findUnvalidatedCourseQueues();
    }

    public function getUnvalidatedSearchedCourseQueues($search)
    {
        return $this->courseQueueRepo->findUnvalidatedSearchedCourseQueues($search);
    }

    /******************
     * Others methods *
     ******************/

    public function getSessionsByUserAndType(User $user, $userType = 0)
    {
        $sessions = array();
        $sessionUsers = $this->getSessionUsersByUser($user);

        foreach ($sessionUsers as $sessionUser) {
            $type = $sessionUser->getUserType();

            if ($type === $userType) {
                $sessions[] = $sessionUser->getSession();
            }
        }

        return $sessions;
    }

    public function getUsersBySessionAndType(CourseSession $session, $userType = 0)
    {
        $users = array();
        $sessionUsers = $this->getSessionUsersBySession($session);

        foreach ($sessionUsers as $sessionUser) {
            $type = $sessionUser->getUserType();

            if ($type === $userType) {
                $users[] = $sessionUser->getUser();
            }
        }

        return $users;
    }

    public function getSessionsByGroupAndType(Group $group, $groupType = 0)
    {
        $sessions = array();
        $sessionGroups = $this->getSessionGroupsByGroup($group);

        foreach ($sessionGroups as $sessionGroup) {
            $type = $sessionGroup->getGroupType();

            if ($type === $groupType) {
                $sessions[] = $sessionGroup->getSession();
            }
        }

        return $sessions;
    }

    /*******************
     * Rights checking *
     *******************/

    private function checkCursusToolRegistrationAccess()
    {
        $cursusTool = $this->toolManager->getAdminToolByName('claroline_cursus_tool_registration');

        if (is_null($cursusTool) ||
            !$this->authorization->isGranted('OPEN', $cursusTool)) {
            throw new AccessDeniedException();
        }
    }

    private function hasCursusToolRegistrationAccess()
    {
        $cursusTool = $this->toolManager->getAdminToolByName('claroline_cursus_tool_registration');

        return $this->authorization->isGranted('OPEN', $cursusTool);
    }
}
