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
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @DI\Service("claroline.manager.agenda_manager")
 */
class AgendaManager
{
    private $om;
    private $security;

    /**
     * @DI\InjectParams({
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "rootDir"            = @DI\Inject("%kernel.root_dir%"),
     *     "security"           = @DI\Inject("security.context")
     * })
     */
    public function __construct(
        ObjectManager $om,
        $rootDir,
        SecurityContextInterface $security
    )
    {
        $this->rootDir = $rootDir;
        $this->om = $om;
        $this->security = $security;
    }

    public function addEvent(Event $event)
    {
        // the end date has to be bigger
        if ($event->getStart() <= $event->getEnd()) {
            $event->setWorkspace($workspace);
            $event->setUser($this->security->getToken()->getUser());
            $this->om->persist($event);
            if ($event->getRecurring() > 0) {
                $this->calculRecurrency($event);
            }
            $this->om->flush();
            $start = is_null($event->getStart())? null : $event->getStart()->getTimestamp();
            $end = is_null($event->getEnd())? null : $event->getEnd()->getTimestamp();
            $data = array(
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'start' => $start,
                'end' => $end,
                'color' => $event->getPriority(),
                'allDay' => $event->getAllDay()
            );
            return array(
                'code' => 200,
                'message' => $data
                ) ;
        }
        return array(
                'code' => 400,
                'message' => 'Start date has to be bigger'
                ) ;
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
     * Import ical files type
     * @param  UploadedFile $file      
     * @param  AbstractWorkspace $workspace 
     * @return int number of events saved
     */
    public function importEvents(UploadedFile $file, $workspace)
    {
        $path = $this->rootDir.'/../web/uploads';
        $ds = DIRECTORY_SEPARATOR;
        $file->move($path);
        $ical = new \ICal($path . $ds . $file->getClientOriginalName());
        $events = $ical->events();
        $this->om->startFlushSuite();
        $count = 0;

        foreach ($events as $i => $event) {
            $e = $this->om->factory('Claroline\CoreBundle\Entity\Event');
            $e->setTitle($event['SUMMARY']);
            $e->setStart($ical->iCalDateToUnixTimestamp($event['DTSTART']));
            $e->setEnd($ical->iCalDateToUnixTimestamp($event['DTEND']));
            $e->setDescription($event['DESCRIPTION']);
            $e->setWorkspace($workspace);
            $e->setUser($this->security->getToken()->getUser());
            $e->setPriority('#01A9DB');
        }
        $this->om->endFlushSuite();     

        return $i;
    }
} 