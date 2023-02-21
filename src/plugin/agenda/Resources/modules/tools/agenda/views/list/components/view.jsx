import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ListData} from '#/main/app/content/list/containers/data'

import {EventCard} from '#/plugin/agenda/event/components/card'
import {EventIcon} from '#/plugin/agenda/event/components/icon'
import {selectors} from '#/plugin/agenda/tools/agenda/views/list/store'

const AgendaViewList = () =>
  <ListData
    name={selectors.STORE_NAME}
    fetch={{
      url: ['apiv2_planned_object_list'],
      autoload: true
    }}
    definition={[
      {
        name: 'name',
        type: 'string',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'meta.type',
        type: 'type',
        label: trans('type'),
        displayed: true,
        calculated: (event) => ({
          icon: <EventIcon type={event.meta.type} />,
          name: trans(event.meta.type, {}, 'event'),
          description: trans(`${event.meta.type}_desc`, {}, 'event')
        })
      }, {
        name: 'description',
        type: 'html',
        label: trans('description'),
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
        label: trans('after_today'),
        displayed: false,
        filterable: true,
        sortable: false
      }, {
        name: 'workspace',
        type: 'workspace',
        label: trans('workspace'),
        displayed: true,
        filterable: false,
        sortable: false
      }
    ]}
    card={EventCard}
  />

AgendaViewList.propTypes = {
  create: T.func.isRequired,

  invalidate: T.func.isRequired
}

export {
  AgendaViewList
}
