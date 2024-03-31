import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Tool} from '#/main/core/tool'

import {AgendaEvent} from '#/plugin/agenda/tools/agenda/containers/event'
import {AgendaCalendar} from '#/plugin/agenda/tools/agenda/containers/calendar'

const AgendaTool = (props) =>
  <Tool
    {...props}
    styles={['claroline-distribution-plugin-agenda-agenda']}
    pages={[
      {
        path: '/event/:id',
        onEnter: (params = {}) => props.loadEvent(params.id),
        component: AgendaEvent
      }, {
        path: '/',
        component: AgendaCalendar
      }
    ]}
  />

AgendaTool.propTypes = {
  loadEvent: T.func.isRequired
}

export {
  AgendaTool
}
