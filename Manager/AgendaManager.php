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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Claroline\CoreBundle\Library\Security\Utilities;

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

    /**
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "rootDir"      = @DI\Inject("%kernel.root_dir%"),
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "rm"           = @DI\Inject("claroline.manager.role_manager"),
     *     "translator"   = @DI\Inject("translator"),
     *     "su"           = @DI\Inject("claroline.security.utilities")
     * })
     */
    public function __construct(
        ObjectManager $om,
        $rootDir,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        RoleManager $rm,
        Translator $translator,
        Utilities $su
    )
    {
        $this->rootDir = $rootDir;
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->rm = $rm;
        $this->translator = $translator;
        $this->su = $su;
    }

    public function addEvent(Event $event, $workspace = null)
    {
        $event->setWorkspace($workspace);
        $event->setUser($this->tokenStorage->getToken()->getUser());
        $this->setEventDate($event);
        $this->om->persist($event);

        if ($event->getRecurring() > 0) {
            $this->addRecurrentEvents($event);
        }

        $this->om->flush();

        return $this->toArray($event);
    }

    /**
     * @param  Event $event
     * @return boolean
     */
    public function deleteEvent(Event $event)
    {
        $removed = $this->toArray($event);
        $this->om->remove($event);
        $this->om->flush();

        return $removed;
    }

    public function desktopEvents(User $usr, $allDay = false)
    {
        $listEvents = $this->om->getRepository('ClarolineAgendaBundle:Event')->findByUser($usr, $allDay);
        $desktopEvents = $this->om->getRepository('ClarolineAgendaBundle:Event')->findDesktop($usr, $allDay);

        return array_merge($this->convertEventsToArray($listEvents), $this->convertEventsToArray($desktopEvents));
    }

    /**
     * @param $workspaceId
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

    private function writeEvent(Event $e)
    {
        $event = "BEGIN:VEVENT\n";
        $event .= "DTSTART:".date('Ymd',$e->getStart()->getTimestamp())."T".date('Hi',$e->getStart()->getTimestamp())."00\r\n";
        $event .= "DTEND:".date('Ymd',$e->getEnd()->getTimestamp())."T".date('Hi',$e->getEnd()->getTimestamp())."00\r\n";
        $event .= "CLASS:CLAROLINECONNECT \r\n";
        $event .= "DESCRIPTION:".$e->getDescription()."\r\n";
        $event .= "LOCATION: \r\n";
        $event .= "STATUS:CONFIRMED\r\n";
        $event .= "SUMMARY:".$e->getTitle()."\r\n";
        $event .= "END:VEVENT\r\n";

        return $event;
    }

    /**
     * @param $array $events
     * @return strings file in ics format
     */
    private function writeCalendar($arrayEvents)
    {
        $date = new \Datetime();
        $tz = $date->getTimezone();
        $calendar = "BEGIN:VCALENDAR"."\n";

        foreach ($arrayEvents as $value) {
            $calendar .= "PRODID:-BayBuk\n";
            $calendar .= "VERSION:2.0\n";
            $calendar .= "CALSCALE:GREGORIAN\n";
            $calendar .= "METHOD:PUBLISH\n";
            $calendar .= "X-WR-CALNAME:".$value->getUser()->getUsername()."\n";
            $calendar .= "X-WR-TIMEZONE:".$tz->getName()."\n";
            $calendar .= $this->writeEvent($value);
        }
        $calendar .= "END:VCALENDAR";
        return $calendar;
    }

    /**
     * @param $text it's the calendar text formated in ics structure
     * @param $workspaceId
     * @return string $fileName path to the file in web/upload folder
     */
    public function writeToICS($text,$workspaceId)
    {
        $name = is_null($workspaceId) ? 'desktop' : $workspaceId->getName();
        $fileName = $this->rootDir.'/../web/uploads/'.$name.".ics";
        file_put_contents($fileName, $text);

        return $fileName;
    }

    /**
     * Imports ical files.
     *
     * @param  UploadedFile $file
     * @param  Workspace $workspace
     * @return int number of events saved
     */
    public function importEvents(UploadedFile $file, $workspace = null)
    {
        $ical = new \ICal($file->getPathname());
        $events = $ical->events();
        //$this->om->startFlushSuite();
        $tabs = [];

        foreach ($events as $i => $event) {
            $e = new Event();
            $e->setTitle($event['SUMMARY']);
            $e->setStart($ical->iCalDateToUnixTimestamp($event['DTSTART']));
            $e->setEnd($ical->iCalDateToUnixTimestamp($event['DTEND']));
            $e->setDescription($event['DESCRIPTION']);
            if ($workspace) $e->setWorkspace($workspace);
            $e->setUser($this->tokenStorage->getToken()->getUser());
            $e->setPriority('#01A9DB');
            $this->om->persist($e);
            //the flush is required to generate an id
            $this->om->flush();
            $tabs[] = $this->toArray($e);
        }
        //$this->om->endFlushSuite();

        return $tabs;
    }

    public function updateEvent(Event $event)
    {
        $this->setEventDate($event);
        $this->om->flush();

        return $this->toArray($event);
    }

    public function displayEvents(Workspace $workspace, $allDay = false)
    {
        $events = $this->om->getRepository('ClarolineAgendaBundle:Event')
            ->findbyWorkspaceId($workspace->getId(), $allDay);

        return $this->convertEventsToArray($events);
    }

    public function updateEndDate(Event $event, $dayDelta = 0, $minDelta = 0)
    {
        $event->setEnd($event->getEnd()->getTimeStamp() + $this->toSeconds($dayDelta, $minDelta));
        $this->om->flush();

        return $this->toArray($event);
    }

    public function updateStartDate(Event $event, $dayDelta = 0, $minDelta = 0)
    {
        $event->setStart($event->getStart()->getTimeStamp() + $this->toSeconds($dayDelta, $minDelta));
        $this->om->flush();
    }

    public function moveEvent(Event $event, $dayDelta, $minuteDelta)
    {
        $this->updateStartDate($event, $dayDelta, $minuteDelta);
        $this->updateEndDate($event, $dayDelta, $minuteDelta);

        return $this->toArray($event);
    }

    /**
     * @param Event $event
     * @return array
     */
    public function toArray(Event $event)
    {
        $start = is_null($event->getStart())? null : $event->getStart()->getTimestamp();
        $end = is_null($event->getEnd())? null : $event->getEnd()->getTimestamp();
        $startDate = new \DateTime();
        $startDate->setTimeStamp($start);
        $startIso = $startDate->format(\DateTime::ISO8601);
        $endDate = new \DateTime();
        $startDate->setTimeStamp($end);
        $endIso = $startDate->format(\DateTime::ISO8601);

        return array(
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'start' => $startIso,
            'end' => $endIso,
            'color' => $event->getPriority(),
            'allDay' => $event->getAllDay(),
            'isTask' => $event->isTask(),
            'owner' => $event->getUser()->getUsername(),
            'description' => $event->getDescription(),
            'editable' => $this->authorization->isGranted('EDIT', $event),
            'deletable' => $this->authorization->isGranted('DELETE', $event),
            'workspace_id' => $event->getWorkspace() ? $event->getWorkspace()->getId(): null,
            'workspace_name' => $event->getWorkspace() ? $event->getWorkspace()->getName(): null,
            'startFormatted' => date($this->translator->trans('date_range.format.with_hours', array(), 'platform'), $start),
            'endFormatted' => date($this->translator->trans('date_range.format.with_hours', array(), 'platform'), $end),
            'endHours' => $event->getEndHours(),
            'startHours' => $event->getStartHours(),
            'className' => 'event_' . $event->getId()
        );
    }

    public function convertEventsToArray(array $events)
    {
        $data = array();

        foreach ($events as $event) {
            $data[] = $this->toArray($event);
        }

        return $data;
    }

    private function addRecurrentEvents(Event $event, $day = 1, $minutes = 0)
    {
        $events = array();

        for ($i = 1; $i <= $event->getRecurring(); $i++) {
            $recEvent = clone $event;
            $recEvent->setStart($event->getStart()->getTimeStamp() + $this->toSeconds($day, $minutes) * $i);
            $recEvent->setEnd($event->getStart()->getTimeStamp() + $this->toSeconds($day, $minutes) * $i);
            $events[] = $recEvent;
            $this->om->persist($recEvent);
        }

        $this->om->flush();

        return $this->convertEventsToArray($events);
    }

    private function toSeconds($days = 0, $mins = 0)
    {
        return $days * 3600 * 24 + $mins * 60;
    }

    /**
     * Set the event date.
     * Only use this method for events created or updated through AgendaType
     *
     * @param Event $event
     */
    private function setEventDate(Event $event)
    {
        //task don't have start nor ending
        if ($event->getAllDay()) {
            $event->setStart(null);
            $event->setEnd(null);
        } else {
            //we get the hours value directly from the property wich has been setted by the form.
            //That way we can use the getter to return the number of hours wich is deduced from the timestamp stored
            //For some reason, symfony2 always substract 3600. Timestamp for hours 0 = -3600 wich is weird.
            //This couldn't be fixed be setting the timezone in the form field.
            $event->setStart($event->getStart()->getTimestamp() + $event->startHours + 3600);
            $event->setEnd($event->getEnd()->getTimestamp() + $event->endHours + 3600);
        }
    }
}
