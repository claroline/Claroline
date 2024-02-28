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
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\CatalogEvents\MessageEvents;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Claroline\CursusBundle\Entity\Registration\SessionGroup;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Repository\SessionRepository;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SessionManager
{
    private SessionRepository $sessionRepo;
    private ObjectRepository $sessionUserRepo;
    private ObjectRepository $sessionGroupRepo;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly TranslatorInterface $translator,
        private readonly ObjectManager $om,
        private readonly UrlGeneratorInterface $router,
        private readonly Crud $crud,
        private readonly SerializerProvider $serializer,
        private readonly PlatformManager $platformManager,
        private readonly RoleManager $roleManager,
        private readonly RoutingHelper $routingHelper,
        private readonly TemplateManager $templateManager,
        private readonly WorkspaceManager $workspaceManager,
        private readonly EventManager $sessionEventManager
    ) {
        $this->sessionRepo = $om->getRepository(Session::class);
        $this->sessionUserRepo = $om->getRepository(SessionUser::class);
        $this->sessionGroupRepo = $om->getRepository(SessionGroup::class);
    }

    public function setDefaultSession(Course $course, Session $session = null): void
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

    public function generateFromTemplate(Session $session, string $locale): string
    {
        $placeholders = array_merge([
                'session_url' => $this->routingHelper->desktopUrl('trainings').'/catalog/'.$session->getCourse()->getSlug().'/'.$session->getUuid(),
                'session_name' => $session->getName(),
                'session_code' => $session->getCode(),
                'session_description' => $session->getDescription(),
                'session_poster' => $session->getPoster() ? '<img src="'.$this->platformManager->getUrl().'/'.$session->getPoster().'" style="max-width: 100%;" />' : '',
                'session_public_registration' => $this->translator->trans($session->getPublicRegistration() ? 'yes' : 'no', [], 'platform'),
                'session_max_users' => $session->getMaxUsers(),
                'workspace_url' => $this->routingHelper->workspaceUrl($session->getWorkspace()),
            ],
            $this->templateManager->formatDatePlaceholder('session_start', $session->getStartDate()),
            $this->templateManager->formatDatePlaceholder('session_end', $session->getEndDate()),
        );

        return $this->templateManager->getTemplate('training_session', $placeholders, $locale);
    }

    /**
     * Generates a workspace from CourseSession.
     */
    public function generateWorkspace(Session $session): Workspace
    {
        $course = $session->getCourse();

        $model = null;
        if (!empty($course->getWorkspace())) {
            $model = ['id' => $course->getWorkspace()->getUuid()];
        }

        /** @var Workspace $workspace */
        $workspace = $this->crud->create(Workspace::class, [
            'name' => $session->getName(),
            'code' => $session->getCode(),
            'thumbnail' => $session->getThumbnail(),
            'poster' => $session->getPoster(),
            'model' => $model,
            'meta' => [
                'description' => $session->getDescription(),
            ],
            'restrictions' => [
                'dates' => DateRangeNormalizer::normalize($session->getStartDate(), $session->getEndDate()),
                'hidden' => $session->isHidden(),
            ],
        ]);

        return $workspace;
    }

    /**
     * Adds users to a session.
     */
    public function addUsers(Session $session, array $users, string $type = AbstractRegistration::LEARNER, bool $validated = false, array $registrationData = []): array
    {
        $this->om->startFlushSuite();

        $results = [];
        foreach ($users as $user) {
            $sessionUser = $this->sessionUserRepo->findOneBy([
                'session' => $session,
                'user' => $user,
                'type' => $type,
            ]);

            if (empty($sessionUser)) {
                $sessionUser = new SessionUser();
                $sessionUser->setSession($session);
                $sessionUser->setUser($user);

                $this->crud->create($sessionUser, [
                    'type' => $type,
                    'validated' => $validated,
                    'data' => !empty($registrationData[$user->getUuid()]) ? $registrationData[$user->getUuid()] : [],
                ], [Crud::THROW_EXCEPTION]);
            }

            $results[] = $sessionUser;
        }

        $this->om->endFlushSuite();

        return $results;
    }

    /**
     * @param SessionUser[] $sessionUsers
     */
    public function moveUsers(Session $targetSession, array $sessionUsers, string $type = AbstractRegistration::LEARNER): void
    {
        $this->om->startFlushSuite();

        // unregister users from current session
        $this->crud->deleteBulk($sessionUsers);

        // register to the new session
        $registrationData = [];
        $users = array_map(function (SessionUser $sessionUser) use (&$registrationData) {
            $serialized = $this->serializer->serialize($sessionUser);
            if ($serialized['data']) {
                $registrationData[$sessionUser->getUser()->getUuid()] = $serialized['data'];
            }

            return $sessionUser->getUser();
        }, $sessionUsers);

        $this->addUsers($targetSession, $users, $type, true, $registrationData);

        $this->om->endFlushSuite();
    }

    /**
     * @param SessionUser[] $sessionUsers
     */
    public function confirmUsers(array $sessionUsers = []): array
    {
        $this->om->startFlushSuite();

        foreach ($sessionUsers as $sessionUser) {
            $sessionUser->setConfirmed(true);
            $this->om->persist($sessionUser);

            $this->registerUser($sessionUser);
        }

        $this->om->endFlushSuite();

        return $sessionUsers;
    }

    /**
     * @param SessionUser[] $sessionUsers
     */
    public function validateUsers(array $sessionUsers = []): array
    {
        $this->om->startFlushSuite();

        foreach ($sessionUsers as $sessionUser) {
            $sessionUser->setValidated(true);
            $this->om->persist($sessionUser);

            $this->registerUser($sessionUser);
        }

        $this->om->endFlushSuite();

        return $sessionUsers;
    }

    /**
     * Adds groups to a session.
     */
    public function addGroups(Session $session, array $groups, string $type = AbstractRegistration::LEARNER): array
    {
        $results = [];

        $this->om->startFlushSuite();

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

                $this->crud->create($sessionGroup, [
                    'type' => $type,
                ]);
            }

            $results[] = $sessionGroup;
        }

        $this->om->endFlushSuite();

        return $results;
    }

    /**
     * @param SessionGroup[] $sessionGroups
     */
    public function moveGroups(Session $targetSession, array $sessionGroups, string $type = AbstractRegistration::LEARNER): void
    {
        $this->om->startFlushSuite();

        // unregister groups from current session
        $this->crud->deleteBulk($sessionGroups);

        // register to the new session
        $this->addGroups($targetSession, array_map(function (SessionGroup $sessionGroup) {
            return $sessionGroup->getGroup();
        }, $sessionGroups), $type);

        $this->om->endFlushSuite();
    }

    /**
     * Gets/generates workspace role for session depending on given role name and type.
     */
    public function generateRoleForSession(Workspace $workspace, Role $courseRole = null, ?string $type = 'learner'): Role
    {
        if (empty($courseRole)) {
            if ('manager' === $type) {
                $role = $this->roleManager->getManagerRole($workspace);
            } else {
                $role = $this->roleManager->getCollaboratorRole($workspace);
            }
        } else {
            $role = $this->roleManager->getRoleByTranslationKeyAndWorkspace($courseRole->getTranslationKey(), $workspace);
            if (empty($role)) {
                $wsRoleName = 'ROLE_WS_'.strtoupper($courseRole->getTranslationKey()).'_'.$workspace->getUuid();

                $role = $this->roleManager->getRoleByName($wsRoleName);
                if (is_null($role)) {
                    $role = $this->roleManager->createWorkspaceRole(
                        $wsRoleName,
                        $courseRole->getTranslationKey(),
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
    public function inviteAllSessionLearners(Session $session): void
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
    public function sendSessionInvitation(Session $session, array $users, bool $confirm = true): void
    {
        $templateName = 'training_session_invitation';
        if ($confirm && $session->getUserValidation()) {
            $templateName = 'training_session_confirmation';
        }

        $workspace = $session->getWorkspace();
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

        $basicPlaceholders = array_merge([
                'course_name' => $course->getName(),
                'course_code' => $course->getCode(),
                'course_description' => $course->getDescription(),
                'session_url' => $this->routingHelper->desktopUrl('trainings').'/catalog/'.$session->getCourse()->getSlug().'/'.$session->getUuid(),
                'session_poster' => $session->getPoster() ? '<img src="'.$this->platformManager->getUrl().'/'.$session->getPoster().'" style="max-width: 100%;" />' : '',
                'session_name' => $session->getName(),
                'session_description' => $session->getDescription(),
                'session_trainers' => $trainersList,
                'workspace_url' => $workspace ? $this->routingHelper->workspaceUrl($workspace) : '',
                'registration_confirmation_url' => $this->router->generate('apiv2_cursus_session_self_confirm', ['id' => $session->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL), // TODO
            ],
            $this->templateManager->formatDatePlaceholder('session_start', $session->getStartDate()),
            $this->templateManager->formatDatePlaceholder('session_end', $session->getEndDate()),
        );

        foreach ($users as $user) {
            $locale = $user->getLocale();
            $placeholders = array_merge($basicPlaceholders, [
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'username' => $user->getUsername(),
            ]);

            if ($session->getInvitationTemplate()) {
                $title = $this->templateManager->getTemplateContent($session->getInvitationTemplate(), $placeholders, $locale, 'title');
                $content = $this->templateManager->getTemplateContent($session->getInvitationTemplate(), $placeholders, $locale);
            } else {
                $title = $this->templateManager->getTemplate($templateName, $placeholders, $locale, 'title');
                $content = $this->templateManager->getTemplate($templateName, $placeholders, $locale);
            }

            $this->eventDispatcher->dispatch(new SendMessageEvent(
                $content,
                $title,
                [$user],
                $session->getCreator()
            ), MessageEvents::MESSAGE_SENDING);
        }
    }

    /**
     * Register the user to the linked workspace and events if the registration is fully validated (confirmed and validated).
     */
    public function registerUser(SessionUser $sessionUser): void
    {
        $session = $sessionUser->getSession();

        if ($sessionUser->isValidated() && $sessionUser->isConfirmed()) {
            // register to linked workspace
            if ($session->getWorkspace()) {
                $role = AbstractRegistration::TUTOR === $sessionUser->getType() ? $session->getTutorRole() : $session->getLearnerRole();
                if ($role && !$sessionUser->getUser()->hasRole($role->getName())) {
                    $this->crud->patch($sessionUser->getUser(), 'role', Crud::COLLECTION_ADD, [$role], [Crud::NO_PERMISSIONS]);
                }
            }

            // register to linked events
            $events = $session->getEvents();
            foreach ($events as $event) {
                if (Session::REGISTRATION_AUTO === $event->getRegistrationType() && !$event->isTerminated()) {
                    $this->sessionEventManager->addUsers($event, [$sessionUser->getUser()], $sessionUser->getType());
                }
            }
        }
    }

    public function unregisterUser(SessionUser $sessionUser): void
    {
        $session = $sessionUser->getSession();

        // unregister user from the linked workspace
        if ($session->getWorkspace()) {
            $this->workspaceManager->unregister($sessionUser->getUser(), $session->getWorkspace(), [Crud::NO_PERMISSIONS]);
        }

        // unregister user from linked events
        $eventRegistrations = $this->sessionEventManager->getBySessionAndUser($session, $sessionUser->getUser());
        foreach ($eventRegistrations as $eventRegistration) {
            $this->sessionEventManager->removeUsers($eventRegistration->getEvent(), [$eventRegistration]);
        }
    }

    public function registerGroup(SessionGroup $sessionGroup): void
    {
        $session = $sessionGroup->getSession();

        // register to linked workspace
        if ($session->getWorkspace()) {
            $role = AbstractRegistration::TUTOR === $sessionGroup->getType() ? $session->getTutorRole() : $session->getLearnerRole();
            if ($role && !$sessionGroup->getGroup()->hasRole($role->getName())) {
                $this->crud->patch($sessionGroup->getGroup(), 'role', Crud::COLLECTION_ADD, [$role], [Crud::NO_PERMISSIONS]);
            }
        }

        // registers groups to linked events
        $events = $session->getEvents();
        foreach ($events as $event) {
            if (Session::REGISTRATION_AUTO === $event->getRegistrationType() && !$event->isTerminated()) {
                $this->sessionEventManager->addGroups($event, [$sessionGroup->getGroup()], $sessionGroup->getType());
            }
        }
    }

    public function unregisterGroup(SessionGroup $sessionGroup): void
    {
        $session = $sessionGroup->getSession();

        // unregister group from the linked workspace
        if ($session->getWorkspace()) {
            $this->workspaceManager->unregister($sessionGroup->getGroup(), $session->getWorkspace());
        }

        // unregister group from linked events
        $eventRegistrations = $this->sessionEventManager->getBySessionAndGroup($session, $sessionGroup->getGroup());
        foreach ($eventRegistrations as $eventRegistration) {
            $this->sessionEventManager->removeGroups($eventRegistration->getEvent(), [$eventRegistration]);
        }
    }
}
