<?php

namespace Claroline\EvaluationBundle\Component\Log\Functional;

use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\ResourceEvaluationEvent;
use Claroline\LogBundle\Component\Log\AbstractFunctionalLog;

class LogResourceAttemptGive extends AbstractFunctionalLog
{
    public static function getName(): string
    {
        return 'evaluation.attempt_give';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EvaluationEvents::RESOURCE_EVALUATION => ['logGiveAnotherAttempt', -25],
        ];
    }

    public function logGiveAnotherAttempt(ResourceEvaluationEvent $event): void
    {
        if ($event->hasNbAttemptsChanged()) {
            $resourceNode = $event->getResourceNode();

            $message = $this->getTranslator()->trans('evaluation.attempt_give_message', [
                '%resource%' => $resourceNode->getName(),
            ], 'log');

            $this->log($message, $resourceNode->getWorkspace(), $resourceNode, $event->getUser());
        }
    }
}
