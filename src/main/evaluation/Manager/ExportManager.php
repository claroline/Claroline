<?php

namespace Claroline\EvaluationBundle\Manager;

use Claroline\AppBundle\API\Finder\FinderFactory;
use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Claroline\EvaluationBundle\Entity\UserEvaluation\AbstractUserEvaluation;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceEvaluation;
use Claroline\EvaluationBundle\Entity\UserEvaluation\SequenceEvaluation;
use Claroline\EvaluationBundle\Entity\UserEvaluation\WorkspaceEvaluation;
use Claroline\EvaluationBundle\Finder\ResourceEvaluationType;
use Claroline\EvaluationBundle\Finder\SequenceEvaluationType;
use Claroline\EvaluationBundle\Finder\WorkspaceEvaluationType;
use Claroline\EvaluationBundle\Library\EvaluationOptions;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExportManager
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly FinderFactory $finderFactory
    ) {
    }

    public function exportResourceEvaluations(ResourceNode $resourceNode): mixed
    {
        $results = $this->finderFactory->create(ResourceEvaluationType::class)
            ->submit(new FinderQuery(null, ['resourceNode' => $resourceNode->getUuid()]))
            ->getResult(function (ResourceEvaluation $evaluation): array {
                return array_merge($this->formatEvaluation($evaluation), [
                    $evaluation->getEstimatedDuration(),
                ]);
            })
            ->getItems();

        return $this->outputCsv([
            $this->translator->trans('last_name', [], 'platform'),
            $this->translator->trans('first_name', [], 'platform'),
            $this->translator->trans('start_date', [], 'platform'),
            $this->translator->trans('end_date', [], 'platform'),
            $this->translator->trans('last_activity', [], 'platform'),
            $this->translator->trans('progression', [], 'platform'),
            $this->translator->trans('status', [], 'platform'),
            $this->translator->trans('score', [], 'platform'),
            $this->translator->trans('total', [], 'platform'),
            $this->translator->trans('estimated_duration', [], 'platform'),
        ], $results);
    }

    public function exportSequenceEvaluations(Sequence $sequence): mixed
    {
        $results = $this->finderFactory->create(SequenceEvaluationType::class)
            ->submit(new FinderQuery(null, ['sequence' => $sequence->getUuid()]))
            ->getResult(function (SequenceEvaluation $evaluation): array {
                return array_merge($this->formatEvaluation($evaluation), [
                    $evaluation->getEstimatedDuration(),
                    $evaluation->isCertified(),
                ]);
            })
            ->getItems();

        return $this->outputCsv([
            $this->translator->trans('last_name', [], 'platform'),
            $this->translator->trans('first_name', [], 'platform'),
            $this->translator->trans('start_date', [], 'platform'),
            $this->translator->trans('end_date', [], 'platform'),
            $this->translator->trans('last_activity', [], 'platform'),
            $this->translator->trans('progression', [], 'platform'),
            $this->translator->trans('status', [], 'platform'),
            $this->translator->trans('score', [], 'platform'),
            $this->translator->trans('total', [], 'platform'),
            $this->translator->trans('estimated_duration', [], 'platform'),
            $this->translator->trans('certified', [], 'evaluation'),
        ], $results);
    }

    public function exportWorkspaceEvaluations(Workspace $workspace): mixed
    {
        $results = $this->finderFactory->create(WorkspaceEvaluationType::class)
            ->submit(new FinderQuery(null, ['workspace' => $workspace->getUuid()]))
            ->getResult(function (WorkspaceEvaluation $evaluation): array {
                return array_merge($this->formatEvaluation($evaluation), [
                    $evaluation->isCertified(),
                ]);
            })
            ->getItems();

        return $this->outputCsv([
            $this->translator->trans('last_name', [], 'platform'),
            $this->translator->trans('first_name', [], 'platform'),
            $this->translator->trans('start_date', [], 'platform'),
            $this->translator->trans('end_date', [], 'platform'),
            $this->translator->trans('last_activity', [], 'platform'),
            $this->translator->trans('progression', [], 'platform'),
            $this->translator->trans('status', [], 'platform'),
            $this->translator->trans('score', [], 'platform'),
            $this->translator->trans('total', [], 'platform'),
            $this->translator->trans('certified', [], 'evaluation'),
        ], $results);
    }

    private function formatEvaluation(AbstractUserEvaluation $evaluation): array
    {
        $user = $evaluation->getUser();

        $progression = $evaluation->getProgression();
        if ($progression) {
            $progression = round($progression, EvaluationOptions::PROGRESSION_PRECISION);
        }

        $score = $evaluation->getScore();
        $total = $evaluation->getScoreMax();
        if ($score || 0 === $score) {
            $score = round($score, EvaluationOptions::SCORE_PRECISION);
        }

        return [
            $user && !$evaluation->isAnonymized() ? $user->getLastName() : '',
            $user && !$evaluation->isAnonymized() ? $user->getFirstName() : '',
            DateNormalizer::normalize($evaluation->getStartedAt()),
            DateNormalizer::normalize($evaluation->getEndedAt()),
            DateNormalizer::normalize($evaluation->getLastActivityAt()),
            $progression,
            $this->translator->trans('evaluation_'.($evaluation->getStatus() ?? EvaluationStatus::UNKNOWN).'_short', [], 'evaluation'),
            $score,
            $total,
        ];
    }

    private function outputCsv(array $headers, ?iterable $evaluations = []): mixed
    {
        $handle = fopen('php://output', 'w+');
        fputcsv($handle, $headers, ';');

        foreach ($evaluations as $evaluationData) {
            fputcsv($handle, $evaluationData, ';');
        }

        fclose($handle);

        return $handle;
    }
}
