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

use Claroline\AppBundle\Event\StrictDispatcher;
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
use Claroline\CursusBundle\Event\Log\LogSessionEventGroupRegistrationEvent;
use Claroline\CursusBundle\Event\Log\LogSessionEventGroupUnregistrationEvent;
use Claroline\CursusBundle\Event\Log\LogSessionEventUserRegistrationEvent;
use Claroline\CursusBundle\Event\Log\LogSessionEventUserUnregistrationEvent;
use Claroline\CursusBundle\Repository\Registration\EventGroupRepository;
use Claroline\CursusBundle\Repository\Registration\EventUserRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EventManager
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var ObjectManager */
    private $om;
    /** @var UrlGeneratorInterface */
    private $router;
    /** @var ICSGenerator */
    private $ics;
    /** @var TemplateManager */
    private $templateManager;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var PlanningManager */
    private $planningManager;

    /** @var EventUserRepository */
    private $eventUserRepo;
    /** @var EventGroupRepository */
    private $eventGroupRepo;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ObjectManager $om,
        UrlGeneratorInterface $router,
        ICSGenerator $ics,
        TemplateManager $templateManager,
        TokenStorageInterface $tokenStorage,
        StrictDispatcher $dispatcher,
        PlanningManager $planningManager
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->om = $om;
        $this->router = $router;
        $this->ics = $ics;
        $this->templateManager = $templateManager;
        $this->tokenStorage = $tokenStorage;
        $this->dispatcher = $dispatcher;
        $this->planningManager = $planningManager;

        $this->eventUserRepo = $om->getRepository(EventUser::class);
        $this->eventGroupRepo = $om->getRepository(EventGroup::class);
    }

    public function getBySessionAndUser(Session $session, User $user)
    {
        return $this->eventUserRepo->findBySessionAndUser($session, $user);
    }

    public function getBySessionAndGroup(Session $session, Group $group)
    {
        return $this->eventGroupRepo->findBySessionAndUser($session, $group);
    }

    public function generateFromTemplate(Event $event, string $locale)
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

                $this->eventDispatcher->dispatch(new LogSessionEventUserRegistrationEvent($eventUser), 'log');

                $results[] = $eventUser;

                // add event to user planning
                $this->planningManager->addToPlanning($event, $eventUser->getUser());
            }
        }

        $this->sendSessionEventInvitation($event, array_map(function (EventUser $eventUser) {
            return $eventUser->getUser();
        }, $results));

        $this->om->endFlushSuite();

        return $results;
    }

    /**
     * @param EventUser[] $eventUsers
     */
    public function removeUsers(Event $event, array $eventUsers)
    {
        $this->om->startFlushSuite();

        foreach ($eventUsers as $eventUser) {
            $this->om->remove($eventUser);

            $this->eventDispatcher->dispatch(new LogSessionEventUserUnregistrationEvent($eventUser), 'log');

            // remove event from user planning
            $this->planningManager->removeFromPlanning($event, $eventUser->getUser());
        }

        $this->om->endFlushSuite();
    }

    /**
     * Registers an user to a session event.
     */
    public function registerUserToSessionEvent(Event $event, User $user)
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

                $this->eventDispatcher->dispatch(new LogSessionEventGroupRegistrationEvent($eventGroup), 'log');

                $results[] = $eventGroup;

                foreach ($group->getUsers() as $user) {
                    $users[$user->getUuid()] = $user;

                    // add event to user planning
                    $this->planningManager->addToPlanning($event, $user);
                }
            }
        }

        $this->sendSessionEventInvitation($event, $users);

        $this->om->endFlushSuite();

        return $results;
    }

    /**
     * @param EventGroup[] $eventGroups
     */
    public function removeGroups(Event $event, array $eventGroups)
    {
        $this->om->startFlushSuite();

        foreach ($eventGroups as $eventGroup) {
            $this->om->remove($eventGroup);

            $group = $eventGroup->getGroup();
            foreach ($group->getUsers() as $user) {
                // remove event from user planning
                $this->planningManager->removeFromPlanning($event, $user);
            }

            $this->eventDispatcher->dispatch(new LogSessionEventGroupUnregistrationEvent($eventGroup), 'log');
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
    public function inviteAllSessionEventLearners(Event $event)
    {
        $users = $this->getRegisteredUsers($event);

        $this->sendSessionEventInvitation($event, $users);
    }

    /**
     * Sends invitation to session event to given users.
     */
    public function sendSessionEventInvitation(Event $event, array $users)
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

            $title = $this->templateManager->getTemplate('training_event_invitation', $placeholders, $locale, 'title');
            $content = $this->templateManager->getTemplate('training_event_invitation', $placeholders, $locale);

            $this->dispatcher->dispatch(MessageEvents::MESSAGE_SENDING, SendMessageEvent::class, [
                $content,
                $title,
                [$user],
                $event->getCreator(),
                [
                    ['name' => 'invitation.ics', 'url' => $icsPath, 'type' => 'text/calendar'],
                ],
            ]);
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
        $sessionLearners = $this->eventUserRepo->findBy([
            'event' => $event,
            'type' => AbstractRegistration::LEARNER,
            'validated' => true,
        ]);

        /** @var EventGroup[] $sessionGroups */
        $sessionGroups = $this->eventGroupRepo->findBy([
            'event' => $event,
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

        return [
            // course info
            'course_name' => $course->getName(),
            'course_code' => $course->getCode(),
            'course_description' => $course->getDescription(),
            // session info
            'session_name' => $session->getName(),
            'session_description' => $session->getDescription(),
            'session_code' => $session->getCode(),
            'session_start' => $session->getStartDate()->format('d/m/Y'),
            'session_end' => $session->getEndDate()->format('d/m/Y'),
            // event info
            'event_name' => $event->getName(),
            'event_description' => $event->getDescription(),
            'event_code' => $event->getCode(),
            'event_start' => $event->getStartDate()->format('d/m/Y H:i'),
            'event_end' => $event->getEndDate()->format('d/m/Y H:i'),
            'event_location_name' => $locationName,
            'event_location_address' => $locationAddress,
            'event_trainers' => $trainersList,
        ];
    }
}
