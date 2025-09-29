<?php

namespace Claroline\EvaluationBundle\Repository\UserEvaluation;

class SequenceEvaluationRepository extends AbstractEvaluationRepository
{
    protected static function getSubjectProp(): string
    {
        return 'sequence';
    }
}
