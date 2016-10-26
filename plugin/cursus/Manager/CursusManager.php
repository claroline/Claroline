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
use Claroline\CoreBundle\Entity\Model\WorkspaceModel;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
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
use Claroline\CursusBundle\Entity\CursusDisplayedWord;
use Claroline\CursusBundle\Entity\CursusGroup;
use Claroline\CursusBundle\Entity\CursusUser;
use Claroline\CursusBundle\Entity\DocumentModel;
use Claroline\CursusBundle\Entity\SessionEvent;
use Claroline\CursusBundle\Entity\SessionEventComment;
use Claroline\CursusBundle\Entity\SessionEventUser;
use Claroline\CursusBundle\Event\Log\LogCourseCreateEvent;
use Claroline\CursusBundle\Event\Log\LogCourseDeleteEvent;
use Claroline\CursusBundle\Event\Log\LogCourseQueueCreateEvent;
use Claroline\CursusBundle\Event\Log\LogCourseQueueDeclineEvent;
use Claroline\CursusBundle\Event\Log\LogCourseQueueOrganizationValidateEvent;
use Claroline\CursusBundle\Event\Log\LogCourseQueueTransferEvent;
use Claroline\CursusBundle\Event\Log\LogCourseQueueUserValidateEvent;
use Claroline\CursusBundle\Event\Log\LogCourseQueueValidatorValidateEvent;
use Claroline\CursusBundle\Event\Log\LogCourseSessionCreateEvent;
use Claroline\CursusBundle\Event\Log\LogCourseSessionDeleteEvent;
use Claroline\CursusBundle\Event\Log\LogCourseSessionUserRegistrationEvent;
use Claroline\CursusBundle\Event\Log\LogCourseSessionUserUnregistrationEvent;
use Claroline\CursusBundle\Event\Log\LogCursusCreateEvent;
use Claroline\CursusBundle\Event\Log\LogCursusDeleteEvent;
use Claroline\CursusBundle\Event\Log\LogCursusUserRegistrationEvent;
use Claroline\CursusBundle\Event\Log\LogCursusUserUnregistrationEvent;
use Claroline\CursusBundle\Event\Log\LogSessionEventCreateEvent;
use Claroline\CursusBundle\Event\Log\LogSessionEventDeleteEvent;
use Claroline\CursusBundle\Event\Log\LogSessionEventUserRegistrationEvent;
use Claroline\CursusBundle\Event\Log\LogSessionEventUserUnregistrationEvent;
use Claroline\CursusBundle\Event\Log\LogSessionQueueCreateEvent;
use Claroline\CursusBundle\Event\Log\LogSessionQueueDeclineEvent;
use Claroline\CursusBundle\Event\Log\LogSessionQueueOrganizationValidateEvent;
use Claroline\CursusBundle\Event\Log\LogSessionQueueUserValidateEvent;
use Claroline\CursusBundle\Event\Log\LogSessionQueueValidatorValidateEvent;
use Claroline\MessageBundle\Manager\MessageManager;
use Claroline\PdfGeneratorBundle\Manager\PdfManager;
use FormaLibre\ReservationBundle\Entity\Resource;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\File;
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
    private $messageManager;
    private $om;
    private $pagerFactory;
    private $platformConfigHandler;
    private $roleManager;
    private $router;
    private $serializer;
    private $defaultTemplate;
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
    private $documentModelRepo;
    private $reservationResourceRepo;
    private $sessionEventRepo;
    private $sessionEventUserRepo;
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
 *     "messageManager"        = @DI\Inject("claroline.manager.message_manager"),
 *     "om"                    = @DI\Inject("claroline.persistence.object_manager"),
 *     "pagerFactory"          = @DI\Inject("claroline.pager.pager_factory"),
 *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
 *     "roleManager"           = @DI\Inject("claroline.manager.role_manager"),
 *     "router"                = @DI\Inject("router"),
 *     "serializer"            = @DI\Inject("jms_serializer"),
 *     "defaultTemplate"       = @DI\Inject("%claroline.param.default_template%"),
 *     "templating"            = @DI\Inject("templating"),
 *     "tokenStorage"          = @DI\Inject("security.token_storage"),
 *     "toolManager"           = @DI\Inject("claroline.manager.tool_manager"),
 *     "translator"            = @DI\Inject("translator"),
 *     "userManager"           = @DI\Inject("claroline.manager.user_manager"),
 *     "ut"                    = @DI\Inject("claroline.utilities.misc"),
 *     "utils"                 = @DI\Inject("claroline.security.utilities"),
 *     "workspaceManager"      = @DI\Inject("claroline.manager.workspace_manager"),
 *     "pdfManager"            = @DI\Inject("claroline.manager.pdf_manager")
 * })
 */
    // why no claroline dispatcher ?
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ContainerInterface $container,
        ContentManager $contentManager,
        EventDispatcherInterface $eventDispatcher,
        MailManager $mailManager,
        MessageManager $messageManager,
        ObjectManager $om,
        PagerFactory $pagerFactory,
        PlatformConfigurationHandler $platformConfigHandler,
        RoleManager $roleManager,
        UrlGeneratorInterface $router,
        Serializer $serializer,
        $defaultTemplate,
        TwigEngine $templating,
        TokenStorageInterface $tokenStorage,
        ToolManager $toolManager,
        TranslatorInterface $translator,
        UserManager $userManager,
        ClaroUtilities $ut,
        Utilities $utils,
        WorkspaceManager $workspaceManager,
        PdfManager $pdfManager,
        $clarolineDispatcher
    ) {
        $this->archiveDir = $container->getParameter('claroline.param.platform_generated_archive_path');
        $this->authorization = $authorization;
        $this->container = $container;
        $this->contentManager = $contentManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->iconsDirectory = $this->container->getParameter('claroline.param.thumbnails_directory').'/';
        $this->mailManager = $mailManager;
        $this->messageManager = $messageManager;
        $this->om = $om;
        $this->pagerFactory = $pagerFactory;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->roleManager = $roleManager;
        $this->router = $router;
        $this->serializer = $serializer;
        $this->defaultTemplate = $defaultTemplate;
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
        $this->toolManager = $toolManager;
        $this->translator = $translator;
        $this->userManager = $userManager;
        $this->ut = $ut;
        $this->utils = $utils;
        $this->workspaceManager = $workspaceManager;
        $this->clarolineDispatcher = $clarolineDispatcher;
        $this->pdfManager = $pdfManager;

        $this->courseRepo = $om->getRepository('ClarolineCursusBundle:Course');
        $this->courseQueueRepo = $om->getRepository('ClarolineCursusBundle:CourseRegistrationQueue');
        $this->courseSessionRepo = $om->getRepository('ClarolineCursusBundle:CourseSession');
        $this->coursesWidgetConfigRepo = $om->getRepository('ClarolineCursusBundle:CoursesWidgetConfig');
        $this->cursusRepo = $om->getRepository('ClarolineCursusBundle:Cursus');
        $this->cursusGroupRepo = $om->getRepository('ClarolineCursusBundle:CursusGroup');
        $this->cursusUserRepo = $om->getRepository('ClarolineCursusBundle:CursusUser');
        $this->cursusWordRepo = $om->getRepository('ClarolineCursusBundle:CursusDisplayedWord');
        $this->documentModelRepo = $om->getRepository('ClarolineCursusBundle:DocumentModel');
        $this->reservationResourceRepo = $om->getRepository('FormaLibre\ReservationBundle\Entity\Resource');
        $this->sessionEventRepo = $om->getRepository('ClarolineCursusBundle:SessionEvent');
        $this->sessionEventUserRepo = $om->getRepository('ClarolineCursusBundle:SessionEventUser');
        $this->sessionGroupRepo = $om->getRepository('ClarolineCursusBundle:CourseSessionGroup');
        $this->sessionQueueRepo = $om->getRepository('ClarolineCursusBundle:CourseSessionRegistrationQueue');
        $this->sessionUserRepo = $om->getRepository('ClarolineCursusBundle:CourseSessionUser');
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
            $result = $this->translator->trans($word, [], 'cursus');
        } else {
            $displayedWord = $cursusDisplayedWord->getDisplayedWord();
            $result = empty($displayedWord) ?
                $this->translator->trans($word, [], 'cursus') :
                $displayedWord;
        }

        return $result;
    }

    public function createCursus(
        $title,
        $code,
        Cursus $parent = null,
        Course $course = null,
        $description = null,
        $blocking = false,
        $icon = null,
        $color = null,
        Workspace $workspace = null
    ) {
        $cursus = new Cursus();
        $cursus->setTitle($title);
        $cursus->setCode($code);
        $cursus->setParent($parent);
        $cursus->setCourse($course);
        $cursus->setDescription($description);
        $cursus->setBlocking($blocking);
        $cursus->setIcon($icon);
        $cursus->setWorkspace($workspace);
        $cursus->setDetails(['color' => $color]);
        $orderMax = is_null($parent) ?
            $this->getLastRootCursusOrder() :
            $this->getLastCursusOrderByParent($parent);
        $order = is_null($orderMax) ? 1 : intval($orderMax) + 1;
        $cursus->setCursusOrder($order);
        $this->persistCursus($cursus);
        $event = new LogCursusCreateEvent($cursus);
        $this->eventDispatcher->dispatch('log', $event);

        return $cursus;
    }

    public function persistCursus(Cursus $cursus)
    {
        $this->om->persist($cursus);
        $this->om->flush();
    }

    public function deleteCursus(Cursus $cursus)
    {
        $details = [];
        $details['id'] = $cursus->getId();
        $details['title'] = $cursus->getTitle();
        $details['code'] = $cursus->getCode();
        $details['blocking'] = $cursus->isBlocking();
        $details['details'] = $cursus->getDetails();
        $details['root'] = $cursus->getRoot();
        $details['lvl'] = $cursus->getLvl();
        $details['lft'] = $cursus->getLft();
        $details['rgt'] = $cursus->getRgt();
        $parent = $cursus->getParent();
        $course = $cursus->getCourse();
        $workspace = $cursus->getWorkspace();

        if (!is_null($parent)) {
            $details['parentId'] = $parent->getId();
            $details['parentTitle'] = $parent->getTitle();
            $details['parentCode'] = $parent->getCode();
        }

        if (!is_null($course)) {
            $details['courseId'] = $course->getId();
            $details['courseTitle'] = $course->getTitle();
            $details['courseCode'] = $course->getCode();
        }

        if (!is_null($workspace)) {
            $details['workspaceId'] = $workspace->getId();
            $details['workspaceName'] = $workspace->getName();
            $details['workspaceCode'] = $workspace->getCode();
            $details['workspaceGuid'] = $workspace->getGuid();
        }
        $this->om->remove($cursus);
        $this->om->flush();
        $event = new LogCursusDeleteEvent($details);
        $this->eventDispatcher->dispatch('log', $event);
    }

    public function createCourse(
        $title,
        $code,
        $description = null,
        $publicRegistration = false,
        $publicUnregistration = false,
        $registrationValidation = false,
        $tutorRoleName = null,
        $learnerRoleName = null,
        WorkspaceModel $workspaceModel = null,
        Workspace $workspace = null,
        $icon = null,
        $userValidation = false,
        $organizationValidation = false,
        $maxUsers = null,
        $defaultSessionDuration = 1,
        $withSessionEvent = true,
        array $validators = [],
        $displayOrder = 500
    ) {
        $course = new Course();
        $course->setTitle($title);
        $course->setCode($code);
        $course->setPublicRegistration($publicRegistration);
        $course->setPublicUnregistration($publicUnregistration);
        $course->setRegistrationValidation($registrationValidation);
        $course->setWorkspaceModel($workspaceModel);
        $course->setWorkspace($workspace);
        $course->setIcon($icon);
        $course->setUserValidation($userValidation);
        $course->setOrganizationValidation($organizationValidation);
        $course->setDefaultSessionDuration($defaultSessionDuration);
        $course->setWithSessionEvent($withSessionEvent);
        $course->setDisplayOrder($displayOrder);

        if ($description) {
            $course->setDescription($description);
        }
        if ($tutorRoleName) {
            $course->setTutorRoleName($tutorRoleName);
        }
        if ($learnerRoleName) {
            $course->setLearnerRoleName($learnerRoleName);
        }
        if ($maxUsers) {
            $course->setMaxUsers($maxUsers);
        }
        foreach ($validators as $validator) {
            $course->addValidator($validator);
        }
        $this->persistCourse($course);
        $event = new LogCourseCreateEvent($course);
        $this->eventDispatcher->dispatch('log', $event);

        return $course;
    }

    public function persistCourse(Course $course)
    {
        $this->om->persist($course);
        $this->om->flush();
    }

    public function deleteCourse(Course $course)
    {
        $details = [];
        $details['id'] = $course->getId();
        $details['title'] = $course->getTitle();
        $details['code'] = $course->getCode();
        $details['publicRegistration'] = $course->getPublicRegistration();
        $details['publicUnregistration'] = $course->getPublicUnregistration();
        $details['registrationValidation'] = $course->getRegistrationValidation();
        $details['icon'] = $course->getIcon();
        $details['tutorRoleName'] = $course->getTutorRoleName();
        $details['learnerRoleName'] = $course->getLearnerRoleName();
        $details['userValidation'] = $course->getUserValidation();
        $details['organizationValidation'] = $course->getOrganizationValidation();
        $details['maxUsers'] = $course->getMaxUsers();
        $details['defaultSessionDuration'] = $course->getDefaultSessionDuration();
        $details['withSessionEvent'] = $course->getWithSessionEvent();
        $workspace = $course->getWorkspace();
        $workspaceModel = $course->getWorkspaceModel();

        if (!is_null($workspace)) {
            $details['workspaceId'] = $workspace->getId();
            $details['workspaceName'] = $workspace->getName();
            $details['workspaceCode'] = $workspace->getCode();
            $details['workspaceGuid'] = $workspace->getGuid();
        }

        if (!is_null($workspaceModel)) {
            $details['workspaceModelId'] = $workspaceModel->getId();
            $details['workspaceModelName'] = $workspaceModel->getName();
        }
        $this->om->remove($course);
        $this->om->flush();
        $event = new LogCourseDeleteEvent($details);
        $this->eventDispatcher->dispatch('log', $event);
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
        $createdCursus = [];
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

        $this->om->startFlushSuite();

        foreach ($createdCursus as $cursus) {
            $event = new LogCursusCreateEvent($cursus);
            $this->eventDispatcher->dispatch('log', $event);
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

    public function registerUserToMultipleCursus(array $multipleCursus, User $user, $withWorkspace = true, $withCourse = false, $force = false)
    {
        $registrationDate = new \DateTime();

        $this->om->startFlushSuite();

        foreach ($multipleCursus as $cursus) {
            $cursusUser = null;
            if (!$force) {
                $cursusUser = $this->cursusUserRepo->findOneCursusUserByCursusAndUser(
                    $cursus,
                    $user
                );
            }

            if (is_null($cursusUser) || $force) {
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

    public function registerUsersToMultipleCursus(array $multipleCursus, array $users, $withWorkspace = true)
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
        $this->unregisterUsersFromCursus($cursus, [$user]);
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
        $toDelete = [];
        $coursesToUnregister = [];
        $root = $cursus->getRoot();
        $cursusRoot = $this->getOneCursusById($root);

        if ($cursus->isBlocking()) {
            $toDelete = $this->getCursusUsersFromCursusAndUsers(
                [$cursus],
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
            [] :
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

    public function registerGroupToMultipleCursus(array $multipleCursus, Group $group, $withWorkspace = true)
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
        $cursusGroupsToDelete = [];
        $cursusUsersToDelete = [];
        $coursesToUnregister = [];
        $root = $cursus->getRoot();
        $cursusRoot = $this->getOneCursusById($root);

        if ($cursus->isBlocking()) {
            $course = $cursus->getCourse();

            if (!is_null($course)) {
                $coursesToUnregister[] = $course;
            }
            $cursusUsersToDelete = $this->getCursusUsersFromCursusAndUsers([$cursus], $users);
            $cursusGroupsToDelete = $this->getCursusGroupsFromCursusAndGroups([$cursus], [$group]);
        } else {
            // Determines from which cursus descendants user has to be removed.
            $unlockedDescendants = $this->getUnlockedDescendants($cursus);
            // Current cursus is included
            $unlockedDescendants[] = $cursus;
            $cursusUsersToDelete = $this->getCursusUsersFromCursusAndUsers(
                $unlockedDescendants,
                $users
            );
            $removableGroupDescendants = $this->getCursusGroupsFromCursusAndGroups($unlockedDescendants, [$group]);

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
            [] :
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

    public function updateCursusParentAndOrder(Cursus $cursus, Cursus $parent = null, $cursusOrder = -1)
    {
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

    public function updateCursusOrderByParent($cursusOrder, Cursus $parent = null, $executeQuery = true)
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
        $hierarchy = [];
        $unlockedDescendants = [];

        foreach ($descendantsCursus as $descendant) {
            $parent = $descendant->getParent();

            if (!is_null($parent)) {
                $parentId = $parent->getId();

                if (!isset($hierarchy[$parentId])) {
                    $hierarchy[$parentId] = [];
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

    private function searchUnlockedDescendants(Cursus $cursus, array $hierarchy, array &$unlockedDescendants)
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
        $removableCursusUsers = [];
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
        $removableCursusGroups = [];
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
                if ($sessionUser->getUserType() === CourseSessionUser::LEARNER || $sessionUser->getUserType() === CourseSessionUser::PENDING_LEARNER) {
                    --$remaingPlace;
                }
            }
        }

        return $remaingPlace;
    }

    public function registerUsersToSession(CourseSession $session, array $users, $type, $cascadeEvent = false)
    {
        $results = ['status' => 'success', 'datas' => [], 'sessionUsers' => '[]'];
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
            $sessionUsers = [];
            $this->om->startFlushSuite();

            foreach ($users as $user) {
                $sessionUser = $this->sessionUserRepo->findOneSessionUserBySessionAndUserAndType($session, $user, $type);

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

                    if ($cascadeEvent) {
                        $this->registerPendingSessionEventUsers($user, $session);
                    }
                    $this->registerUserToAllAutomaticSessionEvent($user, $session);
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
                $results['datas'][] = [
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
                ];
            }
            $results['sessionUsers'] = $this->serializer->serialize(
                $sessionUsers,
                'json',
                SerializationContext::create()->setGroups(['api_user_min'])
            );
        }

        return $results;
    }

    public function registerUsersToSessions(array $sessions, array $users, $type = 0, $force = false)
    {
        $results = ['status' => 'success', 'datas' => []];

        if (intval($type) === CourseSessionUser::LEARNER) {
            foreach ($sessions as $session) {
                $course = $session->getCourse();
                $remainingPlaces = $this->getSessionRemainingPlace($session);

                if (!is_null($remainingPlaces) && ($remainingPlaces < count($users))) {
                    $results['status'] = 'failed';
                    $results['datas'][] = [
                        'sessionId' => $session->getId(),
                        'sessionName' => $session->getName(),
                        'courseId' => $course->getId(),
                        'courseTitle' => $course->getTitle(),
                        'courseCode' => $course->getCode(),
                        'remainingPlaces' => $remainingPlaces,
                    ];
                }
            }
        }

        if ($results['status'] === 'success') {
            $this->om->startFlushSuite();
            $registrationDate = new \DateTime();

            foreach ($sessions as $session) {
                foreach ($users as $user) {
                    $sessionUser = null;

                    if (!$force) {
                        $sessionUser = $this->sessionUserRepo->findOneSessionUserBySessionAndUserAndType(
                            $session,
                            $user,
                            $type
                        );
                    }

                    if (is_null($sessionUser) || $force) {
                        $sessionUser = new CourseSessionUser();
                        $sessionUser->setSession($session);
                        $sessionUser->setUser($user);
                        $sessionUser->setUserType($type);
                        $sessionUser->setRegistrationDate($registrationDate);
                        $this->om->persist($sessionUser);
                        $event = new LogCourseSessionUserRegistrationEvent($session, $user);
                        $this->eventDispatcher->dispatch('log', $event);
                        $this->registerUserToAllAutomaticSessionEvent($user, $session);
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

    public function registerGroupToSession(CourseSession $session, Group $group, $type = 0)
    {
        $users = $group->getUsers()->toArray();
        $results = ['status' => 'success', 'datas' => [], 'sessionUsers' => '[]', 'sessionGroup' => null];

        if (intval($type) === CourseSessionUser::LEARNER) {
            $course = $session->getCourse();
            $remainingPlaces = $this->getSessionRemainingPlace($session);

            if (!is_null($remainingPlaces) && ($remainingPlaces < count($users))) {
                $results['status'] = 'failed';
                $results['datas']['remainingPlaces'] = $remainingPlaces;
                $results['datas']['requiredPlaces'] = count($users);
                $results['datas']['sessionId'] = $session->getId();
                $results['datas']['sessionName'] = $session->getName();
                $results['datas']['courseId'] = $course->getId();
                $results['datas']['courseTitle'] = $course->getTitle();
                $results['datas']['courseCode'] = $course->getCode();
            }
        }

        if ($results['status'] === 'success') {
            $this->om->startFlushSuite();
            $sessionUsers = [];
            $registrationDate = new \DateTime();
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
                    $this->registerUserToAllAutomaticSessionEvent($user, $session);
                    $sessionUsers[] = $sessionUser;
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
            $this->om->endFlushSuite();
            $results['sessionGroup'] = $this->serializer->serialize(
                $sessionGroup,
                'json',
                SerializationContext::create()->setGroups(['api_group_min'])
            );
            $results['sessionUsers'] = $this->serializer->serialize(
                $sessionUsers,
                'json',
                SerializationContext::create()->setGroups(['api_user_min'])
            );
        }

        return $results;
    }

    public function registerGroupToSessions(array $sessions, Group $group, $type = 0)
    {
        $users = $group->getUsers()->toArray();
        $results = ['status' => 'success', 'datas' => []];

        if (intval($type) === CourseSessionUser::LEARNER) {
            foreach ($sessions as $session) {
                $course = $session->getCourse();
                $remainingPlaces = $this->getSessionRemainingPlace($session);

                if (!is_null($remainingPlaces) && ($remainingPlaces < count($users))) {
                    $results['status'] = 'failed';
                    $results['datas'][] = [
                        'sessionId' => $session->getId(),
                        'sessionName' => $session->getName(),
                        'courseId' => $course->getId(),
                        'courseTitle' => $course->getTitle(),
                        'courseCode' => $course->getCode(),
                        'remainingPlaces' => $remainingPlaces,
                    ];
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
        $serializedSessionUsers = $this->serializer->serialize(
            $sessionUsers,
            'json',
            SerializationContext::create()->setGroups(['api_cursus'])
        );
        $this->unregisterUsersFromSession($sessionUsers);
        $this->om->endFlushSuite();

        return $serializedSessionUsers;
    }

    public function deleteCourseSession(CourseSession $session, $withWorkspace = false)
    {
        $course = $session->getCourse();
        $workspace = $session->getWorkspace();
        $learnerRole = $session->getLearnerRole();
        $tutorRole = $session->getTutorRole();
        $details = [];
        $details['id'] = $session->getId();
        $details['name'] = $session->getName();
        $details['defaultSession'] = $session->isDefaultSession();
        $details['creationDate'] = $session->getCreationDate();
        $details['publicRegistration'] = $session->getPublicRegistration();
        $details['publicUnregistration'] = $session->getPublicUnregistration();
        $details['registrationValidation'] = $session->getRegistrationValidation();
        $details['startDate'] = $session->getStartDate();
        $details['endDate'] = $session->getEndDate();
        $details['extra'] = $session->getExtra();
        $details['userValidation'] = $session->getUserValidation();
        $details['organizationValidation'] = $session->getOrganizationValidation();
        $details['maxUsers'] = $session->getMaxUsers();
        $details['type'] = $session->getType();

        $details['courseId'] = $course->getId();
        $details['courseTitle'] = $course->getTitle();
        $details['courseCode'] = $course->getCode();

        if (!is_null($workspace)) {
            $details['workspaceId'] = $workspace->getId();
            $details['workspaceName'] = $workspace->getName();
            $details['workspaceCode'] = $workspace->getCode();
            $details['workspaceGuid'] = $workspace->getGuid();
        }

        if (!is_null($learnerRole)) {
            $details['learnerRoleId'] = $learnerRole->getId();
            $details['learnerRoleName'] = $learnerRole->getName();
            $details['learnerRoleKey'] = $learnerRole->getTranslationKey();
        }

        if (!is_null($tutorRole)) {
            $details['tutorRoleId'] = $tutorRole->getId();
            $details['tutorRoleName'] = $tutorRole->getName();
            $details['tutorRoleKey'] = $tutorRole->getTranslationKey();
        }
        $this->om->startFlushSuite();
        $workspace = $session->getWorkspace();
        $this->om->remove($session);

        if ($withWorkspace && !is_null($workspace)) {
            $this->om->remove($tutorRole);
            $this->om->remove($learnerRole);
            $this->workspaceManager->deleteWorkspace($workspace);
        }
        $event = new LogCourseSessionDeleteEvent($details);
        $this->eventDispatcher->dispatch('log', $event);
        $this->om->endFlushSuite();
    }

    public function createCourseSession(
        Course $course,
        $name = null,
        $description = null,
        array $cursus = [],
        $creationDate = null,
        $startDate = null,
        $endDate = null,
        $defaultSession = false,
        $publicRegistration = false,
        $publicUnregistration = false,
        $registrationValidation = false,
        $userValidation = false,
        $organizationValidation = false,
        $maxUsers = null,
        $type = 0,
        array $validators = [],
        $eventRegistrationType = CourseSession::REGISTRATION_AUTO,
        $displayOrder = 500,
        $color = null
    ) {
        if (is_null($creationDate)) {
            $creationDate = new \DateTime();
        }
        $session = new CourseSession();

        if ($name) {
            $session->setName($name);
        }

        foreach ($cursus as $c) {
            $session->addCursus($c);
        }
        $session->setDescription($description);
        $session->setCreationDate($creationDate);
        $session->setDefaultSession($defaultSession);
        $session->setPublicRegistration($publicRegistration);
        $session->setPublicUnregistration($publicUnregistration);
        $session->setRegistrationValidation($registrationValidation);
        $session->setUserValidation($userValidation);
        $session->setOrganizationValidation($organizationValidation);
        $session->setMaxUsers($maxUsers);
        $session->setType($type);
        $session->setEventRegistrationType($eventRegistrationType);
        $session->setDisplayOrder($displayOrder);
        $details = [];
        $details['color'] = $color;
        $total = $this->platformConfigHandler->hasParameter('cursus_session_default_total') ?
            $this->platformConfigHandler->getParameter('cursus_session_default_total') :
            null;
        $details['total'] = $total;
        $session->setDetails($details);

        if ($defaultSession) {
            $this->resetDefaultSessionByCourse($course);
        }

        if (is_null($startDate)) {
            $startDate = $creationDate;
        }
        $session->setStartDate($startDate);

        if (is_null($endDate)) {
            $endDate = clone $startDate;
            $endDate->add(new \DateInterval('P'.$course->getDefaultSessionDuration().'D'));
        }
        $session->setEndDate($endDate);

        foreach ($validators as $validator) {
            $session->addValidator($validator);
        }
        $this->createCourseSessionFromSession($session, $course);

        if ($course->getWithSessionEvent()) {
            $this->createSessionEvent($session);
        }

        return $session;
    }

    public function createCourseSessionFromSession(CourseSession $session, Course $course)
    {
        $session->setCourse($course);
        $workspace = $course->getWorkspace();

        if (is_null($workspace)) {
            $workspace = $this->generateWorkspace($course, $session);
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
        $event = new LogCourseSessionCreateEvent($session);
        $this->eventDispatcher->dispatch('log', $event);
        //the event will be listened by FormaLibreBulletinBundle (it adds some MatiereOptions)
        $this->clarolineDispatcher->dispatch('create_course_session', 'Claroline\CursusBundle\Event\CreateCourseSessionEvent', [$session]);
    }

    public function createSessionEvent(
        CourseSession $session,
        $name = null,
        $description = null,
        $startDate = null,
        $endDate = null,
        $location = null,
        $locationExtra = null,
        Resource $reservationResource = null,
        array $tutors = [],
        $registrationType = CourseSession::REGISTRATION_AUTO,
        $maxUsers = null
    ) {
        $eventName = is_null($name) ? $session->getName() : $name;
        $eventStartDate = is_null($startDate) ? $session->getStartDate() : $startDate;
        $eventEndDate = is_null($endDate) ? $session->getEndDate() : $endDate;

        $sessionEvent = new SessionEvent();
        $sessionEvent->setSession($session);
        $sessionEvent->setName($eventName);
        $sessionEvent->setDescription($description);
        $sessionEvent->setStartDate($eventStartDate);
        $sessionEvent->setEndDate($eventEndDate);
        $sessionEvent->setLocation($location);
        $sessionEvent->setLocationExtra($locationExtra);
        $sessionEvent->setLocationResource($reservationResource);
        $sessionEvent->setRegistrationType($registrationType);
        $sessionEvent->setMaxUsers($maxUsers);

        foreach ($tutors as $tutor) {
            $sessionEvent->addTutor($tutor);
        }
        $this->persistSessionEvent($sessionEvent);
        $event = new LogSessionEventCreateEvent($sessionEvent);
        $this->eventDispatcher->dispatch('log', $event);

        if ($sessionEvent->getRegistrationType() === CourseSession::REGISTRATION_AUTO) {
            $this->registerSessionUsersToSessionEvent($sessionEvent);
        }

        return $sessionEvent;
    }

    public function persistSessionEvent(SessionEvent $event)
    {
        $this->om->persist($event);
        $this->om->flush();
    }

    public function deleteSessionEvent(SessionEvent $sessionEvent)
    {
        $session = $sessionEvent->getSession();
        $course = $session->getCourse();
        $details = [];
        $details['id'] = $sessionEvent->getId();
        $details['name'] = $sessionEvent->getName();
        $details['startDate'] = $sessionEvent->getStartDate();
        $details['endDate'] = $sessionEvent->getEndDate();
        $details['sessionId'] = $session->getId();
        $details['sessionName'] = $session->getName();
        $details['courseId'] = $course->getId();
        $details['courseTitle'] = $course->getTitle();
        $details['courseCode'] = $course->getCode();
        $this->om->remove($sessionEvent);
        $this->om->flush();
        $event = new LogSessionEventDeleteEvent($details);
        $this->eventDispatcher->dispatch('log', $event);
    }

    public function createSessionEventComment(User $user, SessionEvent $sessionEvent, $content)
    {
        $comment = new SessionEventComment();
        $comment->setUser($user);
        $comment->setSessionEvent($sessionEvent);
        $comment->setContent($content);
        $comment->setCreationDate(new \DateTime());
        $this->persistSessionEventComment($comment);

        return $comment;
    }

    public function persistSessionEventComment(SessionEventComment $comment)
    {
        $this->om->persist($comment);
        $this->om->flush();
    }

    public function deleteSessionEventComment(SessionEventComment $comment)
    {
        $this->om->remove($comment);
        $this->om->flush();
    }

    public function repeatSessionEvent(SessionEvent $sessionEvent, $iteration, \DateTime $until = null, $duration = null)
    {
        $createdSessionEvents = [];
        $dateInterval = new \DateInterval('P1D');
        $session = $sessionEvent->getSession();
        $description = $sessionEvent->getDescription();
        $location = $sessionEvent->getLocation();
        $locationResource = $sessionEvent->getLocationResource();
        $name = $sessionEvent->getName();
        $eventStartDate = $sessionEvent->getStartDate();
        $endDate = $sessionEvent->getEndDate();
        $tutors = $sessionEvent->getTutors();
        $index = 1;
        $year = intval($eventStartDate->format('Y'));
        $month = intval($eventStartDate->format('m'));
        $day = intval($eventStartDate->format('d'));
        $hour = intval($eventStartDate->format('H'));
        $minute = intval($eventStartDate->format('i'));
        $startDate = new \DateTime();
        $startDate->setTimezone(new \DateTimeZone('GMT'));
        $startDate->setDate($year, $month, $day);
        $startDate->setTime($hour, $minute);

        if (is_null($until) && !is_null($duration) && $duration > 0) {
            $until = clone $startDate;
            $daysToAdd = 7 * $duration;
            $until->add(new \DateInterval('P'.$daysToAdd.'D'));
        }
        if (!is_null($until)) {
            $untilYear = intval($until->format('Y'));
            $untilMonth = intval($until->format('m'));
            $untilDay = intval($until->format('d'));
            $formattedUntil = new \DateTime();
            $formattedUntil->setTimezone(new \DateTimeZone('GMT'));
            $formattedUntil->setDate($untilYear, $untilMonth, $untilDay);
            $formattedUntil->setTime(23, 59, 59);
            $this->om->startFlushSuite();

            for ($startDate->add($dateInterval); $startDate < $formattedUntil; $startDate->add($dateInterval)) {
                $eventStartDate->add($dateInterval);
                $endDate->add($dateInterval);
                $day = $startDate->format('l');

                if ($iteration[$day]) {
                    $newStartDate = clone  $eventStartDate;
                    $newEndDate = clone  $endDate;
                    $newSessionEvent = new SessionEvent();
                    $newSessionEvent->setSession($session);
                    $newSessionEvent->setDescription($description);
                    $newSessionEvent->setLocation($location);
                    $newSessionEvent->setLocationResource($locationResource);
                    $newSessionEvent->setStartDate($newStartDate);
                    $newSessionEvent->setEndDate($newEndDate);
                    $newSessionEvent->setName($name." [$index]");

                    foreach ($tutors as $tutor) {
                        $newSessionEvent->addTutor($tutor);
                    }
                    ++$index;
                    $this->persistSessionEvent($newSessionEvent);
                    $createdSessionEvents[] = $newSessionEvent;

                    if ($index % 300 === 0) {
                        $this->om->forceFlush();
                    }
                }
            }
            $this->om->endFlushSuite();
        }

        return $createdSessionEvents;
    }

    public function resetDefaultSessionByCourse(Course $course, CourseSession $session = null)
    {
        $defaultSessions = $this->getDefaultSessionsByCourse($course);

        $this->om->startFlushSuite();

        foreach ($defaultSessions as $defaultSession) {
            if ($defaultSession !== $session) {
                $defaultSession->setDefaultSession(false);
            }
        }
        $this->om->endFlushSuite();
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

    public function generateWorkspace(Course $course, CourseSession $session)
    {
        $user = $this->tokenStorage->getToken()->getUser();
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
            $template = new File($this->defaultTemplate);
            $workspace = new Workspace();
            $workspace->setCreator($user);
            $workspace->setName($name);
            $workspace->setCode($code);
            $workspace->setDisplayable($displayable);
            $workspace->setSelfRegistration($selfRegistration);
            $workspace->setSelfUnregistration($selfUnregistration);
            $workspace->setRegistrationValidation($registrationValidation);
            $workspace->setDescription($description);
            $workspace = $this->workspaceManager->create($workspace, $template);
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
        return $this->contentManager->getContent(['type' => 'claro_cursusbundle_mail_confirmation']);
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
        $existingCodes = [];

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
        $queueDatas = [
            'id' => $queue->getId(),
            'courseId' => $course->getId(),
            'sessionId' => $session->getId(),
            'userId' => $user->getId(),
            'username' => $user->getUsername(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
        ];
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
        $title = $this->translator->trans('course_registration_request_confirmation', [], 'cursus');
        $link = $this->router->generate(
            'claro_cursus_course_registration_queue_user_validate',
            ['queue' => $queue->getId()],
            true
        );
        $content = $this->templating->render(
            'ClarolineCursusBundle:CursusRegistration:courseRequestConfirmationMail.html.twig',
            ['queue' => $queue, 'link' => $link]
        );

        $this->mailManager->send($title, $content, [$user]);
    }

    public function sendSessionQueueRequestConfirmationMail(CourseSessionRegistrationQueue $queue)
    {
        $user = $queue->getUser();
        $title = $this->translator->trans('session_registration_request_confirmation', [], 'cursus');
        $link = $this->router->generate(
            'claro_cursus_session_registration_queue_user_validate',
            ['queue' => $queue->getId()],
            true
        );
        $content = $this->templating->render(
            'ClarolineCursusBundle:CursusRegistration:sessionRequestConfirmationMail.html.twig',
            ['queue' => $queue, 'link' => $link]
        );

        $this->mailManager->send($title, $content, [$user]);
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
        $queueDatas = [
            'id' => $queue->getId(),
            'courseId' => $course->getId(),
            'userId' => $user->getId(),
            'username' => $user->getUsername(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
        ];
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

    public function transferQueuedUserToSession(CourseRegistrationQueue $queue, CourseSession $session)
    {
        $this->checkCursusToolRegistrationAccess();
        $user = $queue->getUser();
        $this->om->startFlushSuite();
        $results = $this->registerUsersToSession($session, [$user], 0);

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
        $config = $this->coursesWidgetConfigRepo->findOneBy(['widgetInstance' => $widgetInstance->getId()]);

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
        $results = ['status' => 'success', 'datas' => []];
        $sessions = $this->getDefaultPublicSessionsByCourse($course);

        if (count($sessions) > 0) {
            $session = $sessions[0];

            if ($session->hasValidation()) {
                $this->addUserToSessionQueue($user, $session);
            } else {
                $results = $this->registerUsersToSession($session, [$user], 0);
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
        $pathArch = $this->platformConfigHandler->getParamter('tmp_dir').DIRECTORY_SEPARATOR.$this->ut->generateGuid().'.zip';
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
            SerializationContext::create()->setGroups(['api_cursus'])
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
            SerializationContext::create()->setGroups(['api_cursus'])
        );
        $archive->addFromString('cursus.json', $cursusJson);

        $courses = [];

        foreach ($cursusList as $cursus) {
            $course = $cursus->getCourse();

            if (!is_null($course)) {
                $courses[$course->getId()] = $course;
            }
        }
        $this->zipCourses($courses, $archive);
    }

    public function importCourses(array $datas, $withIndex = true)
    {
        $courses = [];
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

            if ($withIndex) {
                $courses[$data['id']] = $course;
            } else {
                $courses[] = $course;
            }
            ++$i;

            if ($i % 50 === 0) {
                $this->om->forceFlush();
            }
        }
        $this->om->endFlushSuite();

        return $courses;
    }

    public function importCursus(array $datas, array $courses = [])
    {
        $roots = [];
        $cursusChildren = [];

        foreach ($datas as $cursus) {
            $root = $cursus['root'];
            $lvl = $cursus['lvl'];
            $id = $cursus['id'];

            if ($lvl === 0) {
                $roots[$id] = [
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
                ];
            } else {
                $parentId = $cursus['parentId'];

                if (!isset($cursusChildren[$parentId])) {
                    $cursusChildren[$parentId] = [];
                }
                $cursusChildren[$parentId][$id] = [
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
                ];
            }
        }

        return $this->importRootCursus($roots, $cursusChildren, $courses);
    }

    private function getAllCoursesCodes()
    {
        $codes = [];
        $courses = $this->getAllCourses('', 'id', 'ASC', false);

        foreach ($courses as $course) {
            $codes[$course->getCode()] = true;
        }

        return $codes;
    }

    private function getAllCursusCodes()
    {
        $codes = [];
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
        $createdCursus = [];
        $rootCursus = [];

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
            $rootCursus[] = $cursus;
        }
        $this->om->endFlushSuite();

        return $rootCursus;
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
                    $createdCursus[$parent['id']]->addChild($cursus);
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
        $sessionsDatas = [];
        $sessions = empty($search) ?
            $this->courseSessionRepo->findAllUnclosedSessions() :
            $this->courseSessionRepo->findSearchedlUnclosedSessions($search);

        foreach ($sessions as $session) {
            $course = $session->getCourse();
            $courseCode = $course->getCode();

            if (!isset($sessionsDatas[$courseCode])) {
                $sessionsDatas[$courseCode] = [];
                $sessionsDatas[$courseCode]['course'] = $course;
                $sessionsDatas[$courseCode]['sessions'] = [];
            }
            $sessionsDatas[$courseCode]['sessions'][] = $session;
        }

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($sessionsDatas, $page, $max) :
            $sessionsDatas;
    }

    public function getCursusDatasForCursusRegistration(Cursus $cursus)
    {
        $hierarchy = [];
        $lockedHierarchy = [];
        $unlockedCursus = [];
        $hierarchyArray = [];
        $unlockedArray = [];
        $allRelatedCursus = $this->getRelatedHierarchyByCursus($cursus);

        foreach ($allRelatedCursus as $oneCursus) {
            $parent = $oneCursus->getParent();
            $lockedHierarchy[$oneCursus->getId()] = 'blocked';

            if (is_null($parent)) {
                if (!isset($hierarchy['root'])) {
                    $hierarchy['root'] = [];
                }
                $hierarchy['root'][] = $oneCursus;
            } else {
                $parentId = $parent->getId();

                if (!isset($hierarchy[$parentId])) {
                    $hierarchy[$parentId] = [];
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
            $hierarchyArray[$key] = [];

            foreach ($values as $value) {
                $course = $value->getCourse();
                $valueEntry = [
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
                ];
                $hierarchyArray[$key][] = $valueEntry;
            }
        }

        foreach ($unlockedCursus as $unlocked) {
            $unlockedArray[] = $unlocked->getId();
        }

        return [
            'hierarchy' => $hierarchyArray,
            'lockedHierarchy' => $lockedHierarchy,
            'unlockedCursus' => $unlockedArray,
        ];
    }

    public function getCursusUsersForCursusRegistration(Cursus $cursus)
    {
        $this->checkCursusToolRegistrationAccess();
        $usersArray = [];
        $cursusUsers = $this->getCursusUsersByCursus($cursus);

        foreach ($cursusUsers as $cursusUser) {
            $user = $cursusUser->getUser();
            $userEntry = [
                'id' => $cursusUser->getId(),
                'userType' => $cursusUser->getUserType(),
                'registrationDate' => $cursusUser->getRegistrationDate(),
                'userId' => $user->getId(),
                'username' => $user->getUsername(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
            ];
            $usersArray[] = $userEntry;
        }

        return $usersArray;
    }

    public function getCursusGroupsForCursusRegistration(Cursus $cursus)
    {
        $this->checkCursusToolRegistrationAccess();
        $groupsArray = [];
        $cursusGroups = $this->getCursusGroupsByCursus($cursus);

        foreach ($cursusGroups as $cursusGroup) {
            $group = $cursusGroup->getGroup();
            $groupEntry = [
                'id' => $cursusGroup->getId(),
                'groupType' => $cursusGroup->getGroupType(),
                'registrationDate' => $cursusGroup->getRegistrationDate(),
                'groupId' => $group->getId(),
                'groupName' => $group->getName(),
            ];
            $groupsArray[] = $groupEntry;
        }

        return $groupsArray;
    }

    public function getDatasForSearchedCursusRegistration($search)
    {
        $searchedCursusList = [];
        $searchedCursus = $this->getAllCursus(
            $search,
            'title',
            'ASC'
        );
        $rootIds = [];

        foreach ($searchedCursus as $cursus) {
            $course = $cursus->getCourse();
            $root = $cursus->getRoot();

            $searchedCursusList[] = [
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
            ];

            if (!in_array($root, $rootIds)) {
                $rootIds[] = $root;
            }
        }
        $cursusRoots = $this->getCursusByIds($rootIds);
        $roots = [];

        foreach ($cursusRoots as $cursusRoot) {
            $roots[$cursusRoot->getId()] = [
                'id' => $cursusRoot->getId(),
                'code' => $cursusRoot->getCode(),
                'title' => $cursusRoot->getTitle(),
            ];
        }

        return ['searchedCursus' => $searchedCursusList, 'roots' => $roots];
    }

    public function getDatasForCursusHierarchy(Cursus $cursus)
    {
        $hierarchy = [];
        $allCursus = $this->getRelatedHierarchyByCursus($cursus);

        foreach ($allCursus as $oneCursus) {
            $parent = $oneCursus->getParent();
            $course = $oneCursus->getCourse();

            if (is_null($parent)) {
                if (!isset($hierarchy['root'])) {
                    $hierarchy['root'] = [];
                }
                $hierarchy['root'][] = [
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
                ];
            } else {
                $parentId = $parent->getId();

                if (!isset($hierarchy[$parentId])) {
                    $hierarchy[$parentId] = [];
                }
                $hierarchy[$parentId][] = [
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
                ];
            }
        }

        return $hierarchy;
    }

    public function getCursusFromCursusIdsTxt($cursusIdsTxt)
    {
        $cursusIds = [];
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
        $sessionIds = [];
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
        $userIds = [];
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
        $cursusGroupsIds = [];
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
        $sessionsInfos = [];
        $courses = [];

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
                $sessionsInfos[$courseId] = [];
                $sessionsInfos[$courseId]['courseId'] = $course->getId();
                $sessionsInfos[$courseId]['courseTitle'] = $course->getTitle();
                $sessionsInfos[$courseId]['courseCode'] = $course->getCode();
                $sessionsInfos[$courseId]['sessions'] = [];
            }
        }

        foreach ($sessions as $session) {
            if ($session->getSessionStatus() !== 2) {
                $courseId = $session->getCourse()->getId();

                $sessionsInfos[$courseId]['sessions'][] = [
                    'sessionId' => $session->getId(),
                    'sessionName' => $session->getName(),
                    'sessionStatus' => $session->getSessionStatus(),
                ];
            }
        }

        return $sessionsInfos;
    }

    public function registerGroupToCursusAndSessions(Group $group, array $multipleCursus, array $sessions)
    {
        $this->checkCursusToolRegistrationAccess();
        $coursesWithSession = [];
        $sessionsToCreate = [];
        $root = 0;
        $cursusRoot = null;
        $registrationDate = new \DateTime();
        $configStartDate = $this->platformConfigHandler->getParameter('cursusbundle_default_session_start_date');
        $configEndDate = $this->platformConfigHandler->getParameter('cursusbundle_default_session_end_date');
        $startDate = empty($configStartDate) ? null : new \DateTime($configStartDate);
        $endDate = empty($configEndDate) ? null : new \DateTime($configEndDate);

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
                $sessionName,
                null,
                [$cursusRoot],
                $registrationDate,
                $startDate,
                $endDate,
                false,
                $course->getPublicRegistration(),
                $course->getPublicUnregistration(),
                $course->getRegistrationValidation(),
                $course->getUserValidation(),
                $course->getOrganizationValidation(),
                $course->getMaxUsers(),
                0,
                $course->getValidators(),
                CourseSession::REGISTRATION_AUTO,
                $course->getDisplayOrder()
            );
            $sessions[] = $session;
        }
        $results = $this->registerGroupToSessions($sessions, $group);

        if ($results['status'] === 'success') {
            $this->registerGroupToMultipleCursus($multipleCursus, $group);
        }

        return $results;
    }

    public function registerUsersToCursusAndSessions(array $users, array $multipleCursus, array $sessions)
    {
        $this->checkCursusToolRegistrationAccess();
        $coursesWithSession = [];
        $sessionsToCreate = [];
        $root = 0;
        $cursusRoot = null;
        $registrationDate = new \DateTime();
        $configStartDate = $this->platformConfigHandler->getParameter('cursusbundle_default_session_start_date');
        $configEndDate = $this->platformConfigHandler->getParameter('cursusbundle_default_session_end_date');
        $startDate = empty($configStartDate) ? null : new \DateTime($configStartDate);
        $endDate = empty($configEndDate) ? null : new \DateTime($configEndDate);

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
                $sessionName,
                null,
                [$cursusRoot],
                $registrationDate,
                $startDate,
                $endDate,
                false,
                $course->getPublicRegistration(),
                $course->getPublicUnregistration(),
                $course->getRegistrationValidation(),
                $course->getUserValidation(),
                $course->getOrganizationValidation(),
                $course->getMaxUsers(),
                0,
                $course->getValidators(),
                CourseSession::REGISTRATION_AUTO,
                $course->getDisplayOrder()
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
        $roles = [];
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
        $datas = [];
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
        $datas = [];
        $courseIds = [];

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
            $courseDatas = [
                'id' => $course->getId(),
                'code' => $course->getCode(),
                'title' => $course->getTitle(),
                'description' => $course->getDescription(),
                'publicRegistration' => $course->getPublicRegistration(),
                'publicUnregistration' => $course->getPublicUnregistration(),
                'registrationValidation' => $course->getRegistrationValidation(),
                'userValidation' => $course->getUserValidation(),
                'maxUsers' => $course->getMaxUsers(),
                'validators' => [],
            ];
            $validators = $course->getValidators();

            foreach ($validators as $validator) {
                $validatorsDatas = [
                    'id' => $validator->getId(),
                    'username' => $validator->getUsername(),
                    'firstName' => $validator->getFirstName(),
                    'lastName' => $validator->getLastName(),
                ];
                $courseDatas['validators'][] = $validatorsDatas;
            }
            $datas[] = $courseDatas;
        }

        return $datas;
    }

    public function getCoursesQueuesDatasFromQueues(array $queues)
    {
        $datas = [];

        foreach ($queues as $queue) {
            $user = $queue->getUser();
            $course = $queue->getCourse();
            $courseId = $course->getId();

            if (!isset($datas[$courseId])) {
                $datas[$courseId] = [];
            }

            $datas[$courseId][] = [
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
            ];
        }

        return $datas;
    }

    public function getSessionsQueuesDatasFromQueues(array $queues)
    {
        $datas = [];

        foreach ($queues as $queue) {
            $user = $queue->getUser();
            $session = $queue->getSession();
            $course = $session->getCourse();
            $courseId = $course->getId();

            if (!isset($datas[$courseId])) {
                $datas[$courseId] = [];
            }

            $datas[$courseId][] = [
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
            ];
        }

        return $datas;
    }

    public function mergeCourseQueues($validatorQueues, $orgaQueues, $simpleQueues)
    {
        $courseQueues = [];

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
        $sessionQueues = [];

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
        $datas = [];
        $queuesDatas = [];

        foreach ($validatorQueues as $queue) {
            $queueId = $queue->getId();
            $user = $queue->getUser();
            $course = $queue->getCourse();

            $queuesDatas[$queueId] = [
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
            ];
        }

        foreach ($orgaQueues as $queue) {
            $queueId = $queue->getId();

            if (isset($queuesDatas[$queueId])) {
                $queuesDatas[$queueId]['rights'] += CourseRegistrationQueue::WAITING_ORGANIZATION;
            } else {
                $user = $queue->getUser();
                $course = $queue->getCourse();

                $queuesDatas[$queueId] = [
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
                ];
            }
        }

        foreach ($simpleQueues as $queue) {
            $queueId = $queue->getId();

            if (isset($queuesDatas[$queueId])) {
                $queuesDatas[$queueId]['rights'] += CourseRegistrationQueue::WAITING;
            } else {
                $user = $queue->getUser();
                $course = $queue->getCourse();

                $queuesDatas[$queueId] = [
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
                ];
            }
        }

        foreach ($queuesDatas as $queueData) {
            $courseId = $queueData['courseId'];

            if (!isset($datas[$courseId])) {
                $datas[$courseId] = [];
            }
            $datas[$courseId][] = $queueData;
        }

        return $datas;
    }

    public function computeSessionsQueuesDatas($validatorQueues, $orgaQueues, $simpleQueues)
    {
        $datas = [];
        $queuesDatas = [];

        foreach ($validatorQueues as $queue) {
            $queueId = $queue->getId();
            $user = $queue->getUser();
            $session = $queue->getSession();
            $course = $session->getCourse();

            $queuesDatas[$queueId] = [
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
            ];
        }

        foreach ($orgaQueues as $queue) {
            $queueId = $queue->getId();

            if (isset($queuesDatas[$queueId])) {
                $queuesDatas[$queueId]['rights'] += CourseRegistrationQueue::WAITING_ORGANIZATION;
            } else {
                $user = $queue->getUser();
                $session = $queue->getSession();
                $course = $session->getCourse();

                $queuesDatas[$queueId] = [
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
                ];
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

                $queuesDatas[$queueId] = [
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
                ];
            }
        }

        foreach ($queuesDatas as $queueData) {
            $courseId = $queueData['courseId'];

            if (!isset($datas[$courseId])) {
                $datas[$courseId] = [];
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
        $results = ['status' => 'success', 'datas' => []];
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
            $results = $this->registerUsersToSession($session, [$user], CourseSessionUser::LEARNER);

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
        $queueDatas = [
            'type' => 'none',
            'id' => $queue->getId(),
            'courseId' => $course->getId(),
            'applicationDate' => $queue->getApplicationDate(),
            'userId' => $user->getId(),
            'username' => $user->getUsername(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'queueStatus' => $queue->getStatus(),
        ];
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
        $queueDatas = [
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
        ];
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
                $results = $this->registerUsersToSession($session, [$user], 0);

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

    public function createDocumentModel($name, $content, $type)
    {
        $documentModel = new DocumentModel();
        $documentModel->setName($name);
        $documentModel->setContent($content);
        $documentModel->setDocumentType($type);
        $this->persistDocumentModel($documentModel);

        return $documentModel;
    }

    public function persistDocumentModel(DocumentModel $documentModel)
    {
        $this->om->persist($documentModel);
        $this->om->flush();
    }

    public function deleteDocumentModel(DocumentModel $documentModel)
    {
        $this->om->remove($documentModel);
        $this->om->flush();
    }

    public function getClosedSessionsByUser(User $user)
    {
        $sessions = [];
        $sessionUsers = $this->sessionUserRepo->findClosedSessionUsersByUser($user);

        foreach ($sessionUsers as $sessionUser) {
            $sessions[] = $sessionUser->getSession();
        }

        return $sessions;
    }

    public function sendMessageToSession(User $user, CourseSession $session, $object, $content, $internal = true, $external = true)
    {
        $receivers = $this->getUsersBySessionAndType($session, CourseSessionUser::LEARNER);

        if ($internal) {
            $message = $this->messageManager->create($content, $object, $receivers, $user);
            $this->messageManager->send($message, true, false);
        }
        if ($external) {
            $this->mailManager->send($object, $content, $receivers);
        }
    }

    public function convertKeysForSession(CourseSession $session, $content, $withEventsList = true)
    {
        $course = $session->getCourse();
        $events = $session->getEvents();
        $eventsList = '';
        $sessionTrainers = $this->getUsersBySessionAndType($session, CourseSessionUser::TEACHER);
        $sessionTrainersHtml = '';

        if (count($sessionTrainers) > 0) {
            $sessionTrainersHtml = '<ul>';

            foreach ($sessionTrainers as $trainer) {
                $sessionTrainersHtml .= '<li>'.$trainer->getFirstName().' '.$trainer->getLastName().'</li>';
            }
            $sessionTrainersHtml .= '</ul>';
        }
        if ($withEventsList && count($events) > 0) {
            $eventsList = '<ul>';

            foreach ($events as $event) {
                $eventsList .= '<li>'.$event->getName().' ['.$event->getStartDate()->format('d/m/Y H:i').
                    ' -> '.$event->getEndDate()->format('d/m/Y H:i').']';
                $location = $event->getLocation();

                if (!is_null($location)) {
                    $locationHtml = '<br>'.$location->getStreet().', '.$location->getStreetNumber();
                    $locationHtml .= $location->getBoxNumber() ? ' ('.$location->getBoxNumber().')' : '';
                    $locationHtml .= '<br>'.$location->getPc().' '.$location->getTown().'<br>'.$location->getCountry();
                    $locationHtml .= $location->getPhone() ? '<br>'.$location->getPhone() : '';
                    $eventsList .= $locationHtml;
                }
                $eventsList .= $event->getLocationExtra();
            }
            $eventsList .= '</ul>';
        }
        $now = new \DateTime();
        $keys = [
            '%date%',
            '%course_title%',
            '%course_code%',
            '%course_description%',
            '%session_name%',
            '%session_description%',
            '%session_start%',
            '%session_end%',
            '%session_trainers%',
        ];
        $values = [
            $now->format('d/m/Y'),
            $course->getTitle(),
            $course->getCode(),
            $course->getDescription(),
            $session->getName(),
            $session->getDescription(),
            $session->getStartDate()->format('d/m/Y'),
            $session->getEndDate()->format('d/m/Y'),
            $sessionTrainersHtml,
        ];

        if ($withEventsList) {
            $keys[] = '%events_list%';
            $values[] = $eventsList;
        }

        return str_replace($keys, $values, $content);
    }

    public function convertKeysForSessionEvent(SessionEvent $event, $content)
    {
        $session = $event->getSession();
        $course = $session->getCourse();
        $location = $event->getLocation();
        $eventTrainers = $event->getTutors();
        $sessionTrainers = $this->getUsersBySessionAndType($session, CourseSessionUser::TEACHER);
        $sessionTrainersHtml = '';
        $locationHtml = '';
        $eventTrainersHtml = '';

        if (!is_null($location)) {
            $locationHtml = $location->getStreet().', '.$location->getStreetNumber();
            $locationHtml .= $location->getBoxNumber() ? ' ('.$location->getBoxNumber().')' : '';
            $locationHtml .= '<br>'.$location->getPc().' '.$location->getTown().'<br>'.$location->getCountry();
            $locationHtml .= $location->getPhone() ? '<br>'.$location->getPhone() : '';
        }

        if (count($sessionTrainers) > 0) {
            $sessionTrainersHtml = '<ul>';

            foreach ($sessionTrainers as $trainer) {
                $sessionTrainersHtml .= '<li>'.$trainer->getFirstName().' '.$trainer->getLastName().'</li>';
            }
            $sessionTrainersHtml .= '</ul>';
        }
        if (count($eventTrainers) > 0) {
            $eventTrainersHtml = '<ul>';

            foreach ($eventTrainers as $trainer) {
                $eventTrainersHtml .= '<li>'.$trainer->getFirstName().' '.$trainer->getLastName().'</li>';
            }
            $eventTrainersHtml .= '</ul>';
        }

        $now = new \DateTime();
        $keys = [
            '%date%',
            '%course_title%',
            '%course_code%',
            '%course_description%',
            '%session_name%',
            '%session_description%',
            '%session_start%',
            '%session_end%',
            '%session_trainers%',
            '%event_name%',
            '%event_description%',
            '%event_start%',
            '%event_end%',
            '%event_location%',
            '%event_location_extra%',
            '%event_trainers%',
        ];
        $values = [
            $now->format('d/m/Y'),
            $course->getTitle(),
            $course->getCode(),
            $course->getDescription(),
            $session->getName(),
            $session->getDescription(),
            $session->getStartDate()->format('d/m/Y'),
            $session->getEndDate()->format('d/m/Y'),
            $sessionTrainersHtml,
            $event->getName(),
            $event->getDescription(),
            $event->getStartDate()->format('d/m/Y H:i'),
            $event->getEndDate()->format('d/m/Y H:i'),
            $locationHtml,
            $event->getLocationExtra(),
            $eventTrainersHtml,
        ];

        return str_replace($keys, $values, $content);
    }

    public function generateDocumentFromModel(DocumentModel $documentModel, $sourceId)
    {
        $type = $documentModel->getDocumentType();
        $content = $documentModel->getContent();

        switch ($type) {
            case DocumentModel::SESSION_INVITATION:
                $session = $this->courseSessionRepo->findOneById($sourceId);
                $users = $this->getUsersBySessionAndType($session, CourseSessionUser::LEARNER);
                $title = $this->translator->trans('session_invitation', [], 'cursus');
                $body = $this->convertKeysForSession($session, $content);
                $this->sendInvitation($title, $users, $body);
                break;
            case DocumentModel::SESSION_EVENT_INVITATION:
                $sessionEvent = $this->sessionEventRepo->findOneById($sourceId);
                $session = $sessionEvent->getSession();
                $users = $this->getUsersBySessionAndType($session, CourseSessionUser::LEARNER);
                $title = $this->translator->trans('session_event_invitation', [], 'cursus');
                $body = $this->convertKeysForSessionEvent($sessionEvent, $content);
                $this->sendInvitation($title, $users, $body);
                break;
            case DocumentModel::SESSION_CERTIFICATE:
                $session = $this->courseSessionRepo->findOneById($sourceId);
                $users = $this->getUsersBySessionAndType($session, CourseSessionUser::LEARNER);
                $body = $this->convertKeysForSession($session, $content, false);
                $this->generateCertificatesForUsers($users, $body, $session);
                break;
        }
    }

    public function generateCertificatesForUsers(array $users, $content, CourseSession $session)
    {
        $creator = $this->container->get('security.token_storage')->getToken()->getUser();
        $data = [];

        foreach ($users as $user) {
            $name = $session->getName().'-'.$user->getUsername();
            $replacedContent = str_replace('%first_name%', $user->getFirstName(), $content);
            $replacedContent = str_replace('%last_name%', $user->getLastName(), $replacedContent);
            $eventsList = '';
            $events = $this->getSessionEventsBySessionAndUserAndRegistrationStatus($session, $user, SessionEventUser::REGISTERED);

            if (count($events) > 0) {
                $eventsList = '<ul>';

                foreach ($events as $event) {
                    $eventsList .= '<li>'.$event->getName().' ['.$event->getStartDate()->format('d/m/Y H:i').
                        ' -> '.$event->getEndDate()->format('d/m/Y H:i').']';
                    $location = $event->getLocation();

                    if (!is_null($location)) {
                        $locationHtml = '<br>'.$location->getStreet().', '.$location->getStreetNumber();
                        $locationHtml .= $location->getBoxNumber() ? ' ('.$location->getBoxNumber().')' : '';
                        $locationHtml .= '<br>'.$location->getPc().' '.$location->getTown().'<br>'.$location->getCountry();
                        $locationHtml .= $location->getPhone() ? '<br>'.$location->getPhone() : '';
                        $eventsList .= $locationHtml;
                    }
                    $eventsList .= $event->getLocationExtra();
                }
                $eventsList .= '</ul>';
            }
            $replacedContent = str_replace('%events_list%', $eventsList, $replacedContent);
            $pdf = $this->pdfManager->create($replacedContent, $name, $creator, 'session_certificate');
            $title = $this->translator->trans('new_certificate_email_title', [], 'cursus');
            $link = $this->templating->render('ClarolineCursusBundle:Mail:certificate.html.twig', ['pdf' => $pdf, 'session' => $session]);
            $this->mailManager->send($title, $link, [$user]);
            $data[] = ['user' => $user, 'pdf' => $pdf];
        }

        $title = $this->translator->trans('new_certificates_email_title', [], 'platform');
        $adminContent = $this->templating->render('ClarolineCursusBundle:Mail:certificates.html.twig', ['data' => $data]);
        $this->mailManager->send($title, $adminContent, [$creator]);
    }

    public function sendInvitation($title, array $users, $content)
    {
        foreach ($users as $user) {
            $body = str_replace(['%first_name%', '%last_name%'], [$user->getFirstName(), $user->getLastName()], $content);
            $this->mailManager->send($title, $body, [$user]);
        }
    }

    public function getUnregisteredUsersBySessionEvent(SessionEvent $sessionEvent)
    {
        $users = [];
        $registeredUserIds = [];
        $session = $sessionEvent->getSession();
        $eventUsers = $sessionEvent->getSessionEventUsers();
        $sessionLearners = $this->getUsersBySessionAndType($session);

        foreach ($eventUsers as $eventUser) {
            $registeredUserIds[] = $eventUser->getUser()->getId();
        }
        foreach ($sessionLearners as $learner) {
            if (!in_array($learner->getId(), $registeredUserIds)) {
                $users[] = $learner;
            }
        }

        return $users;
    }

    public function registerUsersToSessionEvent(SessionEvent $sessionEvent, array $users)
    {
        $results = ['status' => 'success', 'datas' => [], 'sessionEventUsers' => []];
        $session = $sessionEvent->getSession();
        $course = $session->getCourse();
        $registrationDate = new \DateTime();
        $remainingPlaces = $this->getSessionEventRemainingPlaces($sessionEvent);

        if (!is_null($remainingPlaces) && ($remainingPlaces < count($users))) {
            $results['status'] = 'failed';
            $results['datas']['remainingPlaces'] = $remainingPlaces;
            $results['datas']['requiredPlaces'] = count($users);
            $results['datas']['sessionEventId'] = $sessionEvent->getId();
            $results['datas']['sessionEventName'] = $sessionEvent->getName();
            $results['datas']['sessionId'] = $session->getId();
            $results['datas']['sessionName'] = $session->getName();
            $results['datas']['courseId'] = $course->getId();
            $results['datas']['courseTitle'] = $course->getTitle();
            $results['datas']['courseCode'] = $course->getCode();
        } else {
            $sessionEventUsers = [];
            $this->om->startFlushSuite();

            foreach ($users as $user) {
                $sessionEventUser = $this->sessionEventUserRepo->findOneBy([
                    'sessionEvent' => $sessionEvent,
                    'user' => $user,
                    'registrationStatus' => SessionEventUser::REGISTERED,
                ]);

                if (is_null($sessionEventUser)) {
                    $sessionEventUser = new SessionEventUser();
                    $sessionEventUser->setSessionEvent($sessionEvent);
                    $sessionEventUser->setUser($user);
                    $sessionEventUser->setRegistrationStatus(SessionEventUser::REGISTERED);
                    $sessionEventUser->setRegistrationDate($registrationDate);
                    $this->om->persist($sessionEventUser);
                    $sessionEventUsers[] = $sessionEventUser;
                    $event = new LogSessionEventUserRegistrationEvent($sessionEvent, $user);
                    $this->eventDispatcher->dispatch('log', $event);
                }
            }
            $this->om->endFlushSuite();

            foreach ($sessionEventUsers as $seu) {
                $user = $seu->getUser();
                $session = $sessionEvent->getSession();
                $course = $session->getCourse();
                $results['datas'] = [
                    'id' => $seu->getId(),
                    'user_id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'user_first_name' => $user->getFirstName(),
                    'user_last_name' => $user->getLastName(),
                    'sessionEventId' => $sessionEvent->getId(),
                    'sessionEventName' => $sessionEvent->getName(),
                    'sessionId' => $session->getId(),
                    'sessionName' => $session->getName(),
                    'courseId' => $course->getId(),
                    'courseTitle' => $course->getTitle(),
                    'courseCode' => $course->getCode(),
                ];
            }
            $results['sessionEventUsers'] = $this->serializer->serialize(
                $sessionEventUsers,
                'json',
                SerializationContext::create()->setGroups(['api_user_min'])
            );
        }

        return $results;
    }

    public function unregisterUsersFromSessionEvent(array $sessionEventUsers)
    {
        $this->om->startFlushSuite();

        foreach ($sessionEventUsers as $sessionEventUser) {
            if ($sessionEventUser->getRegistrationStatus() === SessionEventUser::REGISTERED) {
                $event = new LogSessionEventUserUnregistrationEvent($sessionEventUser);
                $this->eventDispatcher->dispatch('log', $event);
            }
            $this->om->remove($sessionEventUser);
        }
        $this->om->endFlushSuite();
    }

    public function selfRegisterUserToSessionEvent(SessionEvent $sessionEvent, User $user)
    {
        $results = [];
        $sessionRegistrationStatus = null;

        if ($sessionEvent->getRegistrationType() === CourseSession::REGISTRATION_PUBLIC) {
            $session = $sessionEvent->getSession();
            $sessionUser = $this->getOneSessionUserBySessionAndUserAndTypes(
                $session,
                $user,
                [CourseSessionUser::LEARNER, CourseSessionUser::PENDING_LEARNER]
            );
            $sessionPendingUser = $this->getOneSessionQueueBySessionAndUser($session, $user);
            $sessionEventUser = $this->getSessionEventUserBySessionEventAndUser($sessionEvent, $user);

            if (is_null($sessionEventUser)) {
                if (is_null($sessionUser) && is_null($sessionPendingUser)) {
                    if ($session->getPublicRegistration()) {
                        if ($session->hasValidation()) {
                            $this->addUserToSessionQueue($user, $session);
                            $sessionRegistrationStatus = 'pending';
                        } else {
                            $sessionDatas = $this->registerUsersToSession($session, [$user], CourseSessionUser::LEARNER);

                            if ($sessionDatas['status'] === 'success') {
                                $sessionRegistrationStatus = 'registered';
                            } else {
                                $this->sendRegistrationConfirmationMessage($user, $sessionEvent, 'failed');
                            }
                        }
                        if ($sessionRegistrationStatus === 'registered') {
                            $eventDatas = $this->registerUsersToSessionEvent($sessionEvent, [$user]);

                            if ($eventDatas['status'] === 'failed') {
                                $this->createSessionEventUser($user, $sessionEvent, SessionEventUser::PENDING, null, new \DateTime());
                            }
                            $this->sendRegistrationConfirmationMessage($user, $sessionEvent, 'success', $eventDatas['status']);
                        } elseif ($sessionRegistrationStatus === 'pending') {
                            $this->createSessionEventUser($user, $sessionEvent, SessionEventUser::PENDING, null, new \DateTime());
                            $this->sendRegistrationConfirmationMessage($user, $sessionEvent, 'pending', 'failed');
                        }
                    }
                } elseif (!is_null($sessionUser)) {
                    $eventDatas = $this->registerUsersToSessionEvent($sessionEvent, [$user]);

                    if ($eventDatas['status'] === 'failed') {
                        $this->createSessionEventUser($user, $sessionEvent, SessionEventUser::PENDING, null, new \DateTime());
                    }
                    $this->sendRegistrationConfirmationMessage($user, $sessionEvent, 'none', $eventDatas['status']);
                } else {
                    $this->createSessionEventUser($user, $sessionEvent, SessionEventUser::PENDING, null, new \DateTime());
                    $this->sendRegistrationConfirmationMessage($user, $sessionEvent, 'none', 'failed');
                }
            }
        }

        return $results;
    }

    public function getSessionEventRemainingPlaces(SessionEvent $sessionEvent)
    {
        $remainingPlaces = null;
        $maxUsers = $sessionEvent->getMaxUsers();

        if (!is_null($maxUsers)) {
            $remainingPlaces = $maxUsers;
            $eventUsers = $sessionEvent->getSessionEventUsers();

            foreach ($eventUsers as $eventUser) {
                if ($eventUser->getRegistrationStatus() === SessionEventUser::REGISTERED) {
                    --$remainingPlaces;
                }
            }
        }

        return $remainingPlaces;
    }

    public function checkPendingSessionEventUsers(SessionEvent $sessionEvent)
    {
        $remainingPlaces = $this->getSessionEventRemainingPlaces($sessionEvent);
        $session = $sessionEvent->getSession();
        $users = $this->getUsersBySessionAndType($session);
        $pendingUsersToRegister = $this->getSessionEventUsersFromListBySessionEventAndStatus(
            $sessionEvent,
            $users,
            SessionEventUser::PENDING
        );

        if (is_null($remainingPlaces) || count($pendingUsersToRegister) < $remainingPlaces) {
            $now = new \DateTime();
            $this->om->startFlushSuite();

            foreach ($pendingUsersToRegister as $sessionEventUser) {
                $sessionEventUser->setRegistrationStatus(SessionEventUser::REGISTERED);
                $sessionEventUser->setRegistrationDate($now);
                $this->om->persist($sessionEventUser);
                $this->sendRegistrationConfirmationMessage(
                    $sessionEventUser->getUser(),
                    $sessionEventUser->getSessionEvent(),
                    'none',
                    'pending_to_registered'
                );
            }
            $this->om->endFlushSuite();

            return $pendingUsersToRegister;
        } else {
            return [];
        }
    }

    public function registerSessionUsersToSessionEvent(SessionEvent $sessionEvent)
    {
        $remainingPlaces = $this->getSessionEventRemainingPlaces($sessionEvent);
        $session = $sessionEvent->getSession();
        $users = $this->getUsersBySessionAndType($session);
        $usersToRegister = $this->getUnregisteredUsersFromListBySessionEvent($sessionEvent, $users);
        $registrationStatus = is_null($remainingPlaces) || count($usersToRegister) < $remainingPlaces ?
            SessionEventUser::REGISTERED :
            SessionEventUser::PENDING;

        return $this->forceRegisterUsersWithTypeToSessionEvent($sessionEvent, $usersToRegister, $registrationStatus);
    }

    public function forceRegisterUsersWithTypeToSessionEvent(SessionEvent $sessionEvent, array $users, $registrationStatus)
    {
        $sessionEventUsers = [];
        $registrationDate = $registrationStatus === SessionEventUser::REGISTERED ? new \DateTime() : null;
        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $sessionEventUser = $this->createSessionEventUser($user, $sessionEvent, $registrationStatus, $registrationDate);
            $sessionEventUsers[] = $sessionEventUser;
        }
        $this->om->endFlushSuite();

        return $sessionEventUsers;
    }

    public function getUsersBySessionEventAndStatus(SessionEvent $sessionEvent, $status)
    {
        $users = [];
        $sessionEventUsers = $this->getSessionEventUsersBySessionEventAndStatus($sessionEvent, $status);

        foreach ($sessionEventUsers as $sessionEventUser) {
            $users[] = $sessionEventUser->getUser();
        }

        return $users;
    }

    public function registerPendingSessionEventUsers(User $user, CourseSession $session)
    {
        $pendingSessionEventUsers = $this->getSessionEventUsersByUserAndSessionAndStatus($user, $session, SessionEventUser::PENDING);
        $registrationDate = new \DateTime();
        $this->om->startFlushSuite();

        foreach ($pendingSessionEventUsers as $seu) {
            $sessionEvent = $seu->getSessionEvent();
            $remainingPlaces = $this->getSessionEventRemainingPlaces($sessionEvent);

            if (is_null($remainingPlaces) || $remainingPlaces > 0) {
                $seu->setRegistrationStatus(SessionEventUser::REGISTERED);
                $seu->setRegistrationDate($registrationDate);
                $this->om->persist($seu);
                $this->sendRegistrationConfirmationMessage(
                    $seu->getUser(),
                    $seu->getSessionEvent(),
                    'none',
                    'pending_to_registered'
                );
            }
        }
        $this->om->endFlushSuite();
    }

    public function registerUserToAllAutomaticSessionEvent(User $user, CourseSession $session)
    {
        $autoSessionEvents = $this->sessionEventRepo->findBy(['session' => $session, 'registrationType' => CourseSession::REGISTRATION_AUTO]);
        $registrationDate = new \DateTime();
        $this->om->startFlushSuite();

        foreach ($autoSessionEvents as $sessionEvent) {
            $sessionEventUser = $this->sessionEventUserRepo->findOneBy(['sessionEvent' => $sessionEvent, 'user' => $user]);

            if (is_null($sessionEventUser)) {
                $this->createSessionEventUser($user, $sessionEvent, SessionEventUser::REGISTERED, $registrationDate);
            } elseif ($sessionEventUser->getRegistrationStatus() === SessionEventUser::PENDING) {
                $sessionEventUser->getRegistrationStatus(SessionEventUser::REGISTERED);
                $sessionEventUser->setRegistrationDate($registrationDate);
                $this->om->persist($sessionEventUser);
            }
        }
        $this->om->endFlushSuite();
    }

    public function createSessionEventUser(
        User $user,
        SessionEvent $sessionEvent,
        $registrationStatus,
        $registrationDate = null,
        $applicationDate = null
    ) {
        $sessionEventUser = new SessionEventUser();
        $sessionEventUser->setSessionEvent($sessionEvent);
        $sessionEventUser->setUser($user);
        $sessionEventUser->setRegistrationStatus($registrationStatus);
        $sessionEventUser->setRegistrationDate($registrationDate);
        $sessionEventUser->setApplicationDate($applicationDate);
        $this->om->persist($sessionEventUser);
        $this->om->flush();

        if ($registrationStatus === SessionEventUser::REGISTERED) {
            $event = new LogSessionEventUserRegistrationEvent($sessionEvent, $user);
            $this->eventDispatcher->dispatch('log', $event);
        }

        return $sessionEventUser;
    }

    private function sendRegistrationConfirmationMessage(User $user, SessionEvent $sessionEvent, $sessionStatus, $sessionEventStatus = null)
    {
        $session = $sessionEvent->getSession();
        $object = '';
        $content = '';
        $successObject = $this->translator->trans(
            'session_event_registration_request',
            [
                '%event_name%' => $sessionEvent->getName(),
                '%event_start_date%' => $sessionEvent->getStartDate()->format('d/m/Y'),
                '%status%' => $this->translator->trans('registered', [], 'platform'),
            ],
            'cursus'
        );
        $failedObject = $this->translator->trans(
            'session_event_registration_request',
            [
                '%event_name%' => $sessionEvent->getName(),
                '%event_start_date%' => $sessionEvent->getStartDate()->format('d/m/Y'),
                '%status%' => $this->translator->trans('failed', [], 'platform'),
            ],
            'cursus'
        );
        $pendingObject = $this->translator->trans(
            'session_event_registration_request',
            [
                '%event_name%' => $sessionEvent->getName(),
                '%event_start_date%' => $sessionEvent->getStartDate()->format('d/m/Y'),
                '%status%' => $this->translator->trans('pending', [], 'platform'),
            ],
            'cursus'
        );

        switch ($sessionStatus) {
            case 'success':
                $object = $sessionEventStatus === 'success' ? $successObject : $pendingObject;
                $content = $sessionEventStatus === 'success' ?
                    $this->translator->trans(
                        'session_and_event_registration_success_msg',
                        [
                            '%event_name%' => $sessionEvent->getName(),
                            '%event_start_date%' => $sessionEvent->getStartDate()->format('d/m/Y'),
                            '%session_name%' => $session->getName(),
                        ],
                        'cursus'
                    ) :
                    $this->translator->trans(
                        'session_and_event_registration_success_pending_msg',
                        [
                            '%event_name%' => $sessionEvent->getName(),
                            '%event_start_date%' => $sessionEvent->getStartDate()->format('d/m/Y'),
                            '%session_name%' => $session->getName(),
                        ],
                        'cursus'
                    );
                break;
            case 'failed':
                $object = $failedObject;
                $content = $this->translator->trans(
                    'session_and_event_registration_failed',
                    ['%event_name%' => $sessionEvent->getName(), '%event_start_date%' => $sessionEvent->getStartDate()->format('d/m/Y')],
                    'cursus'
                );
                break;
            case 'pending':
                $object = $pendingObject;
                $content = $this->translator->trans(
                    'session_and_event_registration_pending_msg',
                    [
                        '%event_name%' => $sessionEvent->getName(),
                        '%event_start_date%' => $sessionEvent->getStartDate()->format('d/m/Y'),
                        '%session_name%' => $session->getName(),
                    ],
                    'cursus'
                );
                break;
            case 'none':
                switch ($sessionEventStatus) {
                    case 'success':
                        $object = $successObject;
                        $content = $this->translator->trans(
                            'session_event_registration_success_msg',
                            ['%event_name%' => $sessionEvent->getName(), '%event_start_date%' => $sessionEvent->getStartDate()->format('d/m/Y')],
                            'cursus'
                        );
                        break;
                    case 'failed':
                        $object = $pendingObject;
                        $content = $this->translator->trans(
                            'session_event_registration_pending_msg',
                            ['%event_name%' => $sessionEvent->getName(), '%event_start_date%' => $sessionEvent->getStartDate()->format('d/m/Y')],
                            'cursus'
                        );
                        break;
                    case 'pending_to_registered':
                        $object = $successObject;
                        $content = $this->translator->trans(
                            'session_event_registration_pending_to_registered_msg',
                            ['%event_name%' => $sessionEvent->getName(), '%event_start_date%' => $sessionEvent->getStartDate()->format('d/m/Y')],
                            'cursus'
                        );
                        break;
                }
                break;
        }
        $message = $this->messageManager->create($content, $object, [$user]);
        $this->messageManager->send($message, true, false);
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

    public function getAllCursus($search = '', $orderedBy = 'cursusOrder', $order = 'ASC', $withPager = false, $page = 1, $max = 50)
    {
        $cursus = empty($search) ?
            $this->cursusRepo->findAllCursus($orderedBy, $order) :
            $this->cursusRepo->findSearchedCursus($search, $orderedBy, $order);

        return $withPager ? $this->pagerFactory->createPagerFromArray($cursus, $page, $max) : $cursus;
    }

    public function getAllRootCursus($search = '', $orderedBy = 'id', $order = 'ASC', $withPager = false, $page = 1, $max = 50)
    {
        $cursus = empty($search) ?
            $this->cursusRepo->findAllRootCursus($orderedBy, $order) :
            $this->cursusRepo->findSearchedRootCursus($search, $orderedBy, $order);

        return $withPager ? $this->pagerFactory->createPagerFromArray($cursus, $page, $max) : $cursus;
    }

    public function getLastRootCursusOrder($executeQuery = true)
    {
        return $this->cursusRepo->findLastRootCursusOrder($executeQuery);
    }

    public function getLastCursusOrderByParent(Cursus $cursus, $executeQuery = true)
    {
        return $this->cursusRepo->findLastCursusOrderByParent($cursus, $executeQuery);
    }

    public function getHierarchyByCursus(Cursus $cursus, $orderedBy = 'cursusOrder', $order = 'ASC', $executeQuery = true)
    {
        return $this->cursusRepo->findHierarchyByCursus($cursus, $orderedBy, $order, $executeQuery);
    }

    public function getRelatedHierarchyByCursus(Cursus $cursus, $orderedBy = 'cursusOrder', $order = 'ASC', $executeQuery = true)
    {
        return $this->cursusRepo->findRelatedHierarchyByCursus($cursus, $orderedBy, $order, $executeQuery);
    }

    public function getDescendantHierarchyByCursus(Cursus $cursus, $orderedBy = 'cursusOrder', $order = 'ASC', $executeQuery = true)
    {
        return $this->cursusRepo->findDescendantHierarchyByCursus($cursus, $orderedBy, $order, $executeQuery);
    }

    public function getCursusByParentAndCourses(Cursus $parent, array $courses, $executeQuery = true)
    {
        return $this->cursusRepo->findCursusByParentAndCourses($parent, $courses, $executeQuery);
    }

    public function getCursusByIds(array $ids, $executeQuery = true)
    {
        return count($ids) > 0 ? $this->cursusRepo->findCursusByIds($ids, $executeQuery) : [];
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

        return $withPager ? $this->pagerFactory->createPagerFromArray($cursus, $page, $max) : $cursus;
    }

    public function getCursusByCodeWithoutId($code, $id)
    {
        return $this->cursusRepo->findCursusByCodeWithoutId($code, $id);
    }

    /**************************************
     * Access to CourseRepository methods *
     **************************************/

    public function getAllCourses($search = '', $orderedBy = 'title', $order = 'ASC', $withPager = true, $page = 1, $max = 50)
    {
        $courses = empty($search) ?
            $this->courseRepo->findAllCourses($orderedBy, $order) :
            $this->courseRepo->findSearchedCourses($search, $orderedBy, $order);

        return $withPager ? $this->pagerFactory->createPagerFromArray($courses, $page, $max) : $courses;
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
            $this->courseRepo->findUnmappedCoursesByCursus($cursus, $orderedBy, $order) :
            $this->courseRepo->findUnmappedSearchedCoursesByCursus($cursus, $search, $orderedBy, $order);

        return $withPager ? $this->pagerFactory->createPagerFromArray($courses, $page, $max) : $courses;
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
            $this->courseRepo->findDescendantCoursesByCursus($cursus, $orderedBy, $order) :
            $this->courseRepo->findDescendantSearchedCoursesByCursus($cursus, $search, $orderedBy, $order);

        return $withPager ? $this->pagerFactory->createPagerFromArray($courses, $page, $max) : $courses;
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
            $this->courseRepo->findCoursesByUser($user, $orderedBy, $order) :
            $this->courseRepo->findSearchedCoursesByUser($user, $search, $orderedBy, $order);

        return $withPager ? $this->pagerFactory->createPagerFromArray($courses, $page, $max) : $courses;
    }

    public function getCoursesByUserFromList(
        User $user,
        array $coursesList,
        $search = '',
        $orderedBy = 'title',
        $order = 'ASC',
        $withPager = true,
        $page = 1,
        $max = 20
    ) {
        $courses = empty($search) ?
            $this->courseRepo->findCoursesByUserFromList($user, $coursesList, $orderedBy, $order) :
            $this->courseRepo->findSearchedCoursesByUserFromList($user, $coursesList, $search, $orderedBy, $order);

        return $withPager ? $this->pagerFactory->createPagerFromArray($courses, $page, $max) : $courses;
    }

    public function getCoursesByIds(array $ids, $orderedBy = 'title', $order = 'ASC')
    {
        return count($ids) > 0 ? $this->courseRepo->findCoursesByIds($ids, $orderedBy, $order) : [];
    }

    public function getCourseByCodeWithoutId($code, $id)
    {
        return $this->courseRepo->findCourseByCodeWithoutId($code, $id);
    }

    public function getIndependentCourses()
    {
        return $this->courseRepo->findIndependentCourses();
    }

    /******************************************
     * Access to CursusUserRepository methods *
     ******************************************/

    public function getCursusUsersByCursus(Cursus $cursus, $executeQuery = true)
    {
        return $this->cursusUserRepo->findCursusUsersByCursus($cursus, $executeQuery);
    }

    public function getOneCursusUserByCursusAndUser(Cursus $cursus, User $user, $executeQuery = true)
    {
        return $this->cursusUserRepo->findOneCursusUserByCursusAndUser($cursus, $user, $executeQuery);
    }

    public function getCursusUsersFromCursusAndUsers(array $cursus, array $users)
    {
        if (count($cursus) > 0 && count($users) > 0) {
            return $this->cursusUserRepo->findCursusUsersFromCursusAndUsers($cursus, $users);
        } else {
            return [];
        }
    }

    public function getCursusUsersOfCursusChildren(Cursus $cursus, User $user, $executeQuery = true)
    {
        return $this->cursusUserRepo->findCursusUsersOfCursusChildren($cursus, $user, $executeQuery);
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
            $this->cursusUserRepo->findUnregisteredUsersByCursus($cursus, $orderedBy, $order) :
            $this->cursusUserRepo->findSearchedUnregisteredUsersByCursus($cursus, $search, $orderedBy, $order);

        return $withPager ? $this->pagerFactory->createPagerFromArray($users, $page, $max) : $users;
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
            $this->cursusUserRepo->findUsersByCursus($cursus, $orderedBy, $order) :
            $this->cursusUserRepo->findSearchedUsersByCursus($cursus, $search, $orderedBy, $order);

        return $withPager ? $this->pagerFactory->createPagerFromArray($users, $page, $max) : $users;
    }

    /*******************************************
     * Access to CursusGroupRepository methods *
     *******************************************/

    public function getCursusGroupsByCursus(Cursus $cursus, $executeQuery = true)
    {
        return $this->cursusGroupRepo->findCursusGroupsByCursus($cursus, $executeQuery);
    }

    public function getOneCursusGroupByCursusAndGroup(Cursus $cursus, Group $group, $executeQuery = true)
    {
        return $this->cursusGroupRepo->findOneCursusGroupByCursusAndGroup($cursus, $group, $executeQuery);
    }

    public function getCursusGroupsFromCursusAndGroups(array $cursus, array $groups)
    {
        if (count($cursus) > 0 && count($groups) > 0) {
            return $this->cursusGroupRepo->findCursusGroupsFromCursusAndGroups($cursus, $groups);
        } else {
            return [];
        }
    }

    public function getCursusGroupsOfCursusChildren(Cursus $cursus, Group $group, $executeQuery = true)
    {
        return $this->cursusGroupRepo->findCursusGroupsOfCursusChildren($cursus, $group, $executeQuery);
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
            $this->cursusGroupRepo->findUnregisteredGroupsByCursus($cursus, $orderedBy, $order) :
            $this->cursusGroupRepo->findSearchedUnregisteredGroupsByCursus($cursus, $search, $orderedBy, $order);

        return $withPager ? $this->pagerFactory->createPagerFromArray($groups, $page, $max) : $groups;
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

        return $withPager ? $this->pagerFactory->createPagerFromArray($groups, $page, $max) : $groups;
    }

    public function getCursusGroupsByIds(array $ids, $executeQuery = true)
    {
        return count($ids) > 0 ? $this->cursusGroupRepo->findCursusGroupsByIds($ids, $executeQuery) : [];
    }

    /*********************************************
     * Access to CourseSessionRepository methods *
     *********************************************/

    public function getAllSessions($orderedBy = 'startDate', $order = 'ASC', $executeQuery = true)
    {
        return $this->courseSessionRepo->findAllSessions($orderedBy, $order, $executeQuery);
    }

    public function getUnclosedSessions()
    {
        return $this->courseSessionRepo->findUnclosedSessions();
    }

    public function getUnclosedSessionsByCourse(Course $course)
    {
        return $this->courseSessionRepo->findUnclosedSessionsByCourse($course);
    }

    public function getSessionsByCourse(Course $course, $orderedBy = 'creationDate', $order = 'ASC', $executeQuery = true)
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

    public function getSessionsByCourses(array $courses, $orderedBy = 'creationDate', $order = 'ASC', $executeQuery = true)
    {
        if (count($courses) > 0) {
            return $this->courseSessionRepo->findSessionsByCourses(
                $courses,
                $orderedBy,
                $order,
                $executeQuery
            );
        } else {
            return [];
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
            return [];
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
        return count($ids) > 0 ? $this->courseSessionRepo->findSessionsByIds($ids, $executeQuery) : [];
    }

    /*********************************************
     * Access to SessionEventRepository methods *
     *********************************************/

    public function getEventsBySession(CourseSession $session, $orderedBy = 'startDate', $order = 'ASC', $executeQuery = true)
    {
        return $this->sessionEventRepo->findEventsBySession($session, $orderedBy, $order, $executeQuery);
    }

    public function getSessionEventsBySessionAndUserAndRegistrationStatus(CourseSession $session, User $user, $registrationStatus)
    {
        return $this->sessionEventRepo->findSessionEventsBySessionAndUserAndRegistrationStatus($session, $user, $registrationStatus);
    }

    /*************************************************
     * Access to CourseSessionUserRepository methods *
     *************************************************/

    public function getSessionUsersBySessionAndType(CourseSession $session, $userType = 0, $executeQuery = true)
    {
        return $this->sessionUserRepo->findSessionUsersBySessionAndType($session, $userType, $executeQuery);
    }

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

    public function getOneSessionUserBySessionAndUserAndTypes(CourseSession $session, User $user, array $userTypes)
    {
        return count($userTypes) > 0 ?
            $this->sessionUserRepo->findOneSessionUserBySessionAndUserAndTypes($session, $user, $userTypes) :
            [];
    }

    public function getSessionUsersByUser(User $user, $search = '', $executeQuery = true)
    {
        return $search === '' ?
            $this->sessionUserRepo->findSessionUsersByUser($user, $executeQuery) :
            $this->sessionUserRepo->findSessionUsersByUserAndSearch($user, $search, $executeQuery);
    }

    public function getSessionUsersByUserFromCoursesList(User $user, array $coursesList = [], $search = '', $executeQuery = true)
    {
        return $search === '' ?
            $this->sessionUserRepo->findSessionUsersByUserFromCoursesList($user, $coursesList, $executeQuery) :
            $this->sessionUserRepo->findSessionUsersByUserAndSearchFromCoursesList($user, $coursesList, $search, $executeQuery);
    }

    public function getSessionUsersBySession(CourseSession $session, $executeQuery = true)
    {
        return $this->sessionUserRepo->findSessionUsersBySession($session, $executeQuery);
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
            return [];
        }
    }

    public function getSessionUsersBySessionsAndUsers(array $sessions, array $users, $userType, $executeQuery = true)
    {
        if (count($users) > 0 && count($sessions) > 0) {
            return $this->sessionUserRepo->findSessionUsersBySessionsAndUsers(
                $sessions,
                $users,
                $userType,
                $executeQuery
            );
        } else {
            return [];
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

        return $withPager ? $this->pagerFactory->createPagerFromArray($users, $page, $max) : $users;
    }

    public function getSessionUsersByUserAndSessionStatus(User $user, $status, $currentDate = null, $search = '', $coursesList = null)
    {
        $now = $currentDate ? $currentDate : new \DateTime();

        return $this->sessionUserRepo->findSessionUsersByUserAndStatusAndDate($user, $status, $now, $search, $coursesList);
    }

    /**************************************************
     * Access to CourseSessionGroupRepository methods *
     **************************************************/

    public function getOneSessionGroupBySessionAndGroup(CourseSession $session, Group $group, $executeQuery = true)
    {
        return $this->sessionGroupRepo->findOneSessionGroupBySessionAndGroup($session, $group, $executeQuery);
    }

    public function getSessionGroupsBySession(CourseSession $session, $executeQuery = true)
    {
        return $this->sessionGroupRepo->findSessionGroupsBySession($session, $executeQuery);
    }

    public function getSessionGroupsBySessionsAndGroup(array $sessions, Group $group, $groupType, $executeQuery = true)
    {
        return $this->sessionGroupRepo->findSessionGroupsBySessionsAndGroup($sessions, $group, $groupType, $executeQuery);
    }

    public function getSessionGroupsByGroup(Group $group, $executeQuery = true)
    {
        return $this->sessionGroupRepo->findSessionGroupsByGroup($group, $executeQuery);
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

        return $withPager ? $this->pagerFactory->createPagerFromArray($groups, $page, $max) : $groups;
    }

    /**************************************************************
     * Access to CourseSessionRegistrationQueueRepository methods *
     **************************************************************/

    public function getSessionQueuesBySession(CourseSession $session, $executeQuery = true)
    {
        return $this->sessionQueueRepo->findSessionQueuesBySession($session, $executeQuery);
    }

    public function getSessionQueuesByUser(User $user, $executeQuery = true)
    {
        return $this->sessionQueueRepo->findSessionQueuesByUser($user, $executeQuery);
    }

    public function getOneSessionQueueBySessionAndUser(CourseSession $session, User $user, $executeQuery = true)
    {
        return $this->sessionQueueRepo->findOneSessionQueueBySessionAndUser($session, $user, $executeQuery);
    }

    public function getSessionQueuesByCourse(Course $course, $executeQuery = true)
    {
        return $this->sessionQueueRepo->findSessionQueuesByCourse($course, $executeQuery);
    }

    public function getSessionQueuesByCourses(array $courses)
    {
        return count($courses) > 0 ? $this->sessionQueueRepo->findSessionQueuesByCourses($courses) : [];
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
        return $this->courseQueueRepo->findCourseQueuesByCourse($course, $executeQuery);
    }

    public function getCourseQueuesByUser(User $user, $executeQuery = true)
    {
        return $this->courseQueueRepo->findCourseQueuesByUser($user, $executeQuery);
    }

    public function getOneCourseQueueByCourseAndUser(Course $course, User $user, $executeQuery = true)
    {
        return $this->courseQueueRepo->findOneCourseQueueByCourseAndUser($course, $user, $executeQuery);
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

    /*********************************************
     * Access to DoucmentModelRepository methods *
     *********************************************/

    public function getAllDocumentModels()
    {
        return $this->documentModelRepo->findBy([], ['name' => 'ASC']);
    }

    public function getDocumentModelsByType($type)
    {
        return $this->documentModelRepo->findBy(['documentType' => $type], ['name' => 'ASC']);
    }

    public function getDocumentModelById($id)
    {
        return $this->documentModelRepo->findOneById($id);
    }

    /***************************************************
     * Access to ReservationResourceRepository methods *
     ***************************************************/

    public function getAllReservationResources()
    {
        return $this->reservationResourceRepo->findBy([], ['name' => 'ASC']);
    }

    public function getReservationResourceById($id)
    {
        return $this->reservationResourceRepo->findOneById($id);
    }

    /************************************************
     * Access to SessionEventUserRepository methods *
     ************************************************/

    public function getSessionEventUsersBySessionEvent(SessionEvent $sessionEvent)
    {
        return $this->sessionEventUserRepo->findBy(['sessionEvent' => $sessionEvent], ['registrationStatus' => 'DESC']);
    }

    public function getSessionEventUsersByUser(User $user)
    {
        return $this->sessionEventUserRepo->findBy(['user' => $user], ['registrationStatus' => 'DESC']);
    }

    public function getSessionEventUserBySessionEventAndUser(SessionEvent $sessionEvent, User $user)
    {
        return $this->sessionEventUserRepo->findOneBy(['sessionEvent' => $sessionEvent, 'user' => $user]);
    }

    public function getSessionEventUsersBySessionEventAndStatus(SessionEvent $sessionEvent, $status)
    {
        return $this->sessionEventUserRepo->findBy(['sessionEvent' => $sessionEvent, 'registrationStatus' => $status]);
    }

    public function getSessionEventUsersByUserAndSessionAndStatus(User $user, CourseSession $session, $status)
    {
        return $this->sessionEventUserRepo->findSessionEventUsersByUserAndSessionAndStatus($user, $session, $status);
    }

    public function getUnregisteredUsersFromListBySessionEvent(SessionEvent $sessionEvent, array $users)
    {
        return $this->sessionEventUserRepo->findUnregisteredUsersFromListBySessionEvent($sessionEvent, $users);
    }

    public function getSessionEventUsersFromListBySessionEventAndStatus(SessionEvent $sessionEvent, array $users, $status)
    {
        return $this->sessionEventUserRepo->findSessionEventUsersFromListBySessionEventAndStatus($sessionEvent, $users, $status);
    }

    /******************
     * Others methods *
     ******************/

    public function getSessionsByUserAndType(User $user, $userType = 0)
    {
        $sessions = [];
        $sessionUsers = $this->getSessionUsersByUser($user);

        foreach ($sessionUsers as $sessionUser) {
            $type = $sessionUser->getUserType();

            if ($type === $userType) {
                $sessions[] = $sessionUser->getSession();
            }
        }

        return $sessions;
    }

    public function getUsersBySessionAndType(CourseSession $session, $userType = CourseSessionUser::LEARNER)
    {
        $users = [];
        $sessionUsers = $this->getSessionUsersBySessionAndType($session, $userType);

        foreach ($sessionUsers as $su) {
            $users[] = $su->getUser();
        }

        return $users;
    }

    public function getSessionsByGroupAndType(Group $group, $groupType = 0)
    {
        $sessions = [];
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

        if (is_null($cursusTool) || !$this->authorization->isGranted('OPEN', $cursusTool)) {
            throw new AccessDeniedException();
        }
    }

    private function hasCursusToolRegistrationAccess()
    {
        $cursusTool = $this->toolManager->getAdminToolByName('claroline_cursus_tool_registration');

        return $this->authorization->isGranted('OPEN', $cursusTool);
    }
}
