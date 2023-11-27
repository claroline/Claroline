<?php

namespace Claroline\EvaluationBundle\Component\Log\Functional;

use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\ResourceEvaluationEvent;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Claroline\LogBundle\Component\Log\AbstractFunctionalLog;

class LogResourceEvaluationEnd extends AbstractFunctionalLog
{
    public static function getName(): string
    {
        return 'evaluation.resource_end';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EvaluationEvents::RESOURCE_EVALUATION => ['logEnd', 10],
        ];
    }

    public function logEnd(ResourceEvaluationEvent $event): void
    {
        $evaluation = $event->getEvaluation();
        if ($event->hasStatusChanged() && $evaluation->isTerminated()) {
            $resourceNode = $event->getResourceNode();

            switch ($evaluation->getStatus()) {
                case EvaluationStatus::PASSED:
                    $message = $this->getTranslator()->trans('resource_passed_message', [
                        '%resource%' => $resourceNode->getName(),
                    ], 'log');
                    break;
                case EvaluationStatus::FAILED:
                    $message = $this->getTranslator()->trans('resource_failed_message', [
                        '%resource%' => $resourceNode->getName(),
                    ], 'log');
                    break;
                case EvaluationStatus::PARTICIPATED:
                    $message = $this->getTranslator()->trans('resource_participated_message', [
                        '%resource%' => $resourceNode->getName(),
                    ], 'log');
                    break;
                default:
                    $message = $this->getTranslator()->trans('resource_end_message', [
                        '%resource%' => $resourceNode->getName(),
                    ], 'log');
                    break;
            }

            $this->log($message, $resourceNode->getWorkspace(), $resourceNode, $event->getUser());
        }
    }
}
