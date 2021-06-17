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
use Claroline\CoreBundle\Event\UserEvaluationEvent;
use Claroline\EvaluationBundle\Entity\Evaluation\AbstractEvaluation;
use Claroline\OpenBadgeBundle\Entity\Evidence;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Claroline\OpenBadgeBundle\Manager\RuleManager;
use Symfony\Component\Translation\TranslatorInterface;

class WorkspaceEvaluationListener
{
    /** @var ObjectManager */
    private $om;

    /** @var TranslatorInterface */
    private $translator;

    /** @var RuleManager */
    private $manager;

    /**
     * RuleListener constructor.
     */
    public function __construct(
        ObjectManager $om,
        TranslatorInterface $translator,
        RuleManager $manager
    ) {
        $this->om = $om;
        $this->translator = $translator;
        $this->manager = $manager;
    }

    public function onWorkspaceEvaluation(UserEvaluationEvent $event)
    {
        /** @var Evaluation $evaluation */
        $evaluation = $event->getEvaluation();

        /** @var Rule[] $rules */
        $rules = $this->om->getRepository(Rule::class)->findBy(['workspace' => $evaluation->getWorkspace()]);

        foreach ($rules as $rule) {
            switch ($rule->getAction()) {
                case Rule::WORKSPACE_PASSED:
                    $this->awardWorkspacePassed($evaluation->getUser(), $evaluation, $rule);
                    break;
                case Rule::WORKSPACE_SCORE_ABOVE:
                    $this->awardWorkspaceScoreAbove($evaluation->getUser(), $evaluation, $rule);
                    break;
                case Rule::WORKSPACE_COMPLETED_ABOVE:
                    $this->awardWorkspaceCompletedAbove($evaluation->getUser(), $evaluation, $rule);
                    break;
                case Rule::WORKSPACE_PARTICIPATED:
                    $this->awardWorkspaceParticipated($evaluation->getUser(), $evaluation, $rule);
                    break;
                default:
                    break;
            }
        }
    }

    private function awardWorkspacePassed(User $user, Evaluation $evaluation, Rule $rule)
    {
        if (AbstractEvaluation::STATUS_PRIORITY[AbstractEvaluation::STATUS_PASSED] <= AbstractEvaluation::STATUS_PRIORITY[$evaluation->getStatus()]) {
            $evidence = new Evidence();
            $now = new \DateTime();
            $evidence->setNarrative($this->translator->trans(
                'evidence_narrative_workspace_passed',
                [
                    '%date%' => $now->format('Y-m-d H:i:s'),
                ],
                'badge'
            ));
            $evidence->setRule($rule);
            $evidence->setName(Rule::WORKSPACE_PASSED);
            $evidence->setWorkspaceEvidence($evaluation);
            $evidence->setUser($user);

            $this->om->persist($evidence);
            $this->om->flush();

            $this->manager->verifyAssertion($user, $rule->getBadge());
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
                $evidence = new Evidence();
                $now = new \DateTime();
                $evidence->setNarrative($this->translator->trans(
                    'evidence_narrative_workspace_score_above',
                    [
                        '%date%' => $now->format('Y-m-d H:i:s'),
                    ],
                    'badge'
                ));
                $evidence->setRule($rule);
                $evidence->setName(Rule::WORKSPACE_SCORE_ABOVE);
                $evidence->setWorkspaceEvidence($evaluation);
                $evidence->setUser($user);

                $this->om->persist($evidence);
                $this->om->flush();

                $this->manager->verifyAssertion($user, $rule->getBadge());
            }
        }
    }

    private function awardWorkspaceCompletedAbove(User $user, Evaluation $evaluation, Rule $rule)
    {
        $data = $rule->getData();
        $progression = ($evaluation->getProgression() / $evaluation->getProgressionMax()) * 100;
        if ($data && $progression >= $data['value']) {
            $evidence = new Evidence();
            $now = new \DateTime();
            $evidence->setNarrative($this->translator->trans(
                'evidence_narrative_workspace_completed_above',
                [
                    '%date%' => $now->format('Y-m-d H:i:s'),
                ],
                'badge'
            ));
            $evidence->setRule($rule);
            $evidence->setName(Rule::WORKSPACE_COMPLETED_ABOVE);
            $evidence->setWorkspaceEvidence($evaluation);
            $evidence->setUser($user);

            $this->om->persist($evidence);
            $this->om->flush();

            $this->manager->verifyAssertion($user, $rule->getBadge());
        }
    }

    private function awardWorkspaceParticipated(User $user, Evaluation $evaluation, Rule $rule)
    {
        if (AbstractEvaluation::STATUS_PRIORITY[AbstractEvaluation::STATUS_PARTICIPATED] <= AbstractEvaluation::STATUS_PRIORITY[$evaluation->getStatus()]) {
            $evidence = new Evidence();
            $now = new \DateTime();
            $evidence->setNarrative($this->translator->trans(
                'evidence_narrative_workspace_participated',
                [
                    '%date%' => $now->format('Y-m-d H:i:s'),
                ],
                'badge'
            ));
            $evidence->setRule($rule);
            $evidence->setName(Rule::WORKSPACE_PARTICIPATED);
            $evidence->setWorkspaceEvidence($evaluation);
            $evidence->setUser($user);

            $this->om->persist($evidence);
            $this->om->flush();

            $this->manager->verifyAssertion($user, $rule->getBadge());
        }
    }
}
