<?php

namespace Claroline\EvaluationBundle\Transfer\Exporter;

use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;

class ResourceEvaluationListExporter extends AbstractEvaluationListExporter
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

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        $schema = parent::getSchema($options, $extra);
        $schema['properties'] = array_merge($schema['properties'], [
            [
                'name' => 'nbAttempts',
                'type' => 'number',
                'description' => $this->translator->trans('The number of attempts for the evaluation', [], 'schema'),
            ], [
                'name' => 'nbOpenings',
                'type' => 'number',
                'description' => $this->translator->trans('The number of openings of the resource', [], 'schema'),
            ], [
                'name' => 'required',
                'type' => 'boolean',
                'description' => $this->translator->trans('The evaluation is required', [], 'schema'),
            ], [
                'name' => 'resourceNode.id',
                'type' => 'string',
                'description' => $this->translator->trans('The resource id or uuid', [], 'schema'),
            ], [
                'name' => 'resourceNode.name',
                'type' => 'string',
                'description' => $this->translator->trans('The resource name', [], 'schema'),
            ], [
                'name' => 'resourceNode.slug',
                'type' => 'string',
                'description' => $this->translator->trans('The resource slug', [], 'schema'),
            ],
        ]);

        return $schema;
    }

    protected function getAvailableFilters(): array
    {
        return array_merge(parent::getAvailableFilters(), [
            [
                'name' => 'resourceNode',
                'label' => $this->translator->trans('resource', [], 'platform'),
                'type' => 'resource',
            ],
        ]);
    }
}
