<?php

namespace Claroline\EvaluationBundle\Component\Log\Functional;

use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\WorkspaceEvaluationEvent;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Claroline\LogBundle\Component\Log\AbstractFunctionalLog;

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

            switch ($evaluation->getStatus()) {
                case EvaluationStatus::PASSED:
                    $message = $this->getTranslator()->trans('workspace_passed_message', [
                        '%workspace%' => $workspace->getName(),
                    ], 'log');
                    break;
                case EvaluationStatus::FAILED:
                    $message = $this->getTranslator()->trans('workspace_failed_message', [
                        '%workspace%' => $workspace->getName(),
                    ], 'log');
                    break;
                case EvaluationStatus::PARTICIPATED:
                    $message = $this->getTranslator()->trans('workspace_participated_message', [
                        '%workspace%' => $workspace->getName(),
                    ], 'log');
                    break;
                default:
                    $message = $this->getTranslator()->trans('workspace_end_message', [
                        '%workspace%' => $workspace->getName(),
                    ], 'log');
                    break;
            }

            $this->log($message, $workspace, null, $event->getUser());
        }
    }
}
