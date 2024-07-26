<?php

namespace Claroline\EvaluationBundle\Component\Log\Functional;

use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\WorkspaceEvaluationEvent;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Claroline\LogBundle\Component\Log\AbstractFunctionalLog;
use Claroline\LogBundle\Helper\ColorHelper;

class LogWorkspaceEvaluationEnd extends AbstractFunctionalLog
{
    public static function getName(): string
    {
        return 'evaluation.workspace_end';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EvaluationEvents::WORKSPACE_EVALUATION => ['logEnd', -25],
        ];
    }

    public function logEnd(WorkspaceEvaluationEvent $event): void
    {
        $evaluation = $event->getEvaluation();
        if ($event->hasStatusChanged() && $evaluation->isTerminated()) {
            $workspace = $event->getWorkspace();

            $message = $this->getTranslator()->trans('evaluation.workspace_end_message', [
                '%status%' => ColorHelper::color(
                    strtolower($this->getTranslator()->trans('evaluation_'.$evaluation->getStatus().'_short', [], 'evaluation')),
                    EvaluationStatus::FAILED === $evaluation->getStatus() ? ColorHelper::DANGER : ColorHelper::SUCCESS
                ),
                '%workspace%' => $workspace->getName(),
            ], 'log');

            $this->log($message, $workspace, null, $event->getUser());
        }
    }
}
