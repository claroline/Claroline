<?php

namespace Claroline\EvaluationBundle\Repository\UserEvaluation;

class WorkspaceEvaluationRepository extends AbstractEvaluationRepository
{
    protected static function getSubjectProp(): string
    {
        return 'workspace';
    }
}
