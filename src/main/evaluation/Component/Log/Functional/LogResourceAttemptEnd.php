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

            $message = $this->getTranslator()->trans('evaluation.attempt_end_message', [
                '%status%' => ColorHelper::color(
                    strtolower($this->getTranslator()->trans('evaluation_' . $attempt->getStatus() . '_short', [], 'evaluation')),
                    EvaluationStatus::FAILED === $attempt->getStatus() ? ColorHelper::DANGER : ColorHelper::SUCCESS
                ),
                '%resource%' => $resourceNode->getName(),
            ], 'log');

            $this->log($message, $resourceNode->getWorkspace(), $resourceNode, $event->getUser());
        }
    }
}
