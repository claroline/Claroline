import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {AgendaEvent} from '#/plugin/agenda/tools/agenda/containers/event'
import {AgendaCalendar} from '#/plugin/agenda/tools/agenda/containers/calendar'
import {Tool} from '#/main/core/tool'

const AgendaTool = (props) =>
  <Tool
    {...props}
    styles={['claroline-distribution-plugin-agenda-agenda']}
  >
    <Routes
      path={props.path}
      routes={[
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
  </Tool>

AgendaTool.propTypes = {
  path: T.string.isRequired,
  loadEvent: T.func.isRequired
}

export {
  AgendaTool
}
