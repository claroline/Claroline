import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

import {route} from '#/plugin/cursus/routing'
import {SessionCard} from '#/plugin/cursus/session/components/card'

export default {
  name: 'sessions',
  icon: 'fa fa-fw fa-calendar-week',
  parameters: {
    primaryAction: (session) => ({
      type: URL_BUTTON,
      target: `#${route(session.course, session)}`
    }),
    definition: [
      {
        name: 'name',
        type: 'string',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'code',
        type: 'string',
        label: trans('code'),
        displayed: false
      }, {
        name: 'workspace',
        type: 'workspace',
        label: trans('workspace'),
        displayed: false
      }, {
        name: 'course',
        type: 'course',
        label: trans('course', {}, 'cursus'),
        displayed: true
      }, {
        name: 'restrictions.dates[0]',
        alias: 'startDate',
        type: 'date',
        label: trans('start_date'),
        displayed: true
      }, {
        name: 'restrictions.dates[1]',
        alias: 'endDate',
        type: 'date',
        label: trans('end_date'),
        displayed: true
      }, {
        name: 'restrictions.users',
        alias: 'maxUsers',
        type: 'number',
        label: trans('max_participants', {}, 'cursus'),
        displayed: true
      }, {
        name: 'registration.selfRegistration',
        alias: 'publicRegistration',
        type: 'boolean',
        label: trans('public_registration'),
        displayed: false
      }, {
        name: 'registration.selfUnregistration',
        alias: 'publicUnregistration',
        type: 'boolean',
        label: trans('public_unregistration'),
        displayed: false
      }, {
        name: 'registration.validation',
        alias: 'registrationValidation',
        type: 'boolean',
        label: trans('registration_validation', {}, 'cursus'),
        displayed: false
      }, {
        name: 'registration.userValidation',
        alias: 'userValidation',
        type: 'boolean',
        label: trans('user_validation', {}, 'cursus'),
        displayed: false
      }, {
        name: 'courseTags',
        type: 'tag',
        label: trans('tags'),
        displayed: false,
        sortable: false,
        options: {
          objectClass: 'Claroline\\CursusBundle\\Entity\\Course'
        }
      }, {
        name: 'organizations',
        type: 'organizations',
        label: trans('organizations'),
        displayable: false,
        displayed: false,
        sortable: false,
        filterable: true
      }
    ],
    card: SessionCard
  }
}
