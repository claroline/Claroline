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
     *      "security"          = @DI\Inject("security.context")
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

    /**
     * @param $workspaceId
     * @return list of Events
     */
    public function export($workspaceId = null)
    {
        if (isset($workspaceId)) {
            $listEvents = $this->om->getRepository('ClarolineCoreBundle:Event')->findByWorkspaceId($workspaceId, false);
        } else {
            $usr = $this->security->getToken()->getUser();
            $listDesktop = $this->om->getRepository('ClarolineCoreBundle:Event')->findDesktop($usr, 0);
            $listEventsU = $this->om->getRepository('ClarolineCoreBundle:Event')->findByUser($usr, 0);
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


} 