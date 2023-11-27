<?php

namespace Claroline\EvaluationBundle\Component\Log\Functional;

use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\ResourceEvaluationEvent;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Claroline\LogBundle\Component\Log\AbstractFunctionalLog;

class LogResourceEvaluationStart extends AbstractFunctionalLog
{
    public static function getName(): string
    {
        return 'evaluation.resource_start';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EvaluationEvents::RESOURCE_EVALUATION => ['logStart', 10],
        ];
    }

    public function logStart(ResourceEvaluationEvent $event): void
    {
        $evaluation = $event->getEvaluation();
        if ($event->hasStatusChanged() && EvaluationStatus::INCOMPLETE === $evaluation->getStatus()) {
            $resourceNode = $event->getResourceNode();

            $this->log(
                $this->getTranslator()->trans('resource_start_message', [
                    '%resource%' => $resourceNode->getName(),
                ], 'log'),
                $resourceNode->getWorkspace(),
                $resourceNode,
                $event->getUser()
            );
        }
    }
}
