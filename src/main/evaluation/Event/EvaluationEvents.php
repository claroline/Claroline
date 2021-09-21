<?php

namespace Claroline\EvaluationBundle\Event;

final class EvaluationEvents
{
    /**
     * The WORKSPACE event occurs when the workspace evaluation of a user has been updated.
     *
     * @Event("Claroline\EvaluationBundle\Event\WorkspaceEvaluationEvent")
     */
    public const WORKSPACE = 'workspace.evaluate';

    /**
     * The RESOURCE event occurs when the resource evaluation of a user has been updated.
     *
     * @Event("Claroline\EvaluationBundle\Event\ResourceEvaluationEvent")
     */
    public const RESOURCE = 'resource_evaluation';
}
