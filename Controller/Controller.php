<?php

namespace Icap\WikiBundle\Controller;

use Claroline\CoreBundle\Event\Log\LogResourceChildUpdateEvent;
use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Claroline\CoreBundle\Event\Log\LogResourceUpdateEvent;
use Icap\WikiBundle\Entity\Wiki;
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
}