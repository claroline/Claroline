import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ListData} from '#/main/app/content/list/containers/data'

import {EventCard} from '#/plugin/agenda/data/components/event-card'
import {selectors} from '#/plugin/agenda/tools/agenda/views/list/store'
import {constants} from '#/plugin/agenda/event/constants'

const AgendaViewList = props =>
  <ListData
    name={selectors.STORE_NAME}
    fetch={{
      url: ['apiv2_event_list'],
      autoload: true
    }}
    definition={[
      {
        name: 'title',
        type: 'string',
        label: trans('title'),
        displayed: true,
        primary: true
      }, {
        name: 'meta.type',
        type: 'choice',
        label: trans('type'),
        displayed: true,
        options: {
          choices: constants.EVENT_TYPES
        }
      }, {
        name: 'description',
        type: 'html',
        label: trans('description'),
        displayed: true
      }, {
        name: 'allDay',
        type: 'boolean',
        label: trans('all_day', {}, 'agenda'),
        displayed: true
      }, {
        name: 'start',
        type: 'date',
        label: trans('start_date'),
        displayed: true
      }, {
        name: 'end',
        type: 'date',
        label: trans('end_date'),
        displayed: true
      }, {
        name: 'afterToday',
        type: 'boolean',
        label: trans('after_today', {}, 'agenda'),
        displayed: false,
        filterable: true,
        sortable: false
      }, {
        name: 'meta.done',
        alias: 'isTaskDone',
        type: 'boolean',
        label: trans('task_done', {}, 'agenda'),
        displayed: true
      }, {
        name: 'workspace',
        type: 'workspace',
        label: trans('workspace'),
        displayed: true,
        filterable: false,
        sortable: false
      }
    ]}
    actions={(events) => props.eventActions(events[0]).map(action => Object.assign({}, action, {scope: 'object'}))}
    card={EventCard}
  />

AgendaViewList.propTypes = {
  create: T.func.isRequired,
  eventActions: T.func.isRequired,

  invalidate: T.func.isRequired
}


export {
  AgendaViewList
}
