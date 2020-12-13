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
use Claroline\CoreBundle\Manager\MailManager;
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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EventManager
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var MailManager */
    private $mailManager;
    /** @var ObjectManager */
    private $om;
    /** @var UrlGeneratorInterface */
    private $router;
    /** @var TemplateManager */
    private $templateManager;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    private $eventUserRepo;
    private $eventGroupRepo;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        MailManager $mailManager,
        ObjectManager $om,
        UrlGeneratorInterface $router,
        TemplateManager $templateManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->mailManager = $mailManager;
        $this->om = $om;
        $this->router = $router;
        $this->templateManager = $templateManager;
        $this->tokenStorage = $tokenStorage;

        $this->eventUserRepo = $om->getRepository(EventUser::class);
        $this->eventGroupRepo = $om->getRepository(EventGroup::class);
    }

    public function generateFromTemplate(Event $sessionEvent, string $locale)
    {
        // TODO : implement
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
    public function removeUsers(array $eventUsers)
    {
        foreach ($eventUsers as $eventUser) {
            $this->om->remove($eventUser);

            $this->eventDispatcher->dispatch(new LogSessionEventUserUnregistrationEvent($eventUser), 'log');
        }

        $this->om->flush();
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
                }
            }
        }

        $this->sendSessionEventInvitation($event, $users);

        $this->om->endFlushSuite();

        return $results;
    }

    public function removeGroups(Event $event, array $eventGroups)
    {
        foreach ($eventGroups as $eventGroup) {
            $this->om->remove($eventGroup);

            $this->eventDispatcher->dispatch(new LogSessionEventGroupUnregistrationEvent($eventGroup), 'log');
        }

        $this->om->flush();
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
    public function inviteAllSessionEventLearners(Event $event, Template $template = null)
    {
        // only get fully registered users
        $eventUsers = $this->eventUserRepo->findBy([
            'sessionEvent' => $event,
            'confirmed' => true,
            'validated' => true,
        ]);
        $users = array_map(function (EventUser $eventUser) {
            return $eventUser->getUser();
        }, $eventUsers);

        $this->sendSessionEventInvitation($event, $users);
    }

    /**
     * Sends invitation to session event to given users.
     */
    public function sendSessionEventInvitation(Event $event, array $users)
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

        $basicPlaceholders = [
            'course_name' => $course->getName(),
            'course_code' => $course->getCode(),
            'course_description' => $course->getDescription(),
            'session_name' => $session->getName(),
            'session_description' => $session->getDescription(),
            'session_start' => $session->getStartDate()->format('d/m/Y'),
            'session_end' => $session->getEndDate()->format('d/m/Y'),
            'event_name' => $event->getName(),
            'event_description' => $event->getDescription(),
            'event_start' => $event->getStartDate()->format('d/m/Y H:i'),
            'event_end' => $event->getEndDate()->format('d/m/Y H:i'),
            'event_location_name' => $locationName,
            'event_location_address' => $locationAddress,
            'event_trainers' => $trainersList,
        ];

        foreach ($users as $user) {
            $locale = $user->getLocale();
            $placeholders = array_merge($basicPlaceholders, [
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'username' => $user->getUsername(),
            ]);

            $title = $this->templateManager->getTemplate('training_event_invitation', $placeholders, $locale, 'title');
            $content = $this->templateManager->getTemplate('training_event_invitation', $placeholders, $locale);

            $this->mailManager->send($title, $content, [$user]);
        }
    }
}
