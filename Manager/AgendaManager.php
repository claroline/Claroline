<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Event;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @DI\Service("claroline.manager.agenda_manager")
 */
class AgendaManager
{
    private $om;
    private $security;
    private $rm;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "rootDir"      = @DI\Inject("%kernel.root_dir%"),
     *     "security"     = @DI\Inject("security.context"),
     *     "rm"           =  @DI\Inject("claroline.manager.role_manager"),
     *     "translator"   = @DI\Inject("translator")
     * })
     */
    public function __construct(
        ObjectManager $om,
        $rootDir,
        SecurityContextInterface $security,
        RoleManager $rm,
        Translator $translator
    )
    {
        $this->rootDir = $rootDir;
        $this->om = $om;
        $this->security = $security;
        $this->rm = $rm;
        $this->translator = $translator;
    }

    public function addEvent(Event $event, $workspace = null)
    {
        $event->setWorkspace($workspace);
        $event->setUser($this->security->getToken()->getUser());
        $this->om->persist($event);

        if ($event->getRecurring() > 0) {
            $this->calculRecurrency($event);
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

    public function desktopEvents(){
        $usr = $this->security->getToken()->getUser();
        $listEvents = $this->om->getRepository('ClarolineCoreBundle:Event')->findByUser($usr, 0);
        $desktopEvents = $this->om->getRepository('ClarolineCoreBundle:Event')->findDesktop($usr, 0);
        $data = array_merge($this->convertEventsToArray($listEvents), $this->convertEventsToArray($desktopEvents));

        return $data;
    }

    /**
     * @param $workspaceId
     * @return list of Events
     */
    public function export($workspaceId = null)
    {
        $repo = $this->om->getRepository('ClarolineCoreBundle:Event');

        if (isset($workspaceId)) {
            $listEvents = $repo->findByWorkspaceId($workspaceId, false);
        } else {
            $usr = $this->security->getToken()->getUser();
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
    public function importEvents(UploadedFile $file, $workspace)
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
            $e->setWorkspace($workspace);
            $e->setUser($this->security->getToken()->getUser());
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
        $this->om->persist($event);
        $this->om->flush();

        return $this->toArray($event);
    }

    public function displayEvents(Workspace $workspace)
    {
        $listEvents = $this->om->getRepository('ClarolineCoreBundle:Event')
            ->findbyWorkspaceId($workspace->getId(), false);
        $data = array();

        foreach ($listEvents as $key => $object) {
            $data[$key]['id'] = $object->getId();
            $data[$key]['title'] = $object->getTitle();
            $data[$key]['allDay'] = $object->getAllDay();
            $data[$key]['start'] = $object->getStart()->getTimestamp();
            $data[$key]['end'] = $object->getEnd()->getTimestamp();
            $data[$key]['color'] = $object->getPriority();
            $data[$key]['description'] = $object->getDescription();
            $data[$key]['owner'] = $object->getUser()->getUsername();

            if ($data[$key]['owner'] === $this->security->getToken()->getUser()->getUsername()) {
                $data[$key]['editable'] = true;
            } else {
                $data[$key]['editable'] = $role;
            }
        }
        return $data;
    }

    public function updateEndDate(Event $event, $dayDelta = 0, $minDelta = 0)
    {
        $event->setEnd($event->getEnd()->getTimeStamp() + $this->toSeconds($dayDelta, $minDelta));
        $this->om->flush();
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
    }

    /**
     * @param Event $event
     * @return array
     */
    public function toArray(Event $event)
    {
        $start = is_null($event->getStart())? null : $event->getStart()->getTimestamp();
        $end = is_null($event->getEnd())? null : $event->getEnd()->getTimestamp();

        return array(
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'start' => $start,
            'end' => $end,
            'color' => $event->getPriority(),
            'allDay' => $event->getAllDay(),
            'owner' => $event->getUser()->getUsername(),
            'description' => $event->getDescription(),
            'editable' => true //for now, only true
        );
    }

    private function checkUserIsAllowedtoWrite(Workspace $workspace, Event $event = null)
    {
        $usr = $this->security->getToken()->getUser();
        $rm = $this->rm->getManagerRole($workspace);
        $ru = $this->rm->getWorkspaceRolesForUser($usr, $workspace);

        if (!is_null($event)) {
            if ($event->getUser()->getUsername() === $usr->getUsername()) {
                return true;
            }
        }

        foreach ($ru as $role) {
            if ($role->getTranslationKey() === $rm->getTranslationKey()) {
                return true;
            }

            return false;
        }
    }

    private function checkUserIsAllowed($permission, Workspace $workspace)
    {
        if (!$this->security->isGranted($permission, $workspace)) {
            throw new AccessDeniedException();
        }
    }

    private function convertEventsToArray($listEvents)
    {
        $data = array();

        foreach ($listEvents as $event) {
            $data[] = $this->toArray($event);
        }

        return $data;
    }

    private function calculRecurrency(Event $event)
    {
        $listEvents = array();

        // it calculs by weeks for now
        for ($i = 1; $i <= $event->getRecurring(); $i++) {
            $temp = clone $event;
            $newStartDate = $temp->getStart()->getTimestamp() + (3600 * 24 * $i);
            $temp->setStart(new \DateTime(date('d-m-Y H:i', $newStartDate)));
            $newEndDate = $temp->getEnd()->getTimestamp() + (3600 * 24 * $i);
            $temp->setEnd(new \DateTime(date('d-m-Y H:i', $newEndDate)));
            $listEvents[$i] = $temp;
            $this->om->persist($listEvents[$i]);

            return $listEvents;
        }
    }

    private function toSeconds($days = 0, $mins = 0)
    {
        return $days * 3600 * 24 + $mins * 60;
    }
}
