import {LINK_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/core/translation'

import {CourseCard} from '#/plugin/cursus/administration/cursus/course/data/components/course-card'

const CourseList = {
  open: (row) => ({
    type: LINK_BUTTON,
    target: `/courses/form/${row.id}`,
    label: trans('edit', {}, 'actions')
  }),
  definition: [
    {
      name: 'title',
      type: 'string',
      label: trans('title'),
      displayed: true,
      primary: true
    }, {
      name: 'code',
      type: 'string',
      label: trans('code'),
      displayed: true
    }, {
      name: 'restrictions.maxUsers',
      alias: 'maxUsers',
      type: 'number',
      label: trans('maxUsers'),
      displayed: true
    }, {
      name: 'registration.publicRegistration',
      alias: 'publicRegistration',
      type: 'boolean',
      label: trans('public_registration'),
      displayed: true
    }, {
      name: 'registration.publicUnregistration',
      alias: 'publicUnregistration',
      type: 'boolean',
      label: trans('public_unregistration'),
      displayed: true
    }, {
      name: 'registration.registrationValidation',
      alias: 'registrationValidation',
      type: 'boolean',
      label: trans('registration_validation', {}, 'cursus'),
      displayed: true
    }, {
      name: 'registration.userValidation',
      alias: 'userValidation',
      type: 'boolean',
      label: trans('user_validation', {}, 'cursus'),
      displayed: true
    }, {
      name: 'registration.organizationValidation',
      alias: 'organizationValidation',
      type: 'boolean',
      label: trans('organization_validation', {}, 'cursus'),
      displayed: true
    }
  ],
  card: CourseCard
}

export {
  CourseList
}
