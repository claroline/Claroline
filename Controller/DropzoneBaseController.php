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
use Icap\DropzoneBundle\Event\Log\LogCorrectionDeleteEvent;
use Icap\DropzoneBundle\Event\Log\LogCorrectionEndEvent;
use Icap\DropzoneBundle\Event\Log\LogCorrectionStartEvent;
use Icap\DropzoneBundle\Event\Log\LogCorrectionUpdateEvent;
use Icap\DropzoneBundle\Event\Log\LogCorrectionValidationChangeEvent;
use Icap\DropzoneBundle\Event\Log\LogCriterionCreateEvent;
use Icap\DropzoneBundle\Event\Log\LogCriterionDeleteEvent;
use Icap\DropzoneBundle\Event\Log\LogCriterionUpdateEvent;
use Icap\DropzoneBundle\Event\Log\LogDocumentCreateEvent;
use Icap\DropzoneBundle\Event\Log\LogDocumentDeleteEvent;
use Icap\DropzoneBundle\Event\Log\LogDocumentOpenEvent;
use Icap\DropzoneBundle\Event\Log\LogDropEndEvent;
use Icap\DropzoneBundle\Event\Log\LogDropEvaluateEvent;
use Icap\DropzoneBundle\Event\Log\LogDropStartEvent;
use Icap\DropzoneBundle\Event\Log\LogDropzoneConfigureEvent;
use Icap\DropzoneBundle\Event\Log\LogDropzoneManualStateChangedEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DropzoneBaseController extends Controller
{
    const CRITERION_PER_PAGE = 10;
    const DROP_PER_PAGE = 10;
    const CORRECTION_PER_PAGE = 10;


    protected function dispatch($event)
    {
        if (
            $event instanceof LogResourceReadEvent or
            $event instanceof LogDropzoneConfigureEvent or
            $event instanceof LogCriterionCreateEvent or
            $event instanceof LogCriterionUpdateEvent or
            $event instanceof LogCriterionDeleteEvent or
            $event instanceof LogDropStartEvent or
            $event instanceof LogDropEndEvent or
            $event instanceof LogDocumentCreateEvent or
            $event instanceof LogDocumentDeleteEvent or
            $event instanceof LogDocumentOpenEvent or
            $event instanceof LogCorrectionStartEvent or
            $event instanceof LogCorrectionEndEvent or
            $event instanceof LogCorrectionUpdateEvent or
            $event instanceof LogCorrectionDeleteEvent or
            $event instanceof LogCorrectionValidationChangeEvent or
            $event instanceof LogDropEvaluateEvent or
            $event instanceof LogDropReportEvent or
            $event instanceof LogDropzoneManualStateChangedEvent
        ) {

            // Other logs are WIP.
            $this->get('event_dispatcher')->dispatch('log', $event);
        }
        //$this->get('event_dispatcher')->dispatch('log', $event);

        return $this;
    }
}