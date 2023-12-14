<?php

namespace Claroline\EvaluationBundle\Component\Log\Functional;

use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\ResourceAttemptEvent;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Claroline\LogBundle\Component\Log\AbstractFunctionalLog;
use Claroline\LogBundle\Helper\ColorHelper;

class LogResourceAttemptEnd extends AbstractFunctionalLog
{
    public static function getName(): string
    {
        return 'evaluation.attempt_end';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EvaluationEvents::RESOURCE_ATTEMPT => ['logEnd', -25],
        ];
    }

    public function logEnd(ResourceAttemptEvent $event): void
    {
        $attempt = $event->getAttempt();
        if ($event->hasStatusChanged() && $attempt->isTerminated()) {
            $resourceNode = $event->getResourceNode();

            switch ($attempt->getStatus()) {
                case EvaluationStatus::FAILED:
                    $status = ColorHelper::danger(
                        strtolower($this->getTranslator()->trans('evaluation_failed_short', [], 'evaluation'))
                    );
                    break;
                default:
                    $status = ColorHelper::success(
                        strtolower($this->getTranslator()->trans('evaluation_'.$attempt->getStatus().'_short', [], 'evaluation'))
                    );
                    break;
            }

            $message = $this->getTranslator()->trans('evaluation.attempt_end_message', [
                '%status%' => $status,
                '%resource%' => $resourceNode->getName(),
            ], 'log');

            $this->log($message, $resourceNode->getWorkspace(), $resourceNode, $event->getUser());
        }
    }
}
