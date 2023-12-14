<?php

namespace Claroline\EvaluationBundle\Component\Log\Functional;

use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\WorkspaceEvaluationEvent;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Claroline\LogBundle\Component\Log\AbstractFunctionalLog;

class LogWorkspaceEvaluationStart extends AbstractFunctionalLog
{
    public static function getName(): string
    {
        return 'evaluation.workspace_start';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EvaluationEvents::WORKSPACE_EVALUATION => ['logStart', -25],
        ];
    }

    public function logStart(WorkspaceEvaluationEvent $event): void
    {
        $evaluation = $event->getEvaluation();
        if ($event->hasStatusChanged() && EvaluationStatus::INCOMPLETE === $evaluation->getStatus()) {
            $workspace = $event->getWorkspace();

            $this->log(
                $this->getTranslator()->trans('evaluation.workspace_start_message', [
                    '%workspace%' => $workspace->getName(),
                ], 'log'),
                $workspace,
                null,
                $event->getUser()
            );
        }
    }
}
