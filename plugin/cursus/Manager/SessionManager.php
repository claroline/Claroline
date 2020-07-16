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

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CursusBundle\Entity\AbstractRegistrationQueue;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\CourseSessionGroup;
use Claroline\CursusBundle\Entity\CourseSessionRegistrationQueue;
use Claroline\CursusBundle\Entity\CourseSessionUser;
use Claroline\CursusBundle\Entity\SessionEvent;
use Claroline\CursusBundle\Entity\SessionEventUser;
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

class SessionManager
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var MailManager */
    private $mailManager;
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;
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
    /** @var SessionEventManager */
    private $sessionEventManager;

    private $courseSessionRepo;
    private $sessionUserRepo;
    private $sessionGroupRepo;
    /** @var SessionEventRepository */
    private $sessionEventRepo;
    private $sessionQueueRepo;

    /**
     * SessionManager constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param MailManager              $mailManager
     * @param ObjectManager            $om
     * @param Crud                     $crud
     * @param RoleManager              $roleManager
     * @param UrlGeneratorInterface    $router
     * @param TemplateManager          $templateManager
     * @param TokenStorageInterface    $tokenStorage
     * @param WorkspaceManager         $workspaceManager
     * @param SessionEventManager      $sessionEventManager
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        MailManager $mailManager,
        ObjectManager $om,
        Crud $crud,
        RoleManager $roleManager,
        UrlGeneratorInterface $router,
        TemplateManager $templateManager,
        TokenStorageInterface $tokenStorage,
        WorkspaceManager $workspaceManager,
        SessionEventManager $sessionEventManager
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->mailManager = $mailManager;
        $this->om = $om;
        $this->crud = $crud;
        $this->roleManager = $roleManager;
        $this->router = $router;
        $this->templateManager = $templateManager;
        $this->tokenStorage = $tokenStorage;
        $this->workspaceManager = $workspaceManager;
        $this->sessionEventManager = $sessionEventManager;

        $this->courseSessionRepo = $om->getRepository(CourseSession::class);
        $this->sessionUserRepo = $om->getRepository(CourseSessionUser::class);
        $this->sessionGroupRepo = $om->getRepository(CourseSessionGroup::class);
        $this->sessionEventRepo = $om->getRepository(SessionEvent::class);
        $this->sessionQueueRepo = $om->getRepository(CourseSessionRegistrationQueue::class);
    }

    public function setDefaultSession(Course $course, CourseSession $session = null)
    {
        /** @var CourseSession[] $defaultSessions */
        $defaultSessions = $this->courseSessionRepo->findBy(['course' => $course, 'defaultSession' => true]);

        foreach ($defaultSessions as $defaultSession) {
            if ($defaultSession !== $session) {
                $defaultSession->setDefaultSession(false);
                $this->om->persist($defaultSession);
            }
        }

        $this->om->flush();
    }

    public function createDefaultEvent(CourseSession $session)
    {
        $this->om->startFlushSuite();

        /** @var SessionEvent $event */
        $event = $this->crud->create(SessionEvent::class, [
            'name' => $session->getName(),
            'code' => $session->getCode(),
            'meta' => [
                'type' => SessionEvent::TYPE_NONE,
            ],
            'restrictions' => [
                'dates' => DateRangeNormalizer::normalize($session->getStartDate(), $session->getEndDate()),
            ],
            'registration' => [
                'registrationType' => $session->getEventRegistrationType(),
            ],
        ], [Crud::THROW_EXCEPTION]);

        $event->setSession($session);
        $this->om->persist($event);
        $this->om->flush();

        $this->om->startFlushSuite();

        return $event;
    }

    /**
     * Generates a workspace from CourseSession.
     *
     * @param CourseSession $session
     *
     * @return Workspace
     */
    public function generateWorkspace(CourseSession $session)
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $course = $session->getCourse();

        $model = $course->getWorkspaceModel();

        $workspace = new Workspace();
        $workspace->setCreator($user);
        $workspace->setName($course->getTitle().' ['.$session->getName().']');
        $workspace->setCode($this->workspaceManager->getUniqueCode($course->getCode()));
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
     * Adds users to a session.
     *
     * @param CourseSession $session
     * @param array         $users
     * @param int           $type
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

                $this->eventDispatcher->dispatch('log', new LogSessionUserRegistrationEvent($sessionUser));

                $results[] = $sessionUser;
            }
        }
        if (CourseSessionUser::TYPE_LEARNER === $type) {
            $events = $session->getEvents();

            foreach ($events as $event) {
                if (CourseSession::REGISTRATION_AUTO === $event->getRegistrationType() && !$event->isTerminated()) {
                    $this->sessionEventManager->addUsersToSessionEvent($event, $users);
                }
            }
        }
        $this->om->endFlushSuite();

        return $results;
    }

    /**
     * Adds groups to a session.
     *
     * @param CourseSession $session
     * @param array         $groups
     * @param int           $type
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

                $this->eventDispatcher->dispatch('log', new LogSessionGroupRegistrationEvent($sessionGroup));

                $results[] = $sessionGroup;
            }
        }
        $this->om->endFlushSuite();

        return $results;
    }

    /**
     * Registers an user to a session if allowed.
     *
     * @param CourseSession $session
     * @param User          $user
     * @param bool          $skipValidation
     *
     * @return CourseSessionUser|CourseSessionRegistrationQueue|array
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
        }

        if ($this->checkSessionCapacity($session)) {
            return $this->addUsersToSession($session, [$user]);
        }

        return null;
    }

    /**
     * Creates a queue for session and user.
     *
     * @param CourseSession $session
     * @param User          $user
     * @param int           $mask
     * @param \DateTime     $date
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

        $this->eventDispatcher->dispatch('log', new LogSessionQueueCreateEvent($queue));

        $this->om->endFlushSuite();

        return $queue;
    }

    /**
     * Validates user registration to session.
     *
     * @param CourseSessionRegistrationQueue $queue
     * @param User                           $user
     * @param int                            $type
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

                    $this->eventDispatcher->dispatch('log', new LogSessionQueueValidateEvent($queue));
                    break;
                case CourseSessionRegistrationQueue::WAITING_USER:
                    $mask -= CourseSessionRegistrationQueue::WAITING_USER;
                    $queue->setUserValidationDate(new \DateTime());
                    $queue->setStatus($mask);
                    $this->om->persist($queue);

                    $this->eventDispatcher->dispatch('log', new LogSessionQueueUserValidateEvent($queue));
                    break;
                case CourseSessionRegistrationQueue::WAITING_VALIDATOR:
                    $mask -= CourseSessionRegistrationQueue::WAITING_VALIDATOR;
                    $queue->setValidatorValidationDate(new \DateTime());
                    $queue->setValidator($user);
                    $queue->setStatus($mask);
                    $this->om->persist($queue);

                    $this->eventDispatcher->dispatch('log', new LogSessionQueueValidatorValidateEvent($queue));
                    break;
                case CourseSessionRegistrationQueue::WAITING_ORGANIZATION:
                    $mask -= CourseSessionRegistrationQueue::WAITING_ORGANIZATION;
                    $queue->setOrganizationValidationDate(new \DateTime());
                    $queue->setOrganizationAdmin($user);
                    $queue->setStatus($mask);
                    $this->om->persist($queue);

                    $this->eventDispatcher->dispatch('log', new LogSessionQueueOrganizationValidateEvent($queue));
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
     *
     * @param CourseSessionRegistrationQueue $queue
     */
    public function validateSessionQueue(CourseSessionRegistrationQueue $queue)
    {
        $session = $queue->getSession();
        $user = $queue->getUser();

        if ($this->checkSessionCapacity($session)) {
            $this->om->startFlushSuite();
            $this->addUsersToSession($session, [$user]);
            $this->om->remove($queue);
            $this->om->endFlushSuite();
        }
    }

    /**
     * Gets/generates workspace role for session depending on given role name and type.
     *
     * @param Workspace $workspace
     * @param string    $roleName
     * @param string    $type
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
     * Checks user limit of a session to know if there is still place for the given number of users.
     *
     * @param CourseSession $session
     * @param int           $count
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
     * Generates and sends session certificate for given users.
     *
     * @param CourseSession $session
     * @param array         $users
     * @param Template|null $template
     */
    public function generateSessionCertificates(CourseSession $session, array $users, Template $template = null)
    {
        $authenticatedUser = $this->tokenStorage->getToken()->getUser();
        $course = $session->getCourse();

        if ('anon.' !== $authenticatedUser) {
            $data = [];
            $trainersList = '';
            /** @var CourseSessionUser[] $sessionTrainers */
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
                /** @var SessionEvent[] $events */
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
     *
     * @param CourseSession $session
     * @param Template|null $template
     */
    public function generateAllSessionCertificates(CourseSession $session, Template $template = null)
    {
        /** @var CourseSessionUser[] $sessionLearners */
        $sessionLearners = $this->sessionUserRepo->findBy([
            'session' => $session,
            'userType' => CourseSessionUser::TYPE_LEARNER,
        ]);
        /** @var CourseSessionGroup[] $sessionGroups */
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
     * Sends invitation to all session learners.
     *
     * @param CourseSession $session
     * @param Template|null $template
     */
    public function inviteAllSessionLearners(CourseSession $session, Template $template = null)
    {
        /** @var CourseSessionUser[] $sessionLearners */
        $sessionLearners = $this->sessionUserRepo->findBy([
            'session' => $session,
            'userType' => CourseSessionUser::TYPE_LEARNER,
        ]);
        /** @var CourseSessionGroup[] $sessionGroups */
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
     *
     * @param CourseSession $session
     * @param array         $users
     * @param Template|null $template
     */
    public function sendSessionInvitation(CourseSession $session, array $users, Template $template = null)
    {
        $course = $session->getCourse();
        $trainersList = '';
        /** @var CourseSessionUser[] $sessionTrainers */
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
}
