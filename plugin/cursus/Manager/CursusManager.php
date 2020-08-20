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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CursusBundle\Entity\AbstractRegistrationQueue;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\CourseRegistrationQueue;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\CourseSessionGroup;
use Claroline\CursusBundle\Entity\CourseSessionRegistrationQueue;
use Claroline\CursusBundle\Entity\CourseSessionUser;
use Claroline\CursusBundle\Entity\Cursus;
use Claroline\CursusBundle\Entity\CursusGroup;
use Claroline\CursusBundle\Entity\CursusUser;
use Claroline\CursusBundle\Entity\SessionEvent;
use Claroline\CursusBundle\Entity\SessionEventUser;
use Claroline\CursusBundle\Event\Log\LogCourseQueueCreateEvent;
use Claroline\CursusBundle\Event\Log\LogCursusCreateEvent;
use Claroline\CursusBundle\Event\Log\LogCursusGroupRegistrationEvent;
use Claroline\CursusBundle\Event\Log\LogCursusUserRegistrationEvent;
use Claroline\CursusBundle\Event\Log\LogSessionEventUserRegistrationEvent;
use Claroline\CursusBundle\Event\Log\LogSessionGroupRegistrationEvent;
use Claroline\CursusBundle\Event\Log\LogSessionQueueCreateEvent;
use Claroline\CursusBundle\Event\Log\LogSessionQueueOrganizationValidateEvent;
use Claroline\CursusBundle\Event\Log\LogSessionQueueUserValidateEvent;
use Claroline\CursusBundle\Event\Log\LogSessionQueueValidateEvent;
use Claroline\CursusBundle\Event\Log\LogSessionQueueValidatorValidateEvent;
use Claroline\CursusBundle\Event\Log\LogSessionUserRegistrationEvent;
use Claroline\CursusBundle\Repository\SessionEventRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CursusManager
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var MailManager */
    private $mailManager;
    /** @var ObjectManager */
    private $om;
    /** @var RoleManager */
    private $roleManager;
    /** @var UrlGeneratorInterface */
    private $router;
    /** @var TemplateManager */
    private $templateManager;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var WorkspaceManager */
    private $workspaceManager;

    private $courseSessionRepo;
    private $cursusUserRepo;
    private $cursusGroupRepo;
    private $sessionUserRepo;
    private $sessionGroupRepo;
    /** @var SessionEventRepository */
    private $sessionEventRepo;
    private $sessionEventUserRepo;
    private $courseQueueRepo;
    private $sessionQueueRepo;
    private $workspaceRepo;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        MailManager $mailManager,
        ObjectManager $om,
        RoleManager $roleManager,
        UrlGeneratorInterface $router,
        TemplateManager $templateManager,
        TokenStorageInterface $tokenStorage,
        WorkspaceManager $workspaceManager
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->mailManager = $mailManager;
        $this->om = $om;
        $this->roleManager = $roleManager;
        $this->router = $router;
        $this->templateManager = $templateManager;
        $this->tokenStorage = $tokenStorage;
        $this->workspaceManager = $workspaceManager;

        $this->courseSessionRepo = $om->getRepository(CourseSession::class);
        $this->cursusUserRepo = $om->getRepository(CursusUser::class);
        $this->cursusGroupRepo = $om->getRepository(CursusGroup::class);
        $this->sessionUserRepo = $om->getRepository(CourseSessionUser::class);
        $this->sessionGroupRepo = $om->getRepository(CourseSessionGroup::class);
        $this->sessionEventRepo = $om->getRepository(SessionEvent::class);
        $this->sessionEventUserRepo = $om->getRepository(SessionEventUser::class);
        $this->courseQueueRepo = $om->getRepository(CourseRegistrationQueue::class);
        $this->sessionQueueRepo = $om->getRepository(CourseSessionRegistrationQueue::class);
        $this->workspaceRepo = $om->getRepository(Workspace::class);
    }

    /**
     * Creates Cursus for each Course and add them as children of given Cursus.
     *
     * @return Cursus
     */
    public function addCoursesToCursus(Cursus $parent, array $courses)
    {
        $results = [];
        $organizations = $parent->getOrganizations();

        $this->om->startFlushSuite();

        foreach ($courses as $course) {
            $cursus = new Cursus();
            $cursus->setParent($parent);
            $cursus->setCourse($course);
            $cursus->setTitle($course->getTitle());

            foreach ($organizations as $organization) {
                $cursus->addOrganization($organization);
            }
            $this->om->persist($cursus);

            $this->eventDispatcher->dispatch(new LogCursusCreateEvent($cursus), 'log');

            $results[] = $cursus;
        }
        $this->om->endFlushSuite();

        return $results;
    }

    /**
     * Adds users to a cursus.
     *
     * @param int $type
     *
     * @return array
     */
    public function addUsersToCursus(Cursus $cursus, array $users, $type = CursusUser::TYPE_LEARNER)
    {
        $results = [];
        $registrationDate = new \DateTime();
        $workspace = $cursus->getWorkspace();
        $role = $workspace ? $this->roleManager->getCollaboratorRole($workspace) : null;

        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $cursusUser = $this->cursusUserRepo->findOneBy(['cursus' => $cursus, 'user' => $user, 'userType' => $type]);

            if (empty($cursusUser)) {
                $cursusUser = new CursusUser();
                $cursusUser->setCursus($cursus);
                $cursusUser->setUser($user);
                $cursusUser->setUserType($type);
                $cursusUser->setRegistrationDate($registrationDate);

                // Registers user to workspace if one is associated to cursus
                if ($role) {
                    $this->roleManager->associateRole($user, $role);
                }

                $this->om->persist($cursusUser);

                $this->eventDispatcher->dispatch(new LogCursusUserRegistrationEvent($cursusUser), 'log');

                $results[] = $cursusUser;
            }
        }
        $this->om->endFlushSuite();

        return $results;
    }

    /**
     * Adds groups to a cursus.
     *
     * @param int $type
     *
     * @return array
     */
    public function addGroupsToCursus(Cursus $cursus, array $groups, $type = CursusGroup::TYPE_LEARNER)
    {
        $results = [];
        $registrationDate = new \DateTime();
        $workspace = $cursus->getWorkspace();
        $role = $workspace ? $this->roleManager->getCollaboratorRole($workspace) : null;

        $this->om->startFlushSuite();

        foreach ($groups as $group) {
            $cursusGroup = $this->cursusGroupRepo->findOneBy(['cursus' => $cursus, 'group' => $group, 'groupType' => $type]);

            if (empty($cursusGroup)) {
                $cursusGroup = new CursusGroup();
                $cursusGroup->setCursus($cursus);
                $cursusGroup->setGroup($group);
                $cursusGroup->setGroupType($type);
                $cursusGroup->setRegistrationDate($registrationDate);

                // Registers group to workspace if one is associated to cursus
                if ($role) {
                    $this->roleManager->associateRole($group, $role);
                }

                $this->om->persist($cursusGroup);

                $this->eventDispatcher->dispatch(new LogCursusGroupRegistrationEvent($cursusGroup), 'log');

                $results[] = $cursusGroup;
            }
        }
        $this->om->endFlushSuite();

        return $results;
    }

    /**
     * Adds users to a session.
     *
     * @param int $type
     *
     * @return array
     */
    public function addUsersToSession(CourseSession $session, array $users, $type = CourseSessionUser::TYPE_LEARNER)
    {
        $results = [];
        $registrationDate = new \DateTime();

        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $sessionUser = $this->sessionUserRepo->findOneBy(['session' => $session, 'user' => $user, 'userType' => $type]);

            if (empty($sessionUser)) {
                $sessionUser = new CourseSessionUser();
                $sessionUser->setSession($session);
                $sessionUser->setUser($user);
                $sessionUser->setUserType($type);
                $sessionUser->setRegistrationDate($registrationDate);

                // Registers user to session workspace
                $role = CourseSessionGroup::TYPE_TEACHER === $type ? $session->getTutorRole() : $session->getLearnerRole();

                if ($role) {
                    $this->roleManager->associateRole($user, $role);
                }
                $this->om->persist($sessionUser);

                $this->eventDispatcher->dispatch(new LogSessionUserRegistrationEvent($sessionUser), 'log');

                $results[] = $sessionUser;
            }
        }
        if (CourseSessionUser::TYPE_LEARNER === $type) {
            $events = $session->getEvents()->toArray();

            foreach ($events as $event) {
                if (CourseSession::REGISTRATION_AUTO === $event->getRegistrationType() && !$event->isTerminated()) {
                    $this->addUsersToSessionEvent($event, $users);
                }
            }
        }
        $this->om->endFlushSuite();

        return $results;
    }

    /**
     * Adds groups to a session.
     *
     * @param int $type
     *
     * @return array
     */
    public function addGroupsToSession(CourseSession $session, array $groups, $type = CourseSessionGroup::TYPE_LEARNER)
    {
        $results = [];
        $registrationDate = new \DateTime();

        $this->om->startFlushSuite();

        foreach ($groups as $group) {
            $sessionGroup = $this->sessionGroupRepo->findOneBy(['session' => $session, 'group' => $group, 'groupType' => $type]);

            if (empty($sessionGroup)) {
                $sessionGroup = new CourseSessionGroup();
                $sessionGroup->setSession($session);
                $sessionGroup->setGroup($group);
                $sessionGroup->setGroupType($type);
                $sessionGroup->setRegistrationDate($registrationDate);

                // Registers group to session workspace
                $role = CourseSessionGroup::TYPE_TEACHER === $type ? $session->getTutorRole() : $session->getLearnerRole();

                if ($role) {
                    $this->roleManager->associateRole($group, $role);
                }
                $this->om->persist($sessionGroup);

                $this->eventDispatcher->dispatch(new LogSessionGroupRegistrationEvent($sessionGroup), 'log');

                $results[] = $sessionGroup;
            }
        }
        $this->om->endFlushSuite();

        return $results;
    }

    /**
     * Adds users to a session event.
     *
     * @return array
     */
    public function addUsersToSessionEvent(SessionEvent $event, array $users)
    {
        $results = [];
        $registrationDate = new \DateTime();

        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $eventUser = $this->sessionEventUserRepo->findOneBy(['sessionEvent' => $event, 'user' => $user]);

            if (empty($eventUser)) {
                $eventUser = new SessionEventUser();
                $eventUser->setSessionEvent($event);
                $eventUser->setUser($user);
                $eventUser->setRegistrationDate($registrationDate);
                $this->om->persist($eventUser);

                $this->eventDispatcher->dispatch(new LogSessionEventUserRegistrationEvent($eventUser), 'log');

                $results[] = $eventUser;
            }
        }
        $this->om->endFlushSuite();

        return $results;
    }

    /**
     * Registers an user to default session of a course if allowed.
     *
     * @param bool $skipValidation
     *
     * @return CourseRegistrationQueue|CourseSessionUser|CourseSessionRegistrationQueue|null
     */
    public function registerUserToCourse(Course $course, User $user, $skipValidation = false)
    {
        $validationMask = 0;

        if (!$skipValidation) {
            if ($course->getRegistrationValidation()) {
                $validationMask += AbstractRegistrationQueue::WAITING;
            }
            if ($course->getUserValidation()) {
                $validationMask += AbstractRegistrationQueue::WAITING_USER;
            }
            if (0 < count($course->getValidators())) {
                $validationMask += AbstractRegistrationQueue::WAITING_VALIDATOR;
            }
            if ($course->getOrganizationValidation()) {
                $validationMask += AbstractRegistrationQueue::WAITING_ORGANIZATION;
            }
        }
        if (0 < $validationMask) {
            $courseQueue = $this->courseQueueRepo->findOneBy(['course' => $course, 'user' => $user]);

            if (!$courseQueue) {
                $courseQueue = $this->createCourseQueue($course, $user, $validationMask);
            }

            return $courseQueue;
        } else {
            $defaultSession = $course->getDefaultSession();
            $result = null;

            if ($defaultSession && $defaultSession->isActive()) {
                $result = $this->registerUserToSession($defaultSession, $user, $skipValidation);
            }

            return $result;
        }
    }

    /**
     * Registers an user to a session if allowed.
     *
     * @param bool $skipValidation
     *
     * @return CourseSessionUser|CourseSessionRegistrationQueue
     */
    public function registerUserToSession(CourseSession $session, User $user, $skipValidation = false)
    {
        $validationMask = 0;

        if (!$skipValidation) {
            if ($session->getRegistrationValidation()) {
                $validationMask += AbstractRegistrationQueue::WAITING;
            }
            if ($session->getUserValidation()) {
                $validationMask += AbstractRegistrationQueue::WAITING_USER;
            }
            if (0 < count($session->getValidators())) {
                $validationMask += AbstractRegistrationQueue::WAITING_VALIDATOR;
            }
            if ($session->getOrganizationValidation()) {
                $validationMask += AbstractRegistrationQueue::WAITING_ORGANIZATION;
            }
        }
        if (0 < $validationMask) {
            $sessionQueue = $this->sessionQueueRepo->findOneBy(['session' => $session, 'user' => $user]);

            if (!$sessionQueue) {
                $sessionQueue = $this->createSessionQueue($session, $user, $validationMask);
            }

            return $sessionQueue;
        } else {
            if ($this->checkSessionCapacity($session)) {
                return $this->addUsersToSession($session, [$user]);
            }
        }
    }

    /**
     * Registers an user to a session event.
     */
    public function registerUserToSessionEvent(SessionEvent $event, User $user)
    {
        if ($this->checkSessionEventCapacity($event)) {
            $this->addUsersToSessionEvent($event, [$user]);
        }
    }

    /**
     * Creates a queue for course and user.
     *
     * @param int       $mask
     * @param \DateTime $date
     *
     * @return CourseRegistrationQueue
     */
    public function createCourseQueue(Course $course, User $user, $mask = 0, $date = null)
    {
        $this->om->startFlushSuite();
        $queue = new CourseRegistrationQueue();
        $queue->setUser($user);
        $queue->setCourse($course);
        $queue->setStatus($mask);

        if ($date) {
            $queue->setApplicationDate($date);
        }
        $this->om->persist($queue);

        $this->eventDispatcher->dispatch(new LogCourseQueueCreateEvent($queue), 'log');

        $this->om->endFlushSuite();

        return $queue;
    }

    /**
     * Creates a queue for session and user.
     *
     * @param int       $mask
     * @param \DateTime $date
     *
     * @return CourseSessionRegistrationQueue
     */
    public function createSessionQueue(CourseSession $session, User $user, $mask = 0, $date = null)
    {
        $this->om->startFlushSuite();
        $queue = new CourseSessionRegistrationQueue();
        $queue->setUser($user);
        $queue->setSession($session);
        $queue->setStatus($mask);

        if ($date) {
            $queue->setApplicationDate($date);
        }
        $this->om->persist($queue);

        $this->eventDispatcher->dispatch(new LogSessionQueueCreateEvent($queue), 'log');

        $this->om->endFlushSuite();

        return $queue;
    }

    /**
     * Validates user registration to session.
     *
     * @param int $type
     */
    public function validateSessionQueueByType(CourseSessionRegistrationQueue $queue, User $user, $type)
    {
        $mask = $queue->getStatus();

        if ($type === ($mask & $type)) {
            $this->om->startFlushSuite();

            switch ($type) {
                case CourseSessionRegistrationQueue::WAITING:
                    $mask -= CourseSessionRegistrationQueue::WAITING;
                    $queue->setValidationDate(new \DateTime());
                    $queue->setStatus($mask);
                    $this->om->persist($queue);

                    $this->eventDispatcher->dispatch(new LogSessionQueueValidateEvent($queue), 'log');
                    break;
                case CourseSessionRegistrationQueue::WAITING_USER:
                    $mask -= CourseSessionRegistrationQueue::WAITING_USER;
                    $queue->setUserValidationDate(new \DateTime());
                    $queue->setStatus($mask);
                    $this->om->persist($queue);

                    $this->eventDispatcher->dispatch(new LogSessionQueueUserValidateEvent($queue), 'log');
                    break;
                case CourseSessionRegistrationQueue::WAITING_VALIDATOR:
                    $mask -= CourseSessionRegistrationQueue::WAITING_VALIDATOR;
                    $queue->setValidatorValidationDate(new \DateTime());
                    $queue->setValidator($user);
                    $queue->setStatus($mask);
                    $this->om->persist($queue);

                    $this->eventDispatcher->dispatch(new LogSessionQueueValidatorValidateEvent($queue), 'log');
                    break;
                case CourseSessionRegistrationQueue::WAITING_ORGANIZATION:
                    $mask -= CourseSessionRegistrationQueue::WAITING_ORGANIZATION;
                    $queue->setOrganizationValidationDate(new \DateTime());
                    $queue->setOrganizationAdmin($user);
                    $queue->setStatus($mask);
                    $this->om->persist($queue);

                    $this->eventDispatcher->dispatch(new LogSessionQueueOrganizationValidateEvent($queue), 'log');
                    break;
            }
            $this->om->endFlushSuite();
        }
        if (0 === $mask) {
            $this->validateSessionQueue($queue);
        }
    }

    /**
     * Registers user to session from session queue.
     */
    public function validateSessionQueue(CourseSessionRegistrationQueue $queue)
    {
        $session = $queue->getSession();
        $user = $queue->getUser();

        if ($this->checkSessionCapacity($session)) {
            $this->om->startFlushSuite();
            $this->addUsersToSession($session, [$user]);
            $this->deleteEntities([$queue]);
            $this->om->endFlushSuite();
        }
    }

    /**
     * Deletes list of entities.
     */
    public function deleteEntities(array $entities)
    {
        $this->om->startFlushSuite();

        foreach ($entities as $entity) {
            $this->om->remove($entity);
        }
        $this->om->endFlushSuite();
    }

    /**
     * Generates a workspace from CourseSession.
     *
     * @return Workspace
     */
    public function generateWorkspace(CourseSession $session)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $course = $session->getCourse();

        $model = $course->getWorkspaceModel();
        $name = $course->getTitle().' ['.$session->getName().']';
        $code = $this->generateWorkspaceCode($course->getCode());

        $workspace = new Workspace();
        $workspace->setCreator($user);
        $workspace->setName($name);
        $workspace->setCode($code);
        $workspace->setDescription($course->getDescription());

        if (is_null($model)) {
            $defaultModel = $this->workspaceManager->getDefaultModel();
            $workspace = $this->workspaceManager->copy($defaultModel, $workspace);
        } else {
            $workspace = $this->workspaceManager->copy($model, $workspace);
        }
        $workspace->setWorkspaceType(0);
        $workspace->setAccessibleFrom($session->getStartDate());
        $workspace->setAccessibleUntil($session->getEndDate());
        $this->om->persist($workspace);

        return $workspace;
    }

    /**
     * Gets/generates workspace role for session depending on given role name and type.
     *
     * @param string $roleName
     * @param string $type
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function generateRoleForSession(Workspace $workspace, $roleName, $type = 'learner')
    {
        if (empty($roleName)) {
            if ('manager' === $type) {
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
                $uuid = $workspace->getUuid();
                $wsRoleName = 'ROLE_WS_'.strtoupper($roleName).'_'.$uuid;

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

    /**
     * Sets all sessions from a course (excepted given one) as non-default.
     *
     * @param bool $noFlush
     */
    public function resetDefaultSessionByCourse(Course $course, CourseSession $session = null, $noFlush = true)
    {
        $defaultSessions = $this->courseSessionRepo->findBy(['course' => $course, 'defaultSession' => true]);

        foreach ($defaultSessions as $defaultSession) {
            if ($defaultSession !== $session) {
                $defaultSession->setDefaultSession(false);
                $this->om->persist($defaultSession);
            }
        }
        if (!$noFlush) {
            $this->om->flush();
        }
    }

    /**
     * Checks user limit of a session to know if there is still place for the given number of users.
     *
     * @param int $count
     *
     * @return bool
     */
    public function checkSessionCapacity(CourseSession $session, $count = 1)
    {
        $hasPlace = true;
        $maxUsers = $session->getMaxUsers();

        if ($maxUsers) {
            $sessionUsers = $this->sessionUserRepo->findBy(['session' => $session, 'userType' => CourseSessionUser::TYPE_LEARNER]);
            $sessionGroups = $this->sessionGroupRepo->findBy(['session' => $session, 'groupType' => CourseSessionGroup::TYPE_LEARNER]);
            $groups = [];

            foreach ($sessionGroups as $sessionGroup) {
                $groups[] = $sessionGroup->getGroup();
            }
            $nbUsers = count($sessionUsers);

            foreach ($groups as $group) {
                $nbUsers += count($group->getUsers()->toArray());
            }
            $hasPlace = $nbUsers + $count <= $maxUsers;
        }

        return $hasPlace;
    }

    /**
     * Checks user limit of a session event to know if there is still place for the given number of users.
     *
     * @param int $count
     *
     * @return bool
     */
    public function checkSessionEventCapacity(SessionEvent $event, $count = 1)
    {
        $hasPlace = true;
        $maxUsers = $event->getMaxUsers();

        if (CourseSession::REGISTRATION_AUTO !== $event->getRegistrationType() && $maxUsers) {
            $eventUsers = $this->sessionEventUserRepo->findBy(['sessionEvent' => $event, 'registrationStatus' => SessionEventUser::REGISTERED]);
            $nbUsers = count($eventUsers);
            $hasPlace = $nbUsers + $count <= $maxUsers;
        }

        return $hasPlace;
    }

    /**
     * Generates and sends session certificate for given users.
     */
    public function generateSessionCertificates(CourseSession $session, array $users, Template $template = null)
    {
        $authenticatedUser = $this->tokenStorage->getToken()->getUser();
        $course = $session->getCourse();

        if ('anon.' !== $authenticatedUser) {
            $data = [];
            $trainersList = '';
            $sessionTrainers = $this->sessionUserRepo->findBy([
                'session' => $session,
                'userType' => CourseSessionUser::TYPE_TEACHER,
            ]);

            if (0 < count($sessionTrainers)) {
                $trainersList = '<ul>';

                foreach ($sessionTrainers as $sessionTrainer) {
                    $user = $sessionTrainer->getUser();
                    $trainersList .= '<li>'.$user->getFirstName().' '.$user->getLastName().'</li>';
                }
                $trainersList .= '</ul>';
            }
            $basicPlaceholders = [
                'course_title' => $course->getTitle(),
                'course_code' => $course->getCode(),
                'course_description' => $course->getDescription(),
                'session_name' => $session->getName(),
                'session_description' => $session->getDescription(),
                'session_start' => $session->getStartDate()->format('Y-m-d'),
                'session_end' => $session->getEndDate()->format('Y-m-d'),
                'session_trainers' => $trainersList,
            ];

            foreach ($users as $user) {
                $locale = $user->getLocale();

                $eventsList = '';
                $events = $this->sessionEventRepo->findSessionEventsBySessionAndUserAndRegistrationStatus(
                    $session,
                    $user,
                    SessionEventUser::REGISTERED
                );

                if (0 < count($events)) {
                    $eventsList = '<ul>';

                    foreach ($events as $event) {
                        $eventsList .= '<li>'.
                            $event->getName().
                            ' ['.$event->getStartDate()->format('d/m/Y H:i').
                            ' -> '.
                            $event->getEndDate()->format('d/m/Y H:i').']';
                        $location = $event->getLocation();

                        if ($location) {
                            $locationHtml = '<br>'.$location->getStreet().', '.$location->getStreetNumber();

                            if ($location->getBoxNumber()) {
                                $locationHtml .= '/'.$location->getBoxNumber();
                            }
                            $locationHtml .= '<br>'.$location->getPc().' '.$location->getTown().'<br>'.$location->getCountry();

                            if ($location->getPhone()) {
                                $locationHtml .= '<br>'.$location->getPhone();
                            }
                            $eventsList .= $locationHtml;
                        }
                        $eventsList .= $event->getLocationExtra();
                    }
                    $eventsList .= '</ul>';
                }
                $placeholders = array_merge($basicPlaceholders, [
                    'first_name' => $user->getFirstName(),
                    'last_name' => $user->getLastName(),
                    'username' => $user->getUsername(),
                    'events_list' => $eventsList,
                ]);
                $certificateContent = $template ?
                    $this->templateManager->getTemplateContent($template, $placeholders) :
                    $this->templateManager->getTemplate('session_certificate', $placeholders, $locale);
                $pdfName = $session->getName().'-'.$user->getUsername();
                $pdf = $this->pdfManager->create($certificateContent, $pdfName, $authenticatedUser, 'session_certificate');
                $pdfLink = $this->router->generate('claro_pdf_download', ['pdf' => $pdf->getGuid()], true);

                $placeholders['certificate_link'] = $pdfLink;
                $title = $this->templateManager->getTemplate('session_certificate_mail', $placeholders, $locale, 'title');
                $content = $this->templateManager->getTemplate('session_certificate_mail', $placeholders, $locale);
                $this->mailManager->send($title, $content, [$user]);
                $data[] = ['user' => $user->getFirstName().' '.$user->getLastName(), 'pdf' => $pdfLink];
            }
            $links = '<ul>';

            foreach ($data as $row) {
                $links .= '<li><a href="'.$row['pdf'].'">'.$row['user'].'</a></li>';
            }
            $links .= '</ul>';
            $adminTitle = $this->templateManager->getTemplate(
                'admin_certificate_mail',
                ['certificates_link' => $links],
                $authenticatedUser->getLocale(),
                'title'
            );
            $adminContent = $this->templateManager->getTemplate(
                'admin_certificate_mail',
                ['certificates_link' => $links],
                $authenticatedUser->getLocale()
            );
            $this->mailManager->send($adminTitle, $adminContent, [$authenticatedUser]);
        }
    }

    /**
     * Generates certificates for all session learners.
     */
    public function generateAllSessionCertificates(CourseSession $session, Template $template = null)
    {
        $sessionLearners = $this->sessionUserRepo->findBy([
            'session' => $session,
            'userType' => CourseSessionUser::TYPE_LEARNER,
        ]);
        $sessionGroups = $this->sessionGroupRepo->findBy([
            'session' => $session,
            'groupType' => CourseSessionGroup::TYPE_LEARNER,
        ]);
        $users = [];

        foreach ($sessionLearners as $sessionLearner) {
            $user = $sessionLearner->getUser();
            $users[$user->getUuid()] = $user;
        }
        foreach ($sessionGroups as $sessionGroup) {
            $group = $sessionGroup->getGroup();
            $groupUsers = $group->getUsers();

            foreach ($groupUsers as $user) {
                $users[$user->getUuid()] = $user;
            }
        }

        $this->generateSessionCertificates($session, $users, $template);
    }

    /**
     * Generates and sends session event certificate for given users.
     */
    public function generateEventCertificates(SessionEvent $event, array $users, Template $template = null)
    {
        $authenticatedUser = $this->tokenStorage->getToken()->getUser();
        $session = $event->getSession();
        $course = $session->getCourse();

        if ('anon.' !== $authenticatedUser) {
            $data = [];
            $trainersList = '';
            $eventTrainers = $event->getTutors();

            if (0 < count($eventTrainers)) {
                $trainersList = '<ul>';

                foreach ($eventTrainers as $eventTrainer) {
                    $user = $eventTrainer->getUser();
                    $trainersList .= '<li>'.$user->getFirstName().' '.$user->getLastName().'</li>';
                }
                $trainersList .= '</ul>';
            }
            $location = $event->getLocation();
            $locationName = '';
            $locationAddress = '';

            if ($location) {
                $locationName = $location->getName();
                $locationAddress = $location->getStreet().', '.$location->getStreetNumber();

                if ($location->getBoxNumber()) {
                    $locationAddress .= '/'.$location->getBoxNumber();
                }
                $locationAddress .= '<br>'.$location->getPc().' '.$location->getTown().'<br>'.$location->getCountry();

                if ($location->getPhone()) {
                    $locationAddress .= '<br>'.$location->getPhone();
                }
            }
            $basicPlaceholders = [
                'course_title' => $course->getTitle(),
                'course_code' => $course->getCode(),
                'course_description' => $course->getDescription(),
                'session_name' => $session->getName(),
                'session_description' => $session->getDescription(),
                'session_start' => $session->getStartDate()->format('Y-m-d'),
                'session_end' => $session->getEndDate()->format('Y-m-d'),
                'event_name' => $event->getName(),
                'event_description' => $event->getDescription(),
                'event_start' => $event->getStartDate()->format('Y-m-d H:i'),
                'event_end' => $event->getEndDate()->format('Y-m-d H:i'),
                'event_location_name' => $locationName,
                'event_location_address' => $locationAddress,
                'event_location_extra' => $event->getLocationExtra(),
                'event_trainers' => $trainersList,
            ];

            foreach ($users as $user) {
                $locale = $user->getLocale();
                $placeholders = array_merge($basicPlaceholders, [
                    'first_name' => $user->getFirstName(),
                    'last_name' => $user->getLastName(),
                    'username' => $user->getUsername(),
                ]);
                $certificateContent = $template ?
                    $this->templateManager->getTemplateContent($template, $placeholders) :
                    $this->templateManager->getTemplate('session_event_certificate', $placeholders, $locale);
                $pdfName = $session->getName().'-'.$user->getUsername();
                $pdf = $this->pdfManager->create($certificateContent, $pdfName, $authenticatedUser, 'session_event_certificate');
                $pdfLink = $this->router->generate('claro_pdf_download', ['pdf' => $pdf->getGuid()], true);

                $placeholders['certificate_link'] = $pdfLink;
                $title = $this->templateManager->getTemplate('session_event_certificate_mail', $placeholders, $locale, 'title');
                $content = $this->templateManager->getTemplate('session_event_certificate_mail', $placeholders, $locale);
                $this->mailManager->send($title, $content, [$user]);
                $data[] = ['user' => $user->getFirstName().' '.$user->getLastName(), 'pdf' => $pdfLink];
            }
            $links = '<ul>';

            foreach ($data as $row) {
                $links .= '<li><a href="'.$row['pdf'].'">'.$row['user'].'</a></li>';
            }
            $links .= '</ul>';
            $adminTitle = $this->templateManager->getTemplate(
                'admin_certificate_mail',
                ['certificates_link' => $links],
                $authenticatedUser->getLocale(),
                'title'
            );
            $adminContent = $this->templateManager->getTemplate(
                'admin_certificate_mail',
                ['certificates_link' => $links],
                $authenticatedUser->getLocale()
            );
            $this->mailManager->send($adminTitle, $adminContent, [$authenticatedUser]);
        }
    }

    /**
     * Generates certificates for all session event users.
     */
    public function generateAllEventCertificates(SessionEvent $event, Template $template = null)
    {
        $eventUsers = $this->sessionEventUserRepo->findBy([
            'sessionEvent' => $event,
            'registrationStatus' => SessionEventUser::REGISTERED,
        ]);
        $users = array_map(function (SessionEventUser $eventUser) {
            return $eventUser->getUser();
        }, $eventUsers);

        $this->generateEventCertificates($event, $users, $template);
    }

    /**
     * Sends invitation to all session learners.
     */
    public function inviteAllSessionLearners(CourseSession $session, Template $template = null)
    {
        $sessionLearners = $this->sessionUserRepo->findBy([
            'session' => $session,
            'userType' => CourseSessionUser::TYPE_LEARNER,
        ]);
        $sessionGroups = $this->sessionGroupRepo->findBy([
            'session' => $session,
            'groupType' => CourseSessionGroup::TYPE_LEARNER,
        ]);
        $users = [];

        foreach ($sessionLearners as $sessionLearner) {
            $user = $sessionLearner->getUser();
            $users[$user->getUuid()] = $user;
        }
        foreach ($sessionGroups as $sessionGroup) {
            $group = $sessionGroup->getGroup();
            $groupUsers = $group->getUsers();

            foreach ($groupUsers as $user) {
                $users[$user->getUuid()] = $user;
            }
        }

        $this->sendSessionInvitation($session, $users, $template);
    }

    /**
     * Sends invitation to session to given users.
     */
    public function sendSessionInvitation(CourseSession $session, array $users, Template $template = null)
    {
        $course = $session->getCourse();
        $trainersList = '';
        $sessionTrainers = $this->sessionUserRepo->findBy([
            'session' => $session,
            'userType' => CourseSessionUser::TYPE_TEACHER,
        ]);

        if (0 < count($sessionTrainers)) {
            $trainersList = '<ul>';

            foreach ($sessionTrainers as $sessionTrainer) {
                $user = $sessionTrainer->getUser();
                $trainersList .= '<li>'.$user->getFirstName().' '.$user->getLastName().'</li>';
            }
            $trainersList .= '</ul>';
        }
        $basicPlaceholders = [
            'course_title' => $course->getTitle(),
            'course_code' => $course->getCode(),
            'course_description' => $course->getDescription(),
            'session_name' => $session->getName(),
            'session_description' => $session->getDescription(),
            'session_start' => $session->getStartDate()->format('Y-m-d'),
            'session_end' => $session->getEndDate()->format('Y-m-d'),
            'session_trainers' => $trainersList,
        ];

        foreach ($users as $user) {
            $locale = $user->getLocale();
            $placeholders = array_merge($basicPlaceholders, [
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'username' => $user->getUsername(),
            ]);
            $title = $template ?
                $this->templateManager->getTemplateContent($template, $placeholders, 'title') :
                $this->templateManager->getTemplate('session_invitation', $placeholders, $locale);
            $content = $template ?
                $this->templateManager->getTemplateContent($template, $placeholders) :
                $this->templateManager->getTemplate('session_invitation', $placeholders, $locale);
            $this->mailManager->send($title, $content, [$user]);
        }
    }

    /**
     * Sends invitation to all session event users.
     */
    public function inviteAllSessionEventUsers(SessionEvent $event, Template $template = null)
    {
        $eventUsers = $this->sessionEventUserRepo->findBy([
            'sessionEvent' => $event,
            'registrationStatus' => SessionEventUser::REGISTERED,
        ]);
        $users = array_map(function (SessionEventUser $eventUser) {
            return $eventUser->getUser();
        }, $eventUsers);

        $this->sendEventInvitation($event, $users, $template);
    }

    /**
     * Sends invitation to session event to given users.
     */
    public function sendEventInvitation(SessionEvent $event, array $users, Template $template = null)
    {
        $session = $event->getSession();
        $course = $session->getCourse();

        $trainersList = '';
        $eventTrainers = $event->getTutors();

        if (0 < count($eventTrainers)) {
            $trainersList = '<ul>';

            foreach ($eventTrainers as $eventTrainer) {
                $user = $eventTrainer->getUser();
                $trainersList .= '<li>'.$user->getFirstName().' '.$user->getLastName().'</li>';
            }
            $trainersList .= '</ul>';
        }
        $location = $event->getLocation();
        $locationName = '';
        $locationAddress = '';

        if ($location) {
            $locationName = $location->getName();
            $locationAddress = $location->getStreet().', '.$location->getStreetNumber();

            if ($location->getBoxNumber()) {
                $locationAddress .= '/'.$location->getBoxNumber();
            }
            $locationAddress .= '<br>'.$location->getPc().' '.$location->getTown().'<br>'.$location->getCountry();

            if ($location->getPhone()) {
                $locationAddress .= '<br>'.$location->getPhone();
            }
        }
        $basicPlaceholders = [
            'course_title' => $course->getTitle(),
            'course_code' => $course->getCode(),
            'course_description' => $course->getDescription(),
            'session_name' => $session->getName(),
            'session_description' => $session->getDescription(),
            'session_start' => $session->getStartDate()->format('Y-m-d'),
            'session_end' => $session->getEndDate()->format('Y-m-d'),
            'event_name' => $event->getName(),
            'event_description' => $event->getDescription(),
            'event_start' => $event->getStartDate()->format('Y-m-d H:i'),
            'event_end' => $event->getEndDate()->format('Y-m-d H:i'),
            'event_location_name' => $locationName,
            'event_location_address' => $locationAddress,
            'event_location_extra' => $event->getLocationExtra(),
            'event_trainers' => $trainersList,
        ];

        foreach ($users as $user) {
            $locale = $user->getLocale();
            $placeholders = array_merge($basicPlaceholders, [
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'username' => $user->getUsername(),
            ]);
            $title = $template ?
                $this->templateManager->getTemplateContent($template, $placeholders, 'title') :
                $this->templateManager->getTemplate('session_event_invitation', $placeholders, $locale);
            $content = $template ?
                $this->templateManager->getTemplateContent($template, $placeholders) :
                $this->templateManager->getTemplate('session_event_invitation', $placeholders, $locale);
            $this->mailManager->send($title, $content, [$user]);
        }
    }

    /**
     * Generates an unique workspace code from given one by iterating it.
     *
     * @param string $code
     *
     * @return string
     */
    private function generateWorkspaceCode($code)
    {
        $workspaceCodes = $this->workspaceRepo->findWorkspaceCodesWithPrefix($code);
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
}
