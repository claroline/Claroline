<?php
/**
 * Created by : Vincent SAISSET
 * Modified by : Eric VINCENT (mars 2015)
 * Date: 22/08/13
 * Time: 09:30.
 */

namespace Innova\CollecticielBundle\Controller;

use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
/* Logs for Innova */
use Innova\CollecticielBundle\Event\Log\LogCommentCreateEvent;
use Innova\CollecticielBundle\Event\Log\LogCommentReadCreateEvent;
/* end Logs for Innova */
use Innova\CollecticielBundle\Event\Log\LogCorrectionDeleteEvent;
use Innova\CollecticielBundle\Event\Log\LogCorrectionEndEvent;
use Innova\CollecticielBundle\Event\Log\LogCorrectionStartEvent;
use Innova\CollecticielBundle\Event\Log\LogCorrectionUpdateEvent;
use Innova\CollecticielBundle\Event\Log\LogCorrectionValidationChangeEvent;
use Innova\CollecticielBundle\Event\Log\LogCriterionCreateEvent;
use Innova\CollecticielBundle\Event\Log\LogCriterionDeleteEvent;
use Innova\CollecticielBundle\Event\Log\LogCriterionUpdateEvent;
use Innova\CollecticielBundle\Event\Log\LogDocumentCreateEvent;
use Innova\CollecticielBundle\Event\Log\LogDocumentDeleteEvent;
use Innova\CollecticielBundle\Event\Log\LogDocumentOpenEvent;
use Innova\CollecticielBundle\Event\Log\LogDropEndEvent;
use Innova\CollecticielBundle\Event\Log\LogDropEvaluateEvent;
use Innova\CollecticielBundle\Event\Log\LogDropStartEvent;
use Innova\CollecticielBundle\Event\Log\LogDropReportEvent;
use Innova\CollecticielBundle\Event\Log\LogDropzoneConfigureEvent;
use Innova\CollecticielBundle\Event\Log\LogDropzoneManualStateChangedEvent;
use Innova\CollecticielBundle\Event\Log\LogDropzoneManualRequestSentEvent;
use Innova\CollecticielBundle\Event\Log\LogDropzoneReturnReceiptEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DropzoneBaseController extends Controller
{
    const CRITERION_PER_PAGE = 10;
    const DROP_PER_PAGE = 50;
    const CORRECTION_PER_PAGE = 10;

    protected function dispatch($event)
    {
        if (
            $event instanceof LogResourceReadEvent ||
            $event instanceof LogDropzoneConfigureEvent ||
            /* Logs for Innova */
            $event instanceof LogCommentReadCreateEvent ||
            $event instanceof LogCommentCreateEvent ||
            $event instanceof LogDropzoneManualRequestSentEvent ||
            /* end Logs for Innova */
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
            $event instanceof LogDropzoneManualStateChangedEvent ||
            $event instanceof LogDropzoneReturnReceiptEvent
        ) {
            // Other logs are WIP.
            $this->get('event_dispatcher')->dispatch('log', $event);
        }

        return $this;
    }
}
