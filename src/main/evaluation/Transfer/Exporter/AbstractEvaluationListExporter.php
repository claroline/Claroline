<?php

namespace Claroline\EvaluationBundle\Transfer\Exporter;

use Claroline\AppBundle\API\Options;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
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
                    'name' => 'lastActivityAt',
                    'type' => 'date',
                    'description' => $this->translator->trans('The user last activity for the evaluation', [], 'schema'),
                ], [
                    'name' => 'startedAt',
                    'type' => 'date',
                    'description' => $this->translator->trans('The date on which the user began the evaluation', [], 'schema'),
                ], [
                    'name' => 'endedAt',
                    'type' => 'date',
                    'description' => $this->translator->trans('The date on which the user completed the evaluation', [], 'schema'),
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
                        EvaluationStatus::NOT_ATTEMPTED => $this->translator->trans('evaluation_not_attempted_short', [], 'evaluation'),
                        EvaluationStatus::UNKNOWN => $this->translator->trans('evaluation_unknown_short', [], 'evaluation'),
                        EvaluationStatus::INCOMPLETE => $this->translator->trans('evaluation_incomplete_short', [], 'evaluation'),
                        EvaluationStatus::FAILED => $this->translator->trans('evaluation_failed_short', [], 'evaluation'),
                        EvaluationStatus::COMPLETED => $this->translator->trans('evaluation_completed_short', [], 'evaluation'),
                        EvaluationStatus::PASSED => $this->translator->trans('evaluation_passed_short', [], 'evaluation'),
                    ],
                ],
            ], [
                'name' => 'userDisabled',
                'label' => $this->translator->trans('user_disabled', [], 'community'),
                'type' => 'boolean',
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
