<?php

namespace Claroline\EvaluationBundle\Component\Log\Functional;

use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\ResourceEvaluationEvent;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Claroline\LogBundle\Component\Log\AbstractFunctionalLog;
use Claroline\LogBundle\Helper\ColorHelper;

class LogResourceEvaluationEnd extends AbstractFunctionalLog
{
    public static function getName(): string
    {
        return 'evaluation.resource_end';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EvaluationEvents::RESOURCE_EVALUATION => ['logEnd', -25],
        ];
    }

    public function logEnd(ResourceEvaluationEvent $event): void
    {
        $evaluation = $event->getEvaluation();
        if ($event->hasStatusChanged() && $evaluation->isTerminated()) {
            $resourceNode = $event->getResourceNode();

            $message = $this->getTranslator()->trans('evaluation.resource_end_message', [
                '%status%' => ColorHelper::color(
                    strtolower($this->getTranslator()->trans('evaluation_' . $evaluation->getStatus() . '_short', [], 'evaluation')),
                    EvaluationStatus::FAILED === $evaluation->getStatus() ? ColorHelper::DANGER : ColorHelper::SUCCESS
                ),
                '%resource%' => $resourceNode->getName(),
            ], 'log');

            $this->log($message, $resourceNode->getWorkspace(), $resourceNode, $event->getUser());
        }
    }
}
