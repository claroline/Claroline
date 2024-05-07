<?php

namespace Claroline\CursusBundle\Transfer\Exporter\Registration;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Claroline\TransferBundle\Transfer\Exporter\AbstractListExporter;

class SessionUserListExporter extends AbstractListExporter
{
    private ObjectManager $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public static function getAction(): array
    {
        return ['training', 'session_user_list'];
    }

    protected static function getClass(): string
    {
        return SessionUser::class;
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        $schema = [
            'properties' => [
                [
                    'name' => 'id',
                    'type' => 'string',
                    'description' => $this->translator->trans('The registration id', [], 'schema'),
                ], [
                    'name' => 'date',
                    'type' => 'datetime',
                    'description' => $this->translator->trans('The registration date', [], 'schema'),
                ], [
                    'name' => 'confirmed',
                    'type' => 'boolean',
                    'description' => $this->translator->trans('The registration has been confirmed by the user', [], 'schema'),
                ], [
                    'name' => 'validated',
                    'type' => 'boolean',
                    'description' => $this->translator->trans('The registration has been validated by a manager', [], 'schema'),
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

                // User info
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
                ], [
                    'name' => 'user.administrativeCode',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user administrativeCode', [], 'schema'),
                ],
            ],
        ];

        /** @var Course $course */
        $course = null;
        if (!empty($extra) && !empty($extra['course'])) {
            $course = $this->om->getRepository(Course::class)->findOneBy(['uuid' => $extra['course']['id']]);
        }

        if ($course) {
            foreach ($course->getPanelFacets() as $panelFacet) {
                foreach ($panelFacet->getFieldsFacet() as $field) {
                    $schema['properties'][] = [
                        'name' => "data.{$field->getUuid()}",
                        'type' => $field->getType(),
                        'label' => $field->getLabel(),
                        'description' => $field->getLabel(),
                    ];
                }
            }
        }

        return $schema;
    }

    protected function getAvailableFilters(): array
    {
        return [
            [
                'name' => 'session',
                'label' => $this->translator->trans('session', [], 'cursus'),
                'type' => 'training_session',
            ], [
                'name' => 'user',
                'label' => $this->translator->trans('user', [], 'community'),
                'type' => 'user',
            ], [
                'name' => 'validated',
                'label' => $this->translator->trans('validated', [], 'platform'),
                'type' => 'boolean',
            ], [
                'name' => 'confirmed',
                'label' => $this->translator->trans('confirmed', [], 'platform'),
                'type' => 'boolean',
            ],
        ];
    }

    public function getExtraDefinition(?array $options = [], ?array $extra = []): array
    {
        $extraDef = parent::getExtraDefinition($options, $extra);
        $extraDef['fields'][] = [
            'name' => 'course',
            'label' => $this->translator->trans('course', [], 'cursus'),
            'type' => 'training_course',
            'required' => true,
        ];

        return $extraDef;
    }

    protected function getHiddenFilters(?array $options = [], ?array $extra = []): array
    {
        $hiddenFilters = parent::getHiddenFilters($options, $extra);

        if (!empty($extra['course'])) {
            $hiddenFilters['course'] = $extra['course']['id'];
        }

        return $hiddenFilters;
    }
}
