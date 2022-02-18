<?php

namespace Claroline\EvaluationBundle\Transfer\Exporter;

use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\TransferBundle\Transfer\Exporter\AbstractListExporter;

class ResourceEvaluationListExporter extends AbstractListExporter
{
    public function getAction(): array
    {
        return ['evaluation', 'resource_evaluation_list'];
    }

    public function supports(string $format, ?array $options = [], ?array $extra = []): bool
    {
        return in_array($format, ['json', 'csv']);
    }

    protected static function getClass(): string
    {
        return ResourceUserEvaluation::class;
    }
}
