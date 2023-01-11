<?php

namespace Claroline\EvaluationBundle\Event;

final class EvaluationEvents
{
    /**
     * The WORKSPACE_EVALUATION event occurs when the workspace evaluation of a user has been created/updated.
     *
     * @Event("Claroline\EvaluationBundle\Event\WorkspaceEvaluationEvent")
     */
    public const WORKSPACE_EVALUATION = 'workspace_evaluation';

    /**
     * The RESOURCE_ATTEMPT event occurs when a resource attempt has been created or updated.
     * NB. It's only dispatched if the score, progression of status of the attempt has been changed.
     *
     * @Event("Claroline\EvaluationBundle\Event\ResourceAttemptEvent")
     */
    public const RESOURCE_ATTEMPT = 'resource_attempt';

    /**
     * The RESOURCE_EVALUATION event occurs when a resource evaluation has been created/updated.
     * NB. It's only dispatched if the score, progression of status of the evaluation has been changed.
     *
     * @Event("Claroline\EvaluationBundle\Event\ResourceEvaluationEvent")
     */
    public const RESOURCE_EVALUATION = 'resource_evaluation';
}
