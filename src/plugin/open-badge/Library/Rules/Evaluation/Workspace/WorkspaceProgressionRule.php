<?php

namespace Claroline\OpenBadgeBundle\Library\Rules\Evaluation\Workspace;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Claroline\OpenBadgeBundle\Library\Rules\Evaluation\AbstractProgressionRule;
use Symfony\Contracts\Translation\TranslatorInterface;

class WorkspaceProgressionRule extends AbstractProgressionRule
{
    /** @var TranslatorInterface */
    private $translator;
    /** @var ObjectManager */
    private $om;

    public function __construct(
        TranslatorInterface $translator,
        ObjectManager $om
    ) {
        $this->translator = $translator;
        $this->om = $om;
    }

    public static function getType(): string
    {
        return Rule::WORKSPACE_COMPLETED_ABOVE;
    }

    public function getQualifiedUsers(Rule $rule): iterable
    {
        $evaluations = $this->om->getRepository(Evaluation::class)->findBy([
            'workspace' => $rule->getWorkspace(),
        ]);

        return $this->checkEvaluationProgressions($rule, $evaluations);
    }

    public function getEvidenceMessage(): string
    {
        $now = new \DateTime();

        return $this->translator->trans('evidence_narrative_workspace_completed_above', [
            '%date%' => $now->format('Y-m-d H:i:s'),
        ], 'badge');
    }
}
