import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'
import {route as workspaceRoute} from '#/main/core/workspace/routing'
import {route as toolRoute} from '#/main/core/tool/routing'

import {EventCard} from '#/plugin/cursus/event/components/card'

export default {
  name: 'session-events',
  icon: 'fa fa-fw fa-clock-o',
  parameters: {
    primaryAction: (event) => ({
      type: URL_BUTTON,
      target: get(event, 'session.workspace') ?
        '#' + workspaceRoute(get(event, 'session.workspace'), 'training_events') + '/' + event.id :
        '#' + toolRoute('trainings') + '/events/' + event.id
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
        name: 'description',
        type: 'string',
        label: trans('description'),
        displayed: true
      }, {
        name: 'tutors',
        type: 'users',
        label: trans('tutors', {}, 'cursus')
      }, {
        name: 'restrictions.users',
        alias: 'maxUsers',
        type: 'number',
        label: trans('max_participants', {}, 'cursus'),
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
        name: 'session',
        label: trans('session', {}, 'cursus'),
        type: 'training_session',
        displayed: true
      }, {
        name: 'workspace',
        type: 'workspace',
        label: trans('workspace'),
        displayable: false,
        sortable: false
      }
    ],
    card: EventCard
  }
}
