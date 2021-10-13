<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Listener\Rules;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Event\WorkspaceEvaluationEvent;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Claroline\OpenBadgeBundle\Manager\RuleManager;

class WorkspaceEvaluationListener
{
    /** @var ObjectManager */
    private $om;
    /** @var RuleManager */
    private $manager;

    public function __construct(
        ObjectManager $om,
        RuleManager $manager
    ) {
        $this->om = $om;
        $this->manager = $manager;
    }

    public function onWorkspaceEvaluation(WorkspaceEvaluationEvent $event)
    {
        $evaluation = $event->getEvaluation();

        /** @var Rule[] $rules */
        $rules = $this->om->getRepository(Rule::class)->findBy(['workspace' => $evaluation->getWorkspace()]);

        foreach ($rules as $rule) {
            switch ($rule->getAction()) {
                case Rule::WORKSPACE_SCORE_ABOVE:
                    $this->awardWorkspaceScoreAbove($evaluation->getUser(), $evaluation, $rule);
                    break;
                case Rule::WORKSPACE_COMPLETED_ABOVE:
                    $this->awardWorkspaceCompletedAbove($evaluation->getUser(), $evaluation, $rule);
                    break;
                case Rule::WORKSPACE_STATUS:
                    $this->awardWorkspaceStatus($evaluation->getUser(), $evaluation, $rule);
                    break;
                default:
                    break;
            }
        }
    }

    private function awardWorkspaceStatus(User $user, Evaluation $evaluation, Rule $rule)
    {
        $data = $rule->getData();
        if (!empty($data) && !empty($data['value'])) {
            if (AbstractEvaluation::STATUS_PRIORITY[$data['value']] <= AbstractEvaluation::STATUS_PRIORITY[$evaluation->getStatus()]) {
                $this->manager->grant($rule, $user);
            }
        }
    }

    private function awardWorkspaceScoreAbove(User $user, Evaluation $evaluation, Rule $rule)
    {
        $data = $rule->getData();
        if (isset($data)) {
            $scoreProgress = 0;
            if ($evaluation->getScoreMax()) {
                $scoreProgress = ($evaluation->getScore() ?? 0) / $evaluation->getScoreMax() * 100;
            }

            if ($scoreProgress >= $data['value']) {
                $this->manager->grant($rule, $user);
            }
        }
    }

    private function awardWorkspaceCompletedAbove(User $user, Evaluation $evaluation, Rule $rule)
    {
        $data = $rule->getData();
        $progression = ($evaluation->getProgression() / $evaluation->getProgressionMax()) * 100;
        if ($data && $progression >= $data['value']) {
            $this->manager->grant($rule, $user);
        }
    }
}
