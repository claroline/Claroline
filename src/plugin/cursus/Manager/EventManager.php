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
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\MessageEvents;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\CoreBundle\Library\ICS\ICSGenerator;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Manager\PlanningManager;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\CursusBundle\Entity\Event;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Claroline\CursusBundle\Entity\Registration\EventGroup;
use Claroline\CursusBundle\Entity\Registration\EventUser;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Repository\Registration\EventGroupRepository;
use Claroline\CursusBundle\Repository\Registration\EventUserRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @todo use Crud to manage registrations
 */
class EventManager
{
    private EventUserRepository $eventUserRepo;
    private EventGroupRepository $eventGroupRepo;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ObjectManager $om,
        private readonly ICSGenerator $ics,
        private readonly TemplateManager $templateManager,
        private readonly PlanningManager $planningManager,
        private readonly EventPresenceManager $presenceManager
    ) {
        $this->eventUserRepo = $om->getRepository(EventUser::class);
        $this->eventGroupRepo = $om->getRepository(EventGroup::class);
    }

    public function getBySessionAndUser(Session $session, User $user): ?array
    {
        return $this->eventUserRepo->findBySessionAndUser($session, $user);
    }

    public function getBySessionAndGroup(Session $session, Group $group): ?array
    {
        return $this->eventGroupRepo->findBySessionAndUser($session, $group);
    }

    public function generateFromTemplate(Event $event, string $locale): string
    {
        $placeholders = $this->getTemplatePlaceholders($event);

        return $this->templateManager->getTemplate('training_event', $placeholders, $locale);
    }

    /**
     * Adds users to a session event.
     */
    public function addUsers(Event $event, array $users, string $type = AbstractRegistration::LEARNER): array
    {
        $results = [];
        $registrationDate = new \DateTime();

        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $eventUser = $this->eventUserRepo->findOneBy(['event' => $event, 'user' => $user, 'type' => $type]);

            if (empty($eventUser)) {
                $eventUser = new EventUser();
                $eventUser->setEvent($event);
                $eventUser->setUser($user);

                $eventUser->setType($type);
                $eventUser->setDate($registrationDate);
                // no validation for events
                $eventUser->setValidated(true);
                $eventUser->setConfirmed(true);

                $this->om->persist($eventUser);

                $results[] = $eventUser;

                // add event to user planning
                $this->planningManager->addToPlanning($event, $eventUser->getUser());
            }
        }

        if ($event->getRegistrationMail()) {
            $this->sendSessionEventInvitation($event, array_map(function (EventUser $eventUser) {
                return $eventUser->getUser();
            }, $results));
        }

        // initialize presences
        $this->presenceManager->generate($event, $users);

        $this->om->endFlushSuite();

        return $results;
    }

    /**
     * @param EventUser[] $eventUsers
     */
    public function removeUsers(Event $event, array $eventUsers): void
    {
        $this->om->startFlushSuite();

        foreach ($eventUsers as $eventUser) {
            $this->om->remove($eventUser);

            // remove event from user planning
            $this->planningManager->removeFromPlanning($event, $eventUser->getUser());

            // clean presences
            $this->presenceManager->removePresence($event, $eventUser->getUser());
        }

        $this->om->endFlushSuite();
    }

    /**
     * Registers a user to a session event.
     */
    public function registerUserToSessionEvent(Event $event, User $user): void
    {
        if ($this->checkSessionEventCapacity($event)) {
            $this->addUsers($event, [$user]);
        }
    }

    /**
     * Adds groups to a session.
     */
    public function addGroups(Event $event, array $groups, string $type = AbstractRegistration::LEARNER): array
    {
        $results = [];
        $registrationDate = new \DateTime();

        $this->om->startFlushSuite();

        $users = [];
        foreach ($groups as $group) {
            $eventGroup = $this->eventGroupRepo->findOneBy([
                'event' => $event,
                'group' => $group,
                'type' => $type,
            ]);

            if (empty($eventGroup)) {
                $eventGroup = new EventGroup();

                $eventGroup->setEvent($event);
                $eventGroup->setGroup($group);
                $eventGroup->setType($type);
                $eventGroup->setDate($registrationDate);

                $this->om->persist($eventGroup);

                $results[] = $eventGroup;

                foreach ($group->getUsers() as $user) {
                    $users[$user->getUuid()] = $user;

                    // add event to user planning
                    $this->planningManager->addToPlanning($event, $user);
                }
            }
        }

        if ($event->getRegistrationMail()) {
            $this->sendSessionEventInvitation($event, $users);
        }

        // initialize presences
        $this->presenceManager->generate($event, $users);

        $this->om->endFlushSuite();

        return $results;
    }

    /**
     * @param EventGroup[] $eventGroups
     */
    public function removeGroups(Event $event, array $eventGroups): void
    {
        $this->om->startFlushSuite();

        foreach ($eventGroups as $eventGroup) {
            $this->om->remove($eventGroup);

            $group = $eventGroup->getGroup();
            foreach ($group->getUsers() as $user) {
                // remove event from user planning
                $this->planningManager->removeFromPlanning($event, $user);
                // clean presences
                $this->presenceManager->removePresence($event, $user);
            }
        }

        $this->om->endFlushSuite();
    }

    /**
     * Checks user limit of a session event to know if there is still place for the given number of users.
     */
    public function checkSessionEventCapacity(Event $event, int $count = 1): bool
    {
        $hasPlace = true;
        $maxUsers = $event->getMaxUsers();

        if (Session::REGISTRATION_AUTO !== $event->getRegistrationType() && $maxUsers) {
            // only get fully registered users
            $eventUsers = $this->eventUserRepo->findBy([
                'sessionEvent' => $event,
                'confirmed' => true,
                'validated' => true,
            ]);
            $nbUsers = count($eventUsers);
            $hasPlace = $nbUsers + $count <= $maxUsers;
        }

        return $hasPlace;
    }

    /**
     * Sends invitation to all session event users.
     */
    public function inviteAllSessionEventLearners(Event $event): void
    {
        $users = $this->getRegisteredUsers($event);

        $this->sendSessionEventInvitation($event, $users);
    }

    /**
     * Sends invitation to session event to given users.
     */
    public function sendSessionEventInvitation(Event $event, array $users): void
    {
        $basicPlaceholders = $this->getTemplatePlaceholders($event);

        // create ics file to attach to the message
        $icsPath = $this->getICS($event, true);

        foreach ($users as $user) {
            $locale = $user->getLocale();
            $placeholders = array_merge($basicPlaceholders, [
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'username' => $user->getUsername(),
            ]);

            if ($event->getInvitationTemplate()) {
                $title = $this->templateManager->getTemplateContent($event->getInvitationTemplate(), $placeholders, $locale, 'title');
                $content = $this->templateManager->getTemplateContent($event->getInvitationTemplate(), $placeholders, $locale);
            } else {
                $title = $this->templateManager->getTemplate('training_event_invitation', $placeholders, $locale, 'title');
                $content = $this->templateManager->getTemplate('training_event_invitation', $placeholders, $locale);
            }

            $this->eventDispatcher->dispatch(new SendMessageEvent(
                $content,
                $title,
                [$user],
                $event->getCreator(),
                [
                    ['name' => 'invitation.ics', 'url' => $icsPath, 'type' => 'text/calendar'],
                ]
            ), MessageEvents::MESSAGE_SENDING);
        }
    }

    public function getICS(Event $event, bool $toFile = false): string
    {
        $location = $event->getLocation();
        $locationAddress = '';
        if ($location) {
            $locationAddress = $location->getName();
            $locationAddress .= '<br>'.$location->getAddress();
            if ($location->getPhone()) {
                $locationAddress .= '<br>'.$location->getPhone();
            }
        }

        $icsProps = [
            'summary' => $event->getName(),
            'description' => $event->getDescription(),
            'location' => $locationAddress,
            'dtstart' => DateNormalizer::normalize($event->getStartDate()),
            'dtend' => DateNormalizer::normalize($event->getEndDate()),
            'url' => null,
        ];

        if ($toFile) {
            return $this->ics->createFile($icsProps, $event->getUuid());
        }

        return $this->ics->create($icsProps);
    }

    /**
     * @return User[]
     */
    public function getRegisteredUsers(Event $event): array
    {
        /** @var EventUser[] $sessionLearners */
        $eventLearners = $this->eventUserRepo->findBy([
            'event' => $event,
            'type' => AbstractRegistration::LEARNER,
            'validated' => true,
        ]);

        /** @var EventGroup[] $sessionGroups */
        $eventGroups = $this->eventGroupRepo->findBy([
            'event' => $event,
            'type' => AbstractRegistration::LEARNER,
        ]);

        $users = [];

        foreach ($eventLearners as $eventLearner) {
            $user = $eventLearner->getUser();
            $users[$user->getUuid()] = $user;
        }

        foreach ($eventGroups as $eventGroup) {
            $group = $eventGroup->getGroup();
            $groupUsers = $group->getUsers();

            foreach ($groupUsers as $user) {
                $users[$user->getUuid()] = $user;
            }
        }

        return array_values($users);
    }

    private function getTemplatePlaceholders(Event $event): array
    {
        $session = $event->getSession();
        $course = $session->getCourse();

        $trainersList = '';
        $eventTrainers = $this->eventUserRepo->findBy([
            'event' => $event,
            'type' => AbstractRegistration::TUTOR,
        ]);

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
            $locationAddress = $location->getAddress();
            if ($location->getPhone()) {
                $locationAddress .= '<br>'.$location->getPhone();
            }
        }

        return array_merge([
                // course info
                'course_name' => $course->getName(),
                'course_code' => $course->getCode(),
                'course_description' => $course->getDescription(),
                // session info
                'session_name' => $session->getName(),
                'session_description' => $session->getDescription(),
                'session_code' => $session->getCode(),
                // event info
                'event_name' => $event->getName(),
                'event_description' => $event->getDescription(),
                'event_code' => $event->getCode(),
                'event_location_name' => $locationName,
                'event_location_address' => $locationAddress,
                'event_trainers' => $trainersList,
            ],
            $this->templateManager->formatDatePlaceholder('session_start', $session->getStartDate()),
            $this->templateManager->formatDatePlaceholder('session_end', $session->getEndDate()),
            $this->templateManager->formatDatePlaceholder('event_start', $event->getStartDate()),
            $this->templateManager->formatDatePlaceholder('event_end', $event->getEndDate()),
        );
    }
}
