<?php

namespace Claroline\EvaluationBundle\Transfer\Exporter;

use Claroline\CoreBundle\Entity\Workspace\Evaluation;

class WorkspaceEvaluationListExporter extends AbstractEvaluationListExporter
{
    public static function getAction(): array
    {
        return ['evaluation', 'workspace_evaluation_list'];
    }

    protected static function getClass(): string
    {
        return Evaluation::class;
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        $schema = parent::getSchema($options, $extra);
        $schema['properties'] = array_merge($schema['properties'], [
            [
                'name' => 'workspace.id',
                'type' => 'string',
                'description' => $this->translator->trans('The workspace id or uuid', [], 'schema'),
            ], [
                'name' => 'workspace.name',
                'type' => 'string',
                'description' => $this->translator->trans('The workspace name', [], 'schema'),
            ], [
                'name' => 'workspace.code',
                'type' => 'string',
                'description' => $this->translator->trans('The workspace code', [], 'schema'),
            ], [
                'name' => 'workspace.slug',
                'type' => 'string',
                'description' => $this->translator->trans('The workspace slug', [], 'schema'),
            ],
        ]);

        return $schema;
    }
}
