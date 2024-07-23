<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Subscriber\Rules;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\WorkspaceEvaluationEvent;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Claroline\OpenBadgeBundle\Manager\RuleManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WorkspaceEvaluationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly RuleManager $manager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EvaluationEvents::WORKSPACE_EVALUATION => 'onWorkspaceEvaluation',
        ];
    }

    public function onWorkspaceEvaluation(WorkspaceEvaluationEvent $event): void
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

    private function awardWorkspaceStatus(User $user, Evaluation $evaluation, Rule $rule): void
    {
        $data = $rule->getData();
        if (!empty($data) && !empty($data['value'])) {
            if (EvaluationStatus::PRIORITY[$data['value']] <= EvaluationStatus::PRIORITY[$evaluation->getStatus()]) {
                $this->manager->grant($rule, $user);
            }
        }
    }

    private function awardWorkspaceScoreAbove(User $user, Evaluation $evaluation, Rule $rule): void
    {
        $data = $rule->getData();
        if (empty($data)) {
            return;
        }

        $scoreProgress = 0;
        if ($evaluation->getScoreMax()) {
            $scoreProgress = ($evaluation->getScore() ?? 0) / $evaluation->getScoreMax() * 100;
        }

        if ($scoreProgress >= $data['value']) {
            $this->manager->grant($rule, $user);
        }
    }

    private function awardWorkspaceCompletedAbove(User $user, Evaluation $evaluation, Rule $rule): void
    {
        $data = $rule->getData();
        if (empty($data)) {
            return;
        }

        $expectedProgression = $data['value'];
        if ($expectedProgression > 100) {
            // progression is a percentage, it can not be over 100
            $expectedProgression = 100;
        }

        if ($evaluation->getProgression() >= $expectedProgression) {
            $this->manager->grant($rule, $user);
        }
    }
}
