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
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\RoleManager;
use ICal\ICal;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

class AgendaManager
{
    private $om;
    private $tokenStorage;
    private $authorization;
    private $rm;
    private $translator;
    private $su;
    private $container;

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
        $workspace = $this->om->getRepository('ClarolineCoreBundle:Workspace\Workspace')->find($workspaceId);

        if ($workspace) {
            $listEvents = $repo->findByWorkspaceId($workspaceId, false);
        } else {
            $usr = $this->tokenStorage->getToken()->getUser();
            $listDesktop = $repo->findDesktop($usr, false);
            $listEventsU = $repo->findByUser($usr, false);
            $listEvents = array_merge($listEventsU, $listDesktop);
        }

        $calendar = $this->writeCalendar($listEvents);
        $fileName = $this->writeToICS($calendar, $workspace);

        return $fileName;
    }

    /**
     * @param $text it's the calendar text formatted in ics structure
     * @param $workspaceId
     *
     * @return string $fileName path to the file in web/upload folder
     */
    public function writeToICS($text, $workspace)
    {
        $name = is_null($workspace) ? 'desktop' : $workspace->getName();
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
    public function import($fileData, $workspace = null)
    {
        $ical = new ICal($fileData);
        $events = $ical->events();
        $entities = [];

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
            $entities[] = $e;
        }

        return $entities;
    }

    public function convertInvitationsToArray(array $invitations)
    {
        $data = [];

        foreach ($invitations as $invitation) {
            $data[] = $invitation->getEvent()->jsonSerialize($this->tokenStorage->getToken()->getUser());
        }

        return $data;
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
            'ClarolineAgendaBundle:ics_calendar.ics.twig',
            [
                'tzName' => $tz->getName(),
                'events' => $events,
            ]
        );
    }

    public function checkOpenAccess(Workspace $workspace)
    {
        if (!$this->authorization->isGranted('agenda', $workspace)) {
            throw new AccessDeniedException('You cannot open the agenda');
        }
    }

    public function checkEditAccess(Workspace $workspace)
    {
        if (!$this->authorization->isGranted(['agenda', 'edit'], $workspace)) {
            throw new AccessDeniedException('You cannot edit the agenda');
        }
    }

    /**
     * Find every Event for a given user and the replace him by another.
     *
     * @param User $from
     * @param User $to
     *
     * @return int
     */
    public function replaceEventUser(User $from, User $to)
    {
        $events = $this->om->getRepository('ClarolineAgendaBundle:Event')->findBy(['user' => $from]);

        if (count($events) > 0) {
            foreach ($events as $event) {
                $event->setUser($to);
            }

            $this->om->flush();
        }

        return count($events);
    }

    /**
     * Find every EventInvitation for a given user and the replace him by another.
     *
     * @param User $from
     * @param User $to
     *
     * @return int
     */
    public function replaceEventInvitationUser(User $from, User $to)
    {
        $eventInvitations = $this->om->getRepository('ClarolineAgendaBundle:EventInvitation')->findByUser($from);

        if (count($eventInvitations) > 0) {
            foreach ($eventInvitations as $eventInvitation) {
                $eventInvitation->setUser($to);
            }

            $this->om->flush();
        }

        return count($eventInvitations);
    }
}
