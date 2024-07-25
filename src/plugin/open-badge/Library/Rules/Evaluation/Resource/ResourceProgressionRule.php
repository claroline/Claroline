<?php

namespace Claroline\OpenBadgeBundle\Library\Rules\Evaluation\Resource;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Claroline\OpenBadgeBundle\Library\Rules\Evaluation\AbstractProgressionRule;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResourceProgressionRule extends AbstractProgressionRule
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ObjectManager $om
    ) {
    }

    public static function getType(): string
    {
        return Rule::RESOURCE_COMPLETED_ABOVE;
    }

    public function getQualifiedUsers(Rule $rule): iterable
    {
        $evaluations = $this->om->getRepository(ResourceUserEvaluation::class)->findBy([
            'resourceNode' => $rule->getResourceNode(),
        ]);

        return $this->checkEvaluationProgressions($rule, $evaluations);
    }

    public function getEvidenceMessage(): string
    {
        $now = new \DateTime();

        return $this->translator->trans('evidence_narrative_resource_completed_above', [
            '%date%' => $now->format('Y-m-d H:i:s'),
        ], 'badge');
    }
}
