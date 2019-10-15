<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Workspace;

use Claroline\CoreBundle\Event\Resource\ResourceEvaluationEvent;
use Claroline\CoreBundle\Manager\Workspace\EvaluationManager;

class EvaluationListener
{
    /** @var EvaluationManager */
    private $evaluationManager;

    /**
     * EvaluationListener constructor.
     *
     * @param EvaluationManager $evaluationManager
     */
    public function __construct(EvaluationManager $evaluationManager)
    {
        $this->evaluationManager = $evaluationManager;
    }

    /**
     * @param ResourceEvaluationEvent $event
     */
    public function onResourceEvaluation(ResourceEvaluationEvent $event)
    {
        $resourceUserEvaluation = $event->getEvaluation();
        $resourceNode = $resourceUserEvaluation->getResourceNode();
        $workspace = $resourceNode->getWorkspace();
        $user = $resourceUserEvaluation->getUser();

        $this->evaluationManager->computeEvaluation($workspace, $user, $resourceUserEvaluation);
    }
}
