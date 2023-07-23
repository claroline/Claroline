<?php

namespace Claroline\CursusBundle\Transfer\Exporter\Registration;

use Claroline\CursusBundle\Entity\Registration\SessionGroup;
use Claroline\TransferBundle\Transfer\Exporter\AbstractListExporter;

class SessionGroupListExporter extends AbstractListExporter
{
    public static function getAction(): array
    {
        return ['training', 'session_group_list'];
    }

    protected static function getClass(): string
    {
        return SessionGroup::class;
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        return [
            'properties' => [
                [
                    'name' => 'id',
                    'type' => 'string',
                    'description' => $this->translator->trans('The registration id', [], 'schema'),
                ], [
                    'name' => 'date',
                    'type' => 'datetime',
                    'description' => $this->translator->trans('The registration date', [], 'schema'),
                ],

                // Session info
                [
                    'name' => 'session.id',
                    'type' => 'string',
                    'description' => $this->translator->trans('The session id', [], 'schema'),
                ], [
                    'name' => 'session.name',
                    'type' => 'string',
                    'description' => $this->translator->trans('The session name', [], 'schema'),
                ], [
                    'name' => 'session.code',
                    'type' => 'string',
                    'description' => $this->translator->trans('The session code', [], 'schema'),
                ],

                // Group info
                [
                    'name' => 'group.id',
                    'type' => 'string',
                    'description' => $this->translator->trans('The group id', [], 'schema'),
                ], [
                    'name' => 'group.name',
                    'type' => 'string',
                    'description' => $this->translator->trans('The group name', [], 'schema'),
                ], [
                    'name' => 'group.code',
                    'type' => 'string',
                    'description' => $this->translator->trans('The group code', [], 'schema'),
                ],
            ],
        ];
    }

    protected function getAvailableFilters(): array
    {
        return [
            [
                'name' => 'session',
                'label' => $this->translator->trans('session', [], 'cursus'),
                'type' => 'training_session',
            ], [
                'name' => 'group',
                'label' => $this->translator->trans('group', [], 'community'),
                'type' => 'group',
            ],
        ];
    }

    public function getExtraDefinition(?array $options = [], ?array $extra = []): array
    {
        $extraDef = parent::getExtraDefinition($options, $extra);
        $extraDef['fields'][] = [
            'name' => 'course',
            'label' => $this->translator->trans('course', [], 'cursus'),
            'type' => 'course',
        ];

        return $extraDef;
    }

    protected function getHiddenFilters(?array $options = [], ?array $extra = []): array
    {
        $hiddenFilters = parent::getExtraDefinition($options, $extra);

        if (!empty($extra['course'])) {
            $hiddenFilters['course'] = $extra['course']['id'];
        }

        return $hiddenFilters;
    }
}
