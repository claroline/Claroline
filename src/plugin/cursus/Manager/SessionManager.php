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
use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\CatalogEvents\MessageEvents;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Claroline\CursusBundle\Entity\Registration\SessionGroup;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Event\Log\LogSessionGroupRegistrationEvent;
use Claroline\CursusBundle\Event\Log\LogSessionGroupUnregistrationEvent;
use Claroline\CursusBundle\Event\Log\LogSessionUserRegistrationEvent;
use Claroline\CursusBundle\Event\Log\LogSessionUserUnregistrationEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SessionManager
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var TranslatorInterface */
    private $translator;
    /** @var ObjectManager */
    private $om;
    /** @var UrlGeneratorInterface */
    private $router;
    /** @var Crud */
    private $crud;
    /** @var PlatformManager */
    private $platformManager;
    /** @var RoleManager */
    private $roleManager;
    /** @var RoutingHelper */
    private $routingHelper;
    /** @var TemplateManager */
    private $templateManager;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var WorkspaceManager */
    private $workspaceManager;
    /** @var EventManager */
    private $sessionEventManager;

    private $sessionRepo;
    private $sessionUserRepo;
    private $sessionGroupRepo;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator,
        ObjectManager $om,
        UrlGeneratorInterface $router,
        Crud $crud,
        PlatformManager $platformManager,
        RoleManager $roleManager,
        RoutingHelper $routingHelper,
        TemplateManager $templateManager,
        TokenStorageInterface $tokenStorage,
        WorkspaceManager $workspaceManager,
        EventManager $sessionEventManager
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->translator = $translator;
        $this->om = $om;
        $this->router = $router;
        $this->crud = $crud;
        $this->platformManager = $platformManager;
        $this->roleManager = $roleManager;
        $this->routingHelper = $routingHelper;
        $this->templateManager = $templateManager;
        $this->tokenStorage = $tokenStorage;
        $this->workspaceManager = $workspaceManager;
        $this->sessionEventManager = $sessionEventManager;

        $this->sessionRepo = $om->getRepository(Session::class);
        $this->sessionUserRepo = $om->getRepository(SessionUser::class);
        $this->sessionGroupRepo = $om->getRepository(SessionGroup::class);
    }

    public function setDefaultSession(Course $course, Session $session = null)
    {
        /** @var Session[] $defaultSessions */
        $defaultSessions = $this->sessionRepo->findBy(['course' => $course, 'defaultSession' => true]);

        foreach ($defaultSessions as $defaultSession) {
            if ($defaultSession !== $session) {
                $defaultSession->setDefaultSession(false);
                $this->om->persist($defaultSession);
            }
        }

        $this->om->flush();
    }

    public function generateFromTemplate(Session $session, string $locale)
    {
        $placeholders = [
            'session_url' => $this->routingHelper->desktopUrl('trainings').'/catalog/'.$session->getCourse()->getSlug().'/'.$session->getUuid(),
            'session_name' => $session->getName(),
            'session_code' => $session->getCode(),
            'session_description' => $session->getDescription(),
            'session_poster' => $session->getPoster() ? '<img src="'.$this->platformManager->getUrl().'/'.$session->getPoster().'" style="max-width: 100%;" />' : '',
            'session_public_registration' => $this->translator->trans($session->getPublicRegistration() ? 'yes' : 'no', [], 'platform'),
            'session_max_users' => $session->getMaxUsers(),
            'session_start' => $session->getStartDate()->format('d/m/Y'),
            'session_end' => $session->getEndDate()->format('d/m/Y'),
        ];

        return $this->templateManager->getTemplate('training_session', $placeholders, $locale);
    }

    /**
     * Generates a workspace from CourseSession.
     */
    public function generateWorkspace(Session $session): Workspace
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $course = $session->getCourse();
        if (!empty($course->getWorkspaceModel())) {
            $model = $course->getWorkspaceModel();
        } else {
            $model = $this->workspaceManager->getDefaultModel();
        }

        $workspace = new Workspace();
        $workspace->setName($session->getName());
        $workspace->setCode($this->workspaceManager->getUniqueCode($session->getCode()));

        $workspace = $this->workspaceManager->copy($model, $workspace);

        $workspace->setCreator($user);

        $workspace->setDescription($session->getDescription());
        $workspace->setPoster($session->getPoster());
        $workspace->setThumbnail($session->getThumbnail());
        $workspace->setAccessibleFrom($session->getStartDate());
        $workspace->setAccessibleUntil($session->getEndDate());
        $workspace->setHidden($course->isHidden());

        $this->om->persist($workspace);

        return $workspace;
    }

    /**
     * Adds users to a session.
     */
    public function addUsers(Session $session, array $users, string $type = AbstractRegistration::LEARNER, bool $validated = false): array
    {
        $results = [];

        $course = $session->getCourse();
        $registrationDate = new \DateTime();

        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $sessionUser = $this->sessionUserRepo->findOneBy(['session' => $session, 'user' => $user, 'type' => $type]);

            if (empty($sessionUser)) {
                $sessionUser = new SessionUser();
                $sessionUser->setSession($session);
                $sessionUser->setUser($user);
                $sessionUser->setType($type);
                $sessionUser->setDate($registrationDate);

                if (AbstractRegistration::TUTOR === $type) {
                    // no validation on tutors
                    $sessionUser->setValidated(true);
                    $sessionUser->setConfirmed(true);
                } else {
                    // set validations for users based on session config
                    $sessionUser->setValidated(!$session->getRegistrationValidation() || $validated);
                    $sessionUser->setConfirmed(!$session->getUserValidation());
                }

                // grant workspace role if registration is fully validated
                $role = AbstractRegistration::TUTOR === $type ? $session->getTutorRole() : $session->getLearnerRole();
                if ($role && $sessionUser->isValidated() && $sessionUser->isConfirmed() && !$user->hasRole($role->getName())) {
                    $this->crud->patch($user, 'role', Crud::COLLECTION_ADD, [$role], [Crud::NO_PERMISSIONS]);
                }
                $this->om->persist($sessionUser);

                $this->eventDispatcher->dispatch(new LogSessionUserRegistrationEvent($sessionUser), 'log');

                $results[] = $sessionUser;
            }
        }

        $this->checkUsersRegistration($session, $results);

        // TODO : what to do with this if he goes in pending state ?

        if ($session->getRegistrationMail()) {
            $this->sendSessionInvitation($session, array_map(function (SessionUser $sessionUser) {
                return $sessionUser->getUser();
            }, $results), AbstractRegistration::LEARNER === $type);
        }

        // registers users to linked trainings
        if ($course->getPropagateRegistration() && !empty($course->getChildren())) {
            foreach ($course->getChildren() as $childCourse) {
                $childSession = $childCourse->getDefaultSession();
                if ($childSession && !$childSession->isTerminated()) {
                    $this->addUsers($childSession, $users, $type);
                }
            }
        }

        $this->om->endFlushSuite();

        return $results;
    }

    /**
     * @param SessionUser[] $sessionUsers
     */
    public function removeUsers(Session $session, array $sessionUsers)
    {
        foreach ($sessionUsers as $sessionUser) {
            $this->om->remove($sessionUser);

            // unregister user from the linked workspace
            if ($session->getWorkspace()) {
                $this->workspaceManager->unregister($sessionUser->getUser(), $session->getWorkspace());
            }

            // unregister user from linked events
            $eventRegistrations = $this->sessionEventManager->getBySessionAndUser($session, $sessionUser->getUser());
            foreach ($eventRegistrations as $eventRegistration) {
                $this->sessionEventManager->removeUsers($eventRegistration->getEvent(), [$eventRegistration]);
            }

            $this->eventDispatcher->dispatch(new LogSessionUserUnregistrationEvent($sessionUser), 'log');
        }

        $this->om->flush();
    }

    /**
     * @param SessionUser[] $sessionUsers
     */
    public function moveUsers(Session $originalSession, Session $targetSession, array $sessionUsers, string $type = AbstractRegistration::LEARNER)
    {
        $this->om->startFlushSuite();

        // unregister users from current session
        $this->removeUsers($originalSession, $sessionUsers);

        // register to the new session
        $this->addUsers($targetSession, array_map(function (SessionUser $sessionUser) {
            return $sessionUser->getUser();
        }, $sessionUsers), $type, true);

        $this->om->endFlushSuite();
    }

    /**
     * @param SessionUser[] $sessionUsers
     */
    public function confirmUsers(Session $session, array $sessionUsers = []): array
    {
        // TODO : check capacity

        $this->om->startFlushSuite();

        foreach ($sessionUsers as $sessionUser) {
            $sessionUser->setConfirmed(true);
            $this->om->persist($sessionUser);
        }

        $this->checkUsersRegistration($session, $sessionUsers);

        $this->om->endFlushSuite();

        return $sessionUsers;
    }

    /**
     * @param SessionUser[] $sessionUsers
     */
    public function validateUsers(Session $session, array $sessionUsers = []): array
    {
        // TODO : check capacity

        $this->om->startFlushSuite();

        foreach ($sessionUsers as $sessionUser) {
            $sessionUser->setValidated(true);
            $this->om->persist($sessionUser);
        }

        $this->checkUsersRegistration($session, $sessionUsers);

        $this->om->endFlushSuite();

        return $sessionUsers;
    }

    /**
     * Adds groups to a session.
     */
    public function addGroups(Session $session, array $groups, string $type = AbstractRegistration::LEARNER): array
    {
        $results = [];
        $registrationDate = new \DateTime();

        $this->om->startFlushSuite();

        $users = [];
        foreach ($groups as $group) {
            $sessionGroup = $this->sessionGroupRepo->findOneBy([
                'session' => $session,
                'group' => $group,
                'type' => $type,
            ]);

            if (empty($sessionGroup)) {
                $sessionGroup = new SessionGroup();
                $sessionGroup->setSession($session);
                $sessionGroup->setGroup($group);
                $sessionGroup->setType($type);
                $sessionGroup->setDate($registrationDate);

                // Registers group to session workspace
                $role = AbstractRegistration::TUTOR === $type ? $session->getTutorRole() : $session->getLearnerRole();
                if ($role && !$group->hasRole($role->getName())) {
                    $this->crud->patch($group, 'role', Crud::COLLECTION_ADD, [$role], [Crud::NO_PERMISSIONS]);
                }

                $this->om->persist($sessionGroup);

                $this->eventDispatcher->dispatch(new LogSessionGroupRegistrationEvent($sessionGroup), 'log');

                $results[] = $sessionGroup;

                foreach ($group->getUsers() as $user) {
                    $users[$user->getUuid()] = $user;
                }
            }
        }

        // registers groups to linked trainings
        $course = $session->getCourse();
        if ($course->getPropagateRegistration() && !empty($course->getChildren())) {
            foreach ($course->getChildren() as $childCourse) {
                $childSession = $childCourse->getDefaultSession();
                if ($childSession && !$childSession->isTerminated()) {
                    $this->addGroups($childSession, $groups);
                }
            }
        }

        // registers groups to linked events
        $events = $session->getEvents();
        foreach ($events as $event) {
            if (Session::REGISTRATION_AUTO === $event->getRegistrationType() && !$event->isTerminated()) {
                $this->sessionEventManager->addGroups($event, $groups);
            }
        }

        if ($session->getRegistrationMail()) {
            $this->sendSessionInvitation($session, $users, false);
        }

        $this->om->endFlushSuite();

        return $results;
    }

    /**
     * @param SessionGroup[] $sessionGroups
     */
    public function removeGroups(Session $session, array $sessionGroups)
    {
        foreach ($sessionGroups as $sessionGroup) {
            $this->om->remove($sessionGroup);

            // unregister group from the linked workspace
            if ($session->getWorkspace()) {
                $this->workspaceManager->unregister($sessionGroup->getGroup(), $session->getWorkspace());
            }

            // unregister group from linked events
            $eventRegistrations = $this->sessionEventManager->getBySessionAndGroup($session, $sessionGroup->getGroup());
            foreach ($eventRegistrations as $eventRegistration) {
                $this->sessionEventManager->removeGroups($eventRegistration->getEvent(), [$eventRegistration]);
            }

            $this->eventDispatcher->dispatch(new LogSessionGroupUnregistrationEvent($sessionGroup), 'log');
        }

        $this->om->flush();
    }

    /**
     * @param SessionGroup[] $sessionGroups
     */
    public function moveGroups(Session $originalSession, Session $targetSession, array $sessionGroups, string $type = AbstractRegistration::LEARNER)
    {
        $this->om->startFlushSuite();

        // unregister users from current session
        $this->removeGroups($originalSession, $sessionGroups);

        // register to the new session
        $this->addGroups($targetSession, array_map(function (SessionGroup $sessionGroup) {
            return $sessionGroup->getGroup();
        }, $sessionGroups), $type);

        $this->om->endFlushSuite();
    }

    /**
     * Gets/generates workspace role for session depending on given role name and type.
     */
    public function generateRoleForSession(Workspace $workspace, string $roleName = null, string $type = 'learner'): Role
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
     */
    public function checkSessionCapacity(Session $session, $count = 1): bool
    {
        $maxUsers = $session->getMaxUsers();
        if ($maxUsers) {
            $nbUsers = $this->sessionRepo->countLearners($session);

            return $nbUsers + $count <= $maxUsers;
        }

        return true;
    }

    /**
     * Sends invitation to all session learners.
     */
    public function inviteAllSessionLearners(Session $session)
    {
        /** @var SessionUser[] $sessionLearners */
        $sessionLearners = $this->sessionUserRepo->findBy([
            'session' => $session,
            'type' => AbstractRegistration::LEARNER,
            'confirmed' => true,
            'validated' => true,
        ]);
        /** @var SessionGroup[] $sessionGroups */
        $sessionGroups = $this->sessionGroupRepo->findBy([
            'session' => $session,
            'type' => AbstractRegistration::LEARNER,
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

        $this->sendSessionInvitation($session, $users, false);
    }

    /**
     * Sends invitation to session to given users.
     */
    public function sendSessionInvitation(Session $session, array $users, bool $confirm = true)
    {
        $templateName = 'training_session_invitation';
        if ($confirm && $session->getUserValidation()) {
            $templateName = 'training_session_confirmation';
        }

        $course = $session->getCourse();
        $trainersList = '';
        /** @var SessionUser[] $sessionTrainers */
        $sessionTrainers = $this->sessionUserRepo->findBy([
            'session' => $session,
            'type' => AbstractRegistration::TUTOR,
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
            'course_name' => $course->getName(),
            'course_code' => $course->getCode(),
            'course_description' => $course->getDescription(),
            'session_url' => $this->routingHelper->desktopUrl('trainings').'/catalog/'.$session->getCourse()->getSlug().'/'.$session->getUuid(),
            'session_poster' => $session->getPoster() ? '<img src="'.$this->platformManager->getUrl().'/'.$session->getPoster().'" style="max-width: 100%;" />' : '',
            'session_name' => $session->getName(),
            'session_description' => $session->getDescription(),
            'session_start' => $session->getStartDate()->format('d/m/Y'),
            'session_end' => $session->getEndDate()->format('d/m/Y'),
            'session_trainers' => $trainersList,
            'registration_confirmation_url' => $this->router->generate('apiv2_cursus_session_self_confirm', ['id' => $session->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL), // TODO
        ];

        foreach ($users as $user) {
            $locale = $user->getLocale();
            $placeholders = array_merge($basicPlaceholders, [
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'username' => $user->getUsername(),
            ]);

            $title = $this->templateManager->getTemplate($templateName, $placeholders, $locale, 'title');
            $content = $this->templateManager->getTemplate($templateName, $placeholders, $locale);

            $this->eventDispatcher->dispatch(new SendMessageEvent(
                $content,
                $title,
                [$user]
            ), MessageEvents::MESSAGE_SENDING);
        }
    }

    /**
     * @param SessionUser[] $sessionUsers
     */
    private function checkUsersRegistration(Session $session, array $sessionUsers)
    {
        $fullyRegistered = [
            AbstractRegistration::TUTOR => [],
            AbstractRegistration::LEARNER => [],
        ];

        foreach ($sessionUsers as $sessionUser) {
            if ($sessionUser->isValidated() && $sessionUser->isConfirmed()) {
                // registration is fully validated
                $fullyRegistered[$sessionUser->getType()][] = $sessionUser->getUser();

                // grant workspace role if registration is fully validated
                $role = AbstractRegistration::TUTOR === $sessionUser->getType() ? $session->getTutorRole() : $session->getLearnerRole();
                if ($role && !$sessionUser->getUser()->hasRole($role->getName())) {
                    $this->crud->patch($sessionUser->getUser(), 'role', Crud::COLLECTION_ADD, [$role], [Crud::NO_PERMISSIONS]);
                }
            }
        }

        if (!empty($fullyRegistered[AbstractRegistration::TUTOR]) || !empty($fullyRegistered[AbstractRegistration::LEARNER])) {
            // registers users to linked events
            $events = $session->getEvents();
            foreach ($events as $event) {
                if (Session::REGISTRATION_AUTO === $event->getRegistrationType() && !$event->isTerminated()) {
                    if (!empty($fullyRegistered[AbstractRegistration::TUTOR])) {
                        $this->sessionEventManager->addUsers($event, $fullyRegistered[AbstractRegistration::TUTOR], AbstractRegistration::TUTOR);
                    }

                    if (!empty($fullyRegistered[AbstractRegistration::LEARNER])) {
                        $this->sessionEventManager->addUsers($event, $fullyRegistered[AbstractRegistration::LEARNER], AbstractRegistration::LEARNER);
                    }
                }
            }
        }
    }
}
