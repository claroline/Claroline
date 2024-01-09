<?php

namespace Claroline\EvaluationBundle\Transfer\Exporter;

use Claroline\AppBundle\API\Options;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\TransferBundle\Transfer\Exporter\AbstractListExporter;

abstract class AbstractEvaluationListExporter extends AbstractListExporter
{
    public function supports(string $format, ?array $options = [], ?array $extra = []): bool
    {
        return in_array($format, ['json', 'csv']);
    }

    public function getExtraDefinition(?array $options = [], ?array $extra = []): array
    {
        $extraDef = parent::getExtraDefinition($options, $extra);

        if (!in_array(Options::WORKSPACE_IMPORT, $options)) {
            $extraDef['fields'][] = [
                'name' => 'workspace',
                'label' => $this->translator->trans('workspace', [], 'platform'),
                'type' => 'workspace',
            ];
        }

        return $extraDef;
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        return [
            'properties' => [
                [
                    'name' => 'id',
                    'type' => 'string',
                    'description' => $this->translator->trans('The evaluation id', [], 'schema'),
                ], [
                    'name' => 'date',
                    'type' => 'date',
                    'description' => $this->translator->trans('The evaluation date', [], 'schema'),
                ], [
                    'name' => 'status',
                    'type' => 'string',
                    'description' => $this->translator->trans('The evaluation status', [], 'schema'),
                ], [
                    'name' => 'duration',
                    'type' => 'number',
                    'description' => $this->translator->trans('The evaluation duration', [], 'schema'),
                ], [
                    'name' => 'progression',
                    'type' => 'number',
                    'description' => $this->translator->trans('The evaluation progression', [], 'schema'),
                ],

                // recipient
                [
                    'name' => 'user.id',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user id', [], 'schema'),
                ], [
                    'name' => 'user.email',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user email address', [], 'schema'),
                ], [
                    'name' => 'user.username',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user username', [], 'schema'),
                ], [
                    'name' => 'user.firstName',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user first name', [], 'schema'),
                ], [
                    'name' => 'user.lastName',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user last name', [], 'schema'),
                ],
            ],
        ];
    }

    protected function getAvailableFilters(): array
    {
        return [
            [
                'name' => 'user',
                'label' => $this->translator->trans('user', [], 'platform'),
                'type' => 'user',
            ], [
                'name' => 'status',
                'label' => $this->translator->trans('status', [], 'platform'),
                'type' => 'choice',
                'options' => [
                    'choices' => [
                        AbstractEvaluation::STATUS_NOT_ATTEMPTED => $this->translator->trans('evaluation_not_attempted_short', [], 'evaluation'),
                        AbstractEvaluation::STATUS_TODO => $this->translator->trans('evaluation_todo_short', [], 'evaluation'),
                        AbstractEvaluation::STATUS_UNKNOWN => $this->translator->trans('evaluation_unknown_short', [], 'evaluation'),
                        AbstractEvaluation::STATUS_OPENED => $this->translator->trans('evaluation_opened_short', [], 'evaluation'),
                        AbstractEvaluation::STATUS_INCOMPLETE => $this->translator->trans('evaluation_incomplete_short', [], 'evaluation'),
                        AbstractEvaluation::STATUS_PARTICIPATED => $this->translator->trans('evaluation_participated_short', [], 'evaluation'),
                        AbstractEvaluation::STATUS_FAILED => $this->translator->trans('evaluation_failed_short', [], 'evaluation'),
                        AbstractEvaluation::STATUS_COMPLETED => $this->translator->trans('evaluation_completed_short', [], 'evaluation'),
                        AbstractEvaluation::STATUS_PASSED => $this->translator->trans('evaluation_passed_short', [], 'evaluation'),
                    ],
                ],
            ],
        ];
    }

    protected function getAvailableSortBy(): array
    {
        return [
            [
                'name' => 'user.lastName',
                'label' => $this->translator->trans('last_name', [], 'platform'),
            ], [
                'name' => 'user.firstName',
                'label' => $this->translator->trans('first_name', [], 'platform'),
            ], [
                'name' => 'date',
                'label' => $this->translator->trans('last_modification', [], 'platform'),
            ],
        ];
    }
}
