<?php

namespace Claroline\OpenBadgeBundle\Transfer\Exporter;

use Claroline\AppBundle\API\Options;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Claroline\TransferBundle\Transfer\Exporter\AbstractListExporter;

class AssertionListExporter extends AbstractListExporter
{
    public static function getAction(): array
    {
        return ['badge', 'list_assertions'];
    }

    public function supports(string $format, ?array $options = [], ?array $extra = []): bool
    {
        return in_array($format, ['json', 'csv']);
    }

    protected static function getClass(): string
    {
        return Assertion::class;
    }

    protected function getHiddenFilters(): array
    {
        return [
            // do not filter disabled and deleted users for now
            'userDisabled' => 'all',
        ];
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
                    'description' => $this->translator->trans('The assertion id', [], 'schema'),
                ], [
                    'name' => 'issuedOn',
                    'type' => 'date',
                    'description' => $this->translator->trans('The assertion date', [], 'schema'),
                ],
                // badge
                [
                    'name' => 'badge.id',
                    'type' => 'string',
                    'description' => $this->translator->trans('The badge id', [], 'schema'),
                ], [
                    'name' => 'badge.name',
                    'type' => 'string',
                    'description' => $this->translator->trans('The badge name', [], 'schema'),
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
                'name' => 'badge',
                'label' => $this->translator->trans('badge', [], 'badge'),
                'type' => 'badge',
            ], [
                'name' => 'user',
                'label' => $this->translator->trans('user', [], 'platform'),
                'type' => 'user',
            ],
        ];
    }

    protected function getAvailableSortBy(): array
    {
        return [
            [
                'name' => 'issuedOn',
                'label' => $this->translator->trans('granted_date', [], 'badge'),
            ],
        ];
    }
}
