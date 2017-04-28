<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Manager;

use Claroline\AgendaBundle\Entity\Event;
use Claroline\AgendaBundle\Entity\EventInvitation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use ICal\ICal;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("claroline.manager.agenda_manager")
 */
class AgendaManager
{
    private $om;
    private $tokenStorage;
    private $authorization;
    private $rm;
    private $translator;
    private $su;
    private $container;

    /**
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "rootDir"      = @DI\Inject("%kernel.root_dir%"),
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "rm"           = @DI\Inject("claroline.manager.role_manager"),
     *     "translator"   = @DI\Inject("translator"),
     *     "su"           = @DI\Inject("claroline.security.utilities"),
     *     "container"    = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        ObjectManager $om,
        $rootDir,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        RoleManager $rm,
        TranslatorInterface $translator,
        Utilities $su,
        ContainerInterface $container
    ) {
        $this->rootDir = $rootDir;
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->rm = $rm;
        $this->translator = $translator;
        $this->su = $su;
        $this->container = $container;
    }

    public function addEvent(Event $event, $workspace = null, array $users = [])
    {
        $event->setWorkspace($workspace);
        $event->setUser($this->tokenStorage->getToken()->getUser());
        $this->setEventDate($event);
        $this->om->persist($event);
        $this->om->flush();

        $this->sendInvitation($event, $users);

        return $event->jsonSerialize();
    }

    public function updateEvent(Event $event, array $users = [])
    {
        $this->setEventDate($event);
        $this->om->flush();

        $this->sendInvitation($event, $users);

        return $event->jsonSerialize();
    }

    /**
     * @param Event $event
     *
     * @return bool
     */
    public function deleteEvent(Event $event)
    {
        $removed = $event->jsonSerialize();
        $this->om->remove($event);
        $this->om->flush();

        return $removed;
    }

    public function sendInvitation(Event $event, array $users = [])
    {
        foreach ($users as $key => $user) {
            $invitation = $this->om->getRepository('ClarolineAgendaBundle:EventInvitation')->findOneBy([
                'user' => $user,
                'event' => $event,
            ]);

            if ($invitation) {
                unset($users[$key]);
                continue;
            }

            $eventInvitation = new EventInvitation($event, $user);
            $this->om->persist($eventInvitation);
        }
        $this->om->flush();

        $creator = $this->tokenStorage->getToken()->getUser();
        $message = new SendMessageEvent(
            $creator,
            $this->translator->trans('send_message_content', [
                '%Sender%' => $creator->getUserName(),
                '%Start%' => $event->getStart(),
                '%End%' => $event->getEnd(),
                '%Description%' => $event->getDescription(),
                '%JoinAction%' => $this->container->get('router')->generate(
                    'claro_agenda_invitation_action',
                    ['event' => $event->getId(), 'action' => EventInvitation::JOIN],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                '%MaybeAction%' => $this->container->get('router')->generate(
                    'claro_agenda_invitation_action',
                    ['event' => $event->getId(), 'action' => EventInvitation::MAYBE],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                '%ResignAction%' => $this->container->get('router')->generate(
                    'claro_agenda_invitation_action',
                    ['event' => $event->getId(), 'action' => EventInvitation::RESIGN],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ], 'agenda'),
            $this->translator->trans('send_message_object', ['%EventName%' => $event->getTitle()], 'agenda'),
            null,
            $users,
            false
        );

        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->dispatch('claroline_message_sending_to_users', $message);
    }

    public function desktopEvents(User $usr, $allDay = false)
    {
        $desktopEvents = $this->om->getRepository('ClarolineAgendaBundle:Event')->findDesktop($usr, $allDay);
        $workspaceEventsAndTasks = $this->om->getRepository('ClarolineAgendaBundle:Event')->findEventsAndTasksOfWorkspaceForTheUser($usr);
        $invitationEvents = $this->om->getRepository('ClarolineAgendaBundle:EventInvitation')->findBy([
            'user' => $usr,
            'status' => [EventInvitation::JOIN, EventInvitation::MAYBE],
        ]);

        return array_merge(
            $this->convertEventsToArray($workspaceEventsAndTasks),
            $this->convertEventsToArray($desktopEvents),
            $this->convertInvitationsToArray($invitationEvents)
        );
    }

    /**
     * @param $workspaceId
     *
     * @return list of Events
     */
    public function export($workspaceId = null)
    {
        $repo = $this->om->getRepository('ClarolineAgendaBundle:Event');

        if (isset($workspaceId)) {
            $listEvents = $repo->findByWorkspaceId($workspaceId, false);
        } else {
            $usr = $this->tokenStorage->getToken()->getUser();
            $listDesktop = $repo->findDesktop($usr, false);
            $listEventsU = $repo->findByUser($usr, false);
            $listEvents = array_merge($listEventsU, $listDesktop);
        }

        $calendar = $this->writeCalendar($listEvents);
        $fileName = $this->writeToICS($calendar, $workspaceId);

        return $fileName;
    }

    /**
     * @param $text it's the calendar text formatted in ics structure
     * @param $workspaceId
     *
     * @return string $fileName path to the file in web/upload folder
     */
    public function writeToICS($text, $workspaceId)
    {
        $name = is_null($workspaceId) ? 'desktop' : $workspaceId->getName();
        $fileName = $this->rootDir.'/../web/uploads/'.$name.'.ics';
        file_put_contents($fileName, $text);

        return $fileName;
    }

    /**
     * Imports ical files.
     *
     * @param UploadedFile $file
     * @param Workspace    $workspace
     *
     * @return int number of events saved
     */
    public function importEvents(UploadedFile $file, $workspace = null)
    {
        $ical = new ICal($file->getPathname());
        $events = $ical->events();
        $tabs = [];

        foreach ($events as $event) {
            $e = new Event();
            $e->setTitle($event->summary);
            $e->setStart($ical->iCalDateToUnixTimestamp($event->dtstart));
            $e->setEnd($ical->iCalDateToUnixTimestamp($event->dtend));
            $e->setDescription($event->description);
            if ($workspace) {
                $e->setWorkspace($workspace);
            }
            $e->setUser($this->tokenStorage->getToken()->getUser());
            $e->setPriority('#01A9DB');
            $this->om->persist($e);
            //the flush is required to generate an id
            $this->om->flush();
            $tabs[] = $e->jsonSerialize();
        }

        return $tabs;
    }

    public function displayEvents(Workspace $workspace, $allDay = false)
    {
        $events = $this->om->getRepository('ClarolineAgendaBundle:Event')
            ->findByWorkspaceId($workspace->getId());

        return $this->convertEventsToArray($events);
    }

    public function updateEndDate(Event $event, $dayDelta = 0, $minDelta = 0)
    {
        $event->setEnd($event->getEndInTimestamp() + $this->toSeconds($dayDelta, $minDelta));
        $this->om->flush();

        return $event->jsonSerialize();
    }

    public function updateStartDate(Event $event, $dayDelta = 0, $minDelta = 0)
    {
        $event->setStart($event->getStartInTimestamp() + $this->toSeconds($dayDelta, $minDelta));
        $this->om->flush();
    }

    public function moveEvent(Event $event, $dayDelta, $minuteDelta)
    {
        $this->updateStartDate($event, $dayDelta, $minuteDelta);
        $this->updateEndDate($event, $dayDelta, $minuteDelta);

        return $event->jsonSerialize();
    }

    public function convertEventsToArray(array $events)
    {
        $data = [];

        foreach ($events as $event) {
            $data[] = $event->jsonSerialize();
        }

        return $data;
    }

    public function convertInvitationsToArray(array $invitations)
    {
        $data = [];

        foreach ($invitations as $invitation) {
            $data[] = $invitation->getEvent()->jsonSerialize($this->tokenStorage->getToken()->getUser());
        }

        return $data;
    }

    private function toSeconds($days = 0, $mins = 0)
    {
        return $days * 3600 * 24 + $mins * 60;
    }

    /**
     * Set the event date.
     * Only use this method for events created or updated through AgendaType.
     *
     * @param Event $event
     */
    private function setEventDate(Event $event)
    {
        if ($event->isAllDay()) {
            // If it's a task we set the start date at the beginning of the day
            if ($event->isTask()) {
                $event->setStart(strtotime($event->getEndInDateTime()->format('Y-m-d').' 00:00:00'));
            } else {
                $event->setStart(strtotime($event->getStartInDateTime()->format('Y-m-d').' 00:00:00'));
            }
            $event->setEnd(strtotime($event->getEndInDateTime()->format('Y-m-d').' 24:00:00'));
        } else {
            // If it's a task, we subtract 30 min so that the event is not a simple line on the calendar
            if ($event->isTask()) {
                $event->setStart($event->getEndInTimestamp() - 30 * 60);
            } else {
                $event->setStart($event->getStartInTimestamp());
            }
            $event->setEnd($event->getEndInTimestamp());
        }
    }

    public function sortEvents($listEvents)
    {
        usort(
            $listEvents,
            function ($a, $b) {
                $aStartTimestamp = $a->getStartInTimestamp();
                $bStartTimestamp = $b->getStartInTimestamp();
                if ($aStartTimestamp === $bStartTimestamp) {
                    return 0;
                }

                return $aStartTimestamp > $bStartTimestamp ? 1 : -1;
            }
        );

        return $listEvents;
    }

    /**
     * @param array $events
     *
     * @return Twig view in ics format
     */
    private function writeCalendar(array $events)
    {
        $date = new \Datetime();
        $tz = $date->getTimezone();

        return $this->container->get('templating')->render(
            'ClarolineAgendaBundle:Tool:exportIcsCalendar.ics.twig',
            [
                'tzName' => $tz->getName(),
                'events' => $events,
            ]
        );
    }

    public function checkOpenAccess(Workspace $workspace)
    {
        if (!$this->authorization->isGranted('agenda_', $workspace)) {
            throw new AccessDeniedException('You cannot open the agenda');
        }
    }

    public function checkEditAccess(Workspace $workspace)
    {
        if (!$this->authorization->isGranted(['agenda_', 'edit'], $workspace)) {
            throw new AccessDeniedException('You cannot edit the agenda');
        }
    }
}
