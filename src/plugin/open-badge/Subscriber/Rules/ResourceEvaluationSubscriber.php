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
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\ResourceEvaluationEvent;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Claroline\OpenBadgeBundle\Manager\RuleManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ResourceEvaluationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly RuleManager $manager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EvaluationEvents::RESOURCE_EVALUATION => 'onResourceEvaluation',
        ];
    }

    public function onResourceEvaluation(ResourceEvaluationEvent $event): void
    {
        $evaluation = $event->getEvaluation();

        /** @var Rule[] $rules */
        $rules = $this->om->getRepository(Rule::class)->findBy(['node' => $evaluation->getResourceNode()]);

        foreach ($rules as $rule) {
            switch ($rule->getAction()) {
                case Rule::RESOURCE_SCORE_ABOVE:
                    $this->awardResourceScoreAbove($evaluation->getUser(), $evaluation, $rule);
                    break;
                case Rule::RESOURCE_COMPLETED_ABOVE:
                    $this->awardResourceCompletedAbove($evaluation->getUser(), $evaluation, $rule);
                    break;
                case Rule::RESOURCE_STATUS:
                    $this->awardResourceStatus($evaluation->getUser(), $evaluation, $rule);
                    break;
                default:
                    break;
            }
        }
    }

    private function awardResourceStatus(User $user, ResourceUserEvaluation $evaluation, Rule $rule): void
    {
        $data = $rule->getData();
        if (!empty($data) && !empty($data['value'])) {
            if (EvaluationStatus::PRIORITY[$data['value']] <= EvaluationStatus::PRIORITY[$evaluation->getStatus()]) {
                $this->manager->grant($rule, $user);
            }
        }
    }

    private function awardResourceScoreAbove(User $user, ResourceUserEvaluation $evaluation, Rule $rule): void
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

    private function awardResourceCompletedAbove(User $user, ResourceUserEvaluation $evaluation, Rule $rule): void
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
