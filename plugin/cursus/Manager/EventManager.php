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
use Claroline\CursusBundle\Entity\Registration\EventUser;
use Claroline\CursusBundle\Entity\Session;
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
    }

    /**
     * Adds users to a session event.
     */
    public function addUsersToSessionEvent(Event $event, array $users): array
    {
        $results = [];
        $registrationDate = new \DateTime();

        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $eventUser = $this->eventUserRepo->findOneBy(['event' => $event, 'user' => $user]);

            if (empty($eventUser)) {
                $eventUser = new EventUser();
                $eventUser->setEvent($event);
                $eventUser->setUser($user);
                $eventUser->setDate($registrationDate);
                $this->om->persist($eventUser);

                $this->eventDispatcher->dispatch(new LogSessionEventUserRegistrationEvent($eventUser), 'log');

                $results[] = $eventUser;
            }
        }

        $this->om->endFlushSuite();

        return $results;
    }

    /**
     * @param EventUser[] $eventUsers
     */
    public function removeUsersFromSessionEvent(array $eventUsers)
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
            $this->addUsersToSessionEvent($event, [$user]);
        }
    }

    /**
     * Checks user limit of a session event to know if there is still place for the given number of users.
     */
    public function checkSessionEventCapacity(Event $event, int $count = 1): bool
    {
        $hasPlace = true;
        $maxUsers = $event->getMaxUsers();

        if (Session::REGISTRATION_AUTO !== $event->getRegistrationType() && $maxUsers) {
            $eventUsers = $this->eventUserRepo->findBy(['sessionEvent' => $event, 'registrationStatus' => SessionEventUser::REGISTERED]);
            $nbUsers = count($eventUsers);
            $hasPlace = $nbUsers + $count <= $maxUsers;
        }

        return $hasPlace;
    }

    /**
     * Sends invitation to all session event users.
     */
    public function inviteAllSessionEventUsers(Event $event, Template $template = null)
    {
        $eventUsers = $this->eventUserRepo->findBy([
            'sessionEvent' => $event,
            'registrationStatus' => SessionEventUser::REGISTERED,
        ]);
        $users = array_map(function (EventUser $eventUser) {
            return $eventUser->getUser();
        }, $eventUsers);

        $this->sendEventInvitation($event, $users, $template);
    }

    /**
     * Sends invitation to session event to given users.
     */
    public function sendEventInvitation(Event $event, array $users, Template $template = null)
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
            'course_name' => $course->getName(),
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
                $this->templateManager->getTemplate('training_event_invitation', $placeholders, $locale);
            $content = $template ?
                $this->templateManager->getTemplateContent($template, $placeholders) :
                $this->templateManager->getTemplate('training_event_invitation', $placeholders, $locale);
            $this->mailManager->send($title, $content, [$user]);
        }
    }
}
