import {trans} from '#/main/app/intl/translation'

import {constants} from '#/plugin/cursus/administration/cursus/constants'

const SessionEventList = {
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
      name: 'meta.session',
      alias: 'sessionName',
      type: 'string',
      label: trans('session', {}, 'cursus'),
      displayed: true,
      calculated: (sessionEvent) => sessionEvent.meta.session.name
    }, {
      name: 'meta.set',
      alias: 'eventSet',
      type: 'string',
      label: trans('session_event_set', {}, 'cursus'),
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
      name: 'registration.registrationType',
      alias: 'registrationType',
      type: 'choice',
      label: trans('session_event_registration', {}, 'cursus'),
      displayed: false,
      options: {
        choices: constants.REGISTRATION_TYPES
      }
    }
  ]
}

export {
  SessionEventList
}
