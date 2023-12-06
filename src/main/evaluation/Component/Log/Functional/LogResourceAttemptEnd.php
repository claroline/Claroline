<?php

namespace Claroline\EvaluationBundle\Component\Log\Functional;

use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\ResourceAttemptEvent;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Claroline\LogBundle\Component\Log\AbstractFunctionalLog;

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
                case EvaluationStatus::PASSED:
                    $message = $this->getTranslator()->trans('attempt_passed_message', [
                        '%resource%' => $resourceNode->getName(),
                    ], 'log');
                    break;
                case EvaluationStatus::FAILED:
                    $message = $this->getTranslator()->trans('attempt_failed_message', [
                        '%resource%' => $resourceNode->getName(),
                    ], 'log');
                    break;
                default:
                    $message = $this->getTranslator()->trans('attempt_end_message', [
                        '%resource%' => $resourceNode->getName(),
                    ], 'log');
                    break;
            }

            $this->log($message, $resourceNode->getWorkspace(), $resourceNode, $event->getUser());
        }
    }
}
