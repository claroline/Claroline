<?php
/**
 * Created by : Vincent SAISSET
 * Date: 22/08/13
 * Time: 09:30
 */

namespace Icap\DropzoneBundle\Controller;

use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Icap\DropzoneBundle\Entity\Dropzone;
use Icap\DropzoneBundle\Event\Log\LogCriterionCreateEvent;
use Icap\DropzoneBundle\Event\Log\LogCriterionDeleteEvent;
use Icap\DropzoneBundle\Event\Log\LogCriterionUpdateEvent;
use Icap\DropzoneBundle\Event\Log\LogDropzoneConfigureEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DropzoneBaseController extends Controller
{
    const CRITERION_PER_PAGE = 10;
    const DROP_PER_PAGE = 10;

    protected function isAllow(Dropzone $dropzone, $actionName)
    {
        $collection = new ResourceCollection(array($dropzone->getResourceNode()));
        if (false === $this->get('security.context')->isGranted($actionName, $collection)) {
            throw new AccessDeniedException();
        }
    }

    protected function isAllowToEdit(Dropzone $dropzone)
    {
        $this->isAllow($dropzone, 'EDIT');
//        $log = new LogResourceUpdateEvent($dropzone->getResourceNode(), array());
//        $this->get('event_dispatcher')->dispatch('log', $log);
    }

    protected function isAllowToOpen(Dropzone $dropzone)
    {
        $this->isAllow($dropzone, 'OPEN');

        $event = new LogResourceReadEvent($dropzone->getResourceNode());
        $this->dispatch($event);
    }

    protected function dispatch($event)
    {
        if (
                $event instanceof LogResourceReadEvent or
                $event instanceof LogDropzoneConfigureEvent or
                $event instanceof LogCriterionCreateEvent or
                $event instanceof LogCriterionUpdateEvent or
                $event instanceof LogCriterionDeleteEvent
        ) {
            // Other logs are WIP.
            $this->get('event_dispatcher')->dispatch('log', $event);
        }
        //$this->get('event_dispatcher')->dispatch('log', $event);

        return $this;
    }
}