<?php

namespace Icap\WikiBundle\Controller;

use Claroline\CoreBundle\Event\Log\LogResourceChildUpdateEvent;
use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Claroline\CoreBundle\Event\Log\LogResourceUpdateEvent;
use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Event\Log\LogSectionCreateEvent;
use Icap\WikiBundle\Event\Log\LogSectionDeleteEvent;
use Icap\WikiBundle\Event\Log\LogSectionReadEvent;
use Icap\WikiBundle\Event\Log\LogSectionUpdateEvent;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class Controller extends BaseController
{
    const WIKI_TYPE         = 'icap_wiki';
    const WIKI_SECTION_TYPE    = 'icap_wiki_section';

    /**
     * @param string $permission
     *
     * @param Wiki $wiki
     *
     * @throws AccessDeniedException
     */
    protected function checkAccess($permission, Wiki $wiki)
    {
        $collection = new ResourceCollection(array($wiki->getResourceNode()));
        if (!$this->get('security.context')->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }

        $logEvent = new LogResourceReadEvent($wiki->getResourceNode());
        $this->get('event_dispatcher')->dispatch('log', $logEvent);
    }
    /**
     * @param string $permission
     *
     * @param Wiki $wiki
     *
     * @return bool
     */
    protected function isUserGranted($permission, Wiki $wiki)
    {
        $checkPermission = false;
        if ($this->get('security.context')->isGranted($permission, new ResourceCollection(array($wiki->getResourceNode())))) {
            $checkPermission = true;
        }

        return $checkPermission;
    }

    /**
     * @param $event
     *
     * @return Controller
     */
    protected function dispatch($event)
    {
        $this->get('event_dispatcher')->dispatch('log', $event);

        return $this;
    }

    /**
     * @param Wiki $wiki
     * @param string $childType
     * @param string $action
     * @param array $details
     *
     * @return Controller
     */
    protected function dispatchChildEvent(Wiki $wiki, $childType, $action, $details = array())
    {
        $event = new LogResourceChildUpdateEvent(
            $wiki->getResourceNode(),
            $childType,
            $action,
            $details
        );

        return $this->dispatch($event);        
    }

    

    /**
     * @param Wiki $wiki
     * @param array $changeSet
     *
     * @return Controller
     */
    protected function dispatchWikiUpdateEvent(Wiki $wiki, $changeSet)
    {
        $event = new LogResourceUpdateEvent($wiki->getResourceNode(), $changeSet);
        
        return $this->dispatch($event);
    }

    /**
     * @param Wiki $wiki
     * @param Section $section
     *
     * @return Controller
     */
    protected function dispatchSectionCreateEvent(Wiki $wiki, Section $section)
    {
        $event = new LogSectionCreateEvent($wiki, $section);

        return $this->dispatch($event);
    }

    /**
     * @param Wiki $wiki
     * @param Section $section
     * @param array $changeSet
     *
     * @return Controller
     */
    protected function dispatchSectionMoveEvent(Wiki $wiki, Section $section, $changeSet)
    {
        $event = new LogSectionMoveEvent($wiki, $section, $changeSet);

        return $this->dispatch($event);
    }

    /**
     * @param Wiki $wiki
     * @param Section $section
     * @param array $changeSet
     *
     * @return Controller
     */
    protected function dispatchSectionUpdateEvent(Wiki $wiki, Section $section, $changeSet)
    {
        $event = new LogSectionUpdateEvent($wiki, $section, $changeSet);

        return $this->dispatch($event);
    }

    /**
     * @param Wiki $wiki
     * @param Section $section
     *
     * @return Controller
     */
    protected function dispatchSectionDeleteEvent(Wiki $wiki, Section $section)
    {
        $event = new LogSectionDeleteEvent($wiki, $section);

        return $this->dispatch($event);
    }

}