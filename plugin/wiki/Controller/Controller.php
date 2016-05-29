<?php

namespace Icap\WikiBundle\Controller;

use Claroline\CoreBundle\Event\Log\LogResourceChildUpdateEvent;
use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Claroline\CoreBundle\Event\Log\LogResourceUpdateEvent;
use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Entity\Contribution;
use Icap\WikiBundle\Event\Log\LogSectionCreateEvent;
use Icap\WikiBundle\Event\Log\LogSectionDeleteEvent;
use Icap\WikiBundle\Event\Log\LogSectionRestoreEvent;
use Icap\WikiBundle\Event\Log\LogSectionRemoveEvent;
use Icap\WikiBundle\Event\Log\LogSectionMoveEvent;
use Icap\WikiBundle\Event\Log\LogSectionUpdateEvent;
use Icap\WikiBundle\Event\Log\LogContributionCreateEvent;
use Icap\WikiBundle\Event\Log\LogWikiConfigureEvent;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class Controller extends BaseController
{
    const WIKI_TYPE = 'icap_wiki';
    const WIKI_SECTION_TYPE = 'icap_wiki_section';

    /**
     * @param string $permission
     * @param Wiki   $wiki
     *
     * @throws AccessDeniedException
     */
    protected function checkAccess($permission, Wiki $wiki)
    {
        $collection = new ResourceCollection(array($wiki->getResourceNode()));
        if (!$this->get('security.authorization_checker')->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }

        $logEvent = new LogResourceReadEvent($wiki->getResourceNode());
        $this->get('event_dispatcher')->dispatch('log', $logEvent);
    }

    /**
     * @param string $permission
     * @param Wiki   $wiki
     *
     * @return bool
     */
    protected function isUserGranted($permission, Wiki $wiki, $collection = null)
    {
        if ($collection === null) {
            $collection = new ResourceCollection(array($wiki->getResourceNode()));
        }
        $checkPermission = false;
        if ($this->get('security.authorization_checker')->isGranted($permission, $collection)) {
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
     * @param Wiki   $wiki
     * @param string $childType
     * @param string $action
     * @param array  $details
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
     * @param Wiki  $wiki
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
     * @param Wiki    $wiki
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
     * @param Wiki    $wiki
     * @param Section $section
     * @param array   $changeSet
     *
     * @return Controller
     */
    protected function dispatchSectionMoveEvent(Wiki $wiki, Section $section, $changeSet)
    {
        $event = new LogSectionMoveEvent($wiki, $section, $changeSet);

        return $this->dispatch($event);
    }

    /**
     * @param Wiki    $wiki
     * @param Section $section
     * @param array   $changeSet
     *
     * @return Controller
     */
    protected function dispatchSectionUpdateEvent(Wiki $wiki, Section $section, $changeSet)
    {
        $event = new LogSectionUpdateEvent($wiki, $section, $changeSet);

        return $this->dispatch($event);
    }

    /**
     * @param Wiki    $wiki
     * @param Section $section
     *
     * @return Controller
     */
    protected function dispatchSectionDeleteEvent(Wiki $wiki, Section $section)
    {
        $event = new LogSectionDeleteEvent($wiki, $section);

        return $this->dispatch($event);
    }

    /**
     * @param Wiki    $wiki
     * @param Section $section
     *
     * @return Controller
     */
    protected function dispatchSectionRemoveEvent(Wiki $wiki, Section $section)
    {
        $event = new LogSectionRemoveEvent($wiki, $section);

        return $this->dispatch($event);
    }

    /**
     * @param Wiki    $wiki
     * @param Section $section
     *
     * @return Controller
     */
    protected function dispatchSectionRestoreEvent(Wiki $wiki, Section $section)
    {
        $event = new LogSectionRestoreEvent($wiki, $section);

        return $this->dispatch($event);
    }

    /**
     * @param Wiki         $wiki
     * @param Section      $section
     * @param Contribution $contribution
     *
     * @return Controller
     */
    protected function dispatchContributionCreateEvent(Wiki $wiki, Section $section, Contribution $contribution)
    {
        $event = new LogContributionCreateEvent($wiki, $section, $contribution);

        return $this->dispatch($event);
    }

    /**
     * @param Wiki  $wiki
     * @param array $changeSet
     *
     * @return Controller
     */
    protected function dispatchWikiConfigureEvent(Wiki $wiki, $changeSet)
    {
        $event = new LogWikiConfigureEvent($wiki, $changeSet);

        return $this->dispatch($event);
    }

    /**
     * Retrieve a section from database.
     *
     * @param Wiki $wiki
     * @param int  $sectionId
     *
     * @return Section $section
     */
    protected function getSection($wiki, $sectionId)
    {
        $section = $this
            ->get('icap.wiki.section_repository')
            ->findOneBy(array('id' => $sectionId, 'wiki' => $wiki));
        if ($section === null) {
            throw new NotFoundHttpException();
        }

        return $section;
    }

    /**
     * Retrieve a section from database.
     *
     * @param Section $section
     * @param int     $contributionId
     *
     * @return Section $contri
     */
    protected function getContribution($section, $contributionId)
    {
        $contribution = $this
            ->get('icap.wiki.contribution_repository')
            ->findOneBy(array('id' => $contributionId, 'section' => $section));
        if ($section === null) {
            throw new NotFoundHttpException();
        }

        return $contribution;
    }

    /**
     * Retrieve logged user. If anonymous return null.
     *
     * @return user
     */
    protected function getLoggedUser()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if (is_string($user)) {
            $user = null;
        }

        return $user;
    }
}
