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
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\EvaluateResourceEvent;
use Claroline\EvaluationBundle\Entity\Evaluation\AbstractEvaluation;
use Claroline\OpenBadgeBundle\Entity\Evidence;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Claroline\OpenBadgeBundle\Manager\RuleManager;
use Symfony\Component\Translation\TranslatorInterface;

class ResourceEvaluationListener
{
    /** @var ObjectManager */
    private $om;

    /** @var TranslatorInterface */
    private $translator;

    /** @var RuleManager */
    private $manager;

    public function __construct(
        ObjectManager $om,
        TranslatorInterface $translator,
        RuleManager $manager
    ) {
        $this->om = $om;
        $this->translator = $translator;
        $this->manager = $manager;
    }

    public function onResourceEvaluation(EvaluateResourceEvent $event)
    {
        /** @var ResourceUserEvaluation $evaluation */
        $evaluation = $event->getEvaluation();

        /** @var Rule[] $rules */
        $rules = $this->om->getRepository(Rule::class)->findBy(['node' => $evaluation->getResourceNode()]);

        foreach ($rules as $rule) {
            switch ($rule->getAction()) {
                case Rule::RESOURCE_PASSED:
                    $this->awardResourcePassed($evaluation->getUser(), $evaluation, $rule);
                    break;
                case Rule::RESOURCE_SCORE_ABOVE:
                    $this->awardResourceScoreAbove($evaluation->getUser(), $evaluation, $rule);
                    break;
                case Rule::RESOURCE_COMPLETED_ABOVE:
                    $this->awardResourceCompletedAbove($evaluation->getUser(), $evaluation, $rule);
                    break;
                case Rule::RESOURCE_PARTICIPATED:
                    $this->awardResourceParticipated($evaluation->getUser(), $evaluation, $rule);
                    break;
                default:
                    break;
            }
        }
    }

    private function awardResourcePassed(User $user, ResourceUserEvaluation $evaluation, Rule $rule)
    {
        if (AbstractEvaluation::STATUS_PRIORITY[AbstractEvaluation::STATUS_PASSED] <= AbstractEvaluation::STATUS_PRIORITY[$evaluation->getStatus()]) {
            $evidence = new Evidence();
            $now = new \DateTime();
            $evidence->setNarrative($this->translator->trans(
                'evidence_narrative_resource_passed',
                [
                    '%date%' => $now->format('Y-m-d H:i:s'),
                ],
                'badge'
            ));
            $evidence->setRule($rule);
            $evidence->setName(Rule::RESOURCE_PASSED);
            $evidence->setResourceEvidence($evaluation);
            $evidence->setUser($user);

            $this->om->persist($evidence);
            $this->om->flush();

            $this->manager->verifyAssertion($user, $rule->getBadge());
        }
    }

    private function awardResourceScoreAbove(User $user, ResourceUserEvaluation $evaluation, Rule $rule)
    {
        $data = $rule->getData();
        if (isset($data)) {
            $scoreProgress = 0;
            if ($evaluation->getScoreMax()) {
                $scoreProgress = ($evaluation->getScore() ?? 0) / $evaluation->getScoreMax() * 100;
            }

            if ($scoreProgress >= $data['value']) {
                $evidence = new Evidence();
                $now = new \DateTime();
                $evidence->setNarrative($this->translator->trans(
                    'evidence_narrative_resource_score_above',
                    [
                        '%date%' => $now->format('Y-m-d H:i:s'),
                    ],
                    'badge'
                ));
                $evidence->setRule($rule);
                $evidence->setName(Rule::RESOURCE_SCORE_ABOVE);
                $evidence->setResourceEvidence($evaluation);
                $evidence->setUser($user);

                $this->om->persist($evidence);
                $this->om->flush();

                $this->manager->verifyAssertion($user, $rule->getBadge());
            }
        }
    }

    private function awardResourceCompletedAbove(User $user, ResourceUserEvaluation $evaluation, Rule $rule)
    {
        $data = $rule->getData();
        $progression = ($evaluation->getProgression() / $evaluation->getProgressionMax()) * 100;
        if ($data && $progression >= $data['value']) {
            $evidence = new Evidence();
            $now = new \DateTime();
            $evidence->setNarrative($this->translator->trans(
                'evidence_narrative_resource_completed_above',
                [
                    '%date%' => $now->format('Y-m-d H:i:s'),
                ],
                'badge'
            ));
            $evidence->setRule($rule);
            $evidence->setName(Rule::RESOURCE_COMPLETED_ABOVE);
            $evidence->setResourceEvidence($evaluation);
            $evidence->setUser($user);

            $this->om->persist($evidence);
            $this->om->flush();

            $this->manager->verifyAssertion($user, $rule->getBadge());
        }
    }

    private function awardResourceParticipated(User $user, ResourceUserEvaluation $evaluation, Rule $rule)
    {
        if (AbstractEvaluation::STATUS_PRIORITY[AbstractEvaluation::STATUS_PARTICIPATED] <= AbstractEvaluation::STATUS_PRIORITY[$evaluation->getStatus()]) {
            $evidence = new Evidence();
            $now = new \DateTime();
            $evidence->setNarrative($this->translator->trans(
                'evidence_narrative_resource_participated',
                [
                    '%date%' => $now->format('Y-m-d H:i:s'),
                ],
                'badge'
            ));
            $evidence->setRule($rule);
            $evidence->setName(Rule::RESOURCE_PARTICIPATED);
            $evidence->setResourceEvidence($evaluation);
            $evidence->setUser($user);

            $this->om->persist($evidence);
            $this->om->flush();

            $this->manager->verifyAssertion($user, $rule->getBadge());
        }
    }
}
