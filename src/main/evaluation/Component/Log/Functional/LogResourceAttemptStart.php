<?php

namespace Claroline\EvaluationBundle\Component\Log\Functional;

use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\ResourceAttemptEvent;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Claroline\LogBundle\Component\Log\AbstractFunctionalLog;

class LogResourceAttemptStart extends AbstractFunctionalLog
{
    public static function getName(): string
    {
        return 'evaluation.attempt_start';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EvaluationEvents::RESOURCE_ATTEMPT => ['logStart', -25],
        ];
    }

    public function logStart(ResourceAttemptEvent $event): void
    {
        $attempt = $event->getAttempt();
        if ($event->hasStatusChanged() && EvaluationStatus::INCOMPLETE === $attempt->getStatus()) {
            $resourceNode = $event->getResourceNode();

            $this->log(
                $this->getTranslator()->trans('evaluation.attempt_start_message', [
                    '%resource%' => $resourceNode->getName(),
                ], 'log'),
                $resourceNode->getWorkspace(),
                $resourceNode,
                $event->getUser()
            );
        }
    }
}
