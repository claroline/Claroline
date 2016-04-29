<?php
/**
 * Created by : Vincent SAISSET
 * Date: 22/08/13
 * Time: 09:30.
 */

namespace Icap\DropzoneBundle\Controller;

use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
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
use Icap\DropzoneBundle\Event\Log\LogDropReportEvent;
use Icap\DropzoneBundle\Event\Log\LogDropzoneConfigureEvent;
use Icap\DropzoneBundle\Event\Log\LogDropzoneManualStateChangedEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DropzoneBaseController extends Controller
{
    const CRITERION_PER_PAGE = 10;
    const DROP_PER_PAGE = 10;
    const CORRECTION_PER_PAGE = 10;

    protected function dispatch($event)
    {
        if (
            $event instanceof LogResourceReadEvent ||
            $event instanceof LogDropzoneConfigureEvent ||
            $event instanceof LogCriterionCreateEvent ||
            $event instanceof LogCriterionUpdateEvent ||
            $event instanceof LogCriterionDeleteEvent ||
            $event instanceof LogDropStartEvent ||
            $event instanceof LogDropEndEvent ||
            $event instanceof LogDocumentCreateEvent ||
            $event instanceof LogDocumentDeleteEvent ||
            $event instanceof LogDocumentOpenEvent ||
            $event instanceof LogCorrectionStartEvent ||
            $event instanceof LogCorrectionEndEvent ||
            $event instanceof LogCorrectionUpdateEvent ||
            $event instanceof LogCorrectionDeleteEvent ||
            $event instanceof LogCorrectionValidationChangeEvent ||
            $event instanceof LogDropEvaluateEvent ||
            $event instanceof LogDropReportEvent ||
            $event instanceof LogDropzoneManualStateChangedEvent
        ) {

            // Other logs are WIP.
            $this->get('event_dispatcher')->dispatch('log', $event);
        }
        //$this->get('event_dispatcher')->dispatch('log', $event);

        return $this;
    }
}
