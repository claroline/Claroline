import {LINK_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/core/translation'

import {constants} from '#/plugin/cursus/administration/cursus/constants'
import {SessionEventCard} from '#/plugin/cursus/administration/cursus/session-event/data/components/session-event-card'

const SessionEventList = {
  open: (row) => ({
    type: LINK_BUTTON,
    target: `/events/form/${row.id}`,
    label: trans('edit', {}, 'actions')
  }),
  definition: [
    {
      name: 'name',
      type: 'string',
      label: trans('name'),
      displayed: true,
      primary: true
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
      name: 'restrictions.maxUsers',
      alias: 'maxUsers',
      type: 'number',
      label: trans('maxUsers'),
      displayed: true
    }, {
      name: 'registration.registrationType',
      alias: 'registrationType',
      type: 'choice',
      label: trans('session_event_registration', {}, 'cursus'),
      displayed: true,
      options: {
        choices: constants.REGISTRATION_TYPES
      }
    }
  ],
  card: SessionEventCard
}

export {
  SessionEventList
}
