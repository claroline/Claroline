import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {DashboardLog} from '#/main/log/administration/logs/components/log'
import {DashboardMessage} from '#/main/log/administration/logs/components/message'
import {DashboardFunctional} from '#/main/log/administration/logs/components/functional'

const DashboardTool = (props) =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/security',
        component: DashboardLog
      }, {
        path: '/message',
        component: DashboardMessage
      }, {
        path: '/functional',
        component: DashboardFunctional
      }
    ]}
  />

DashboardTool.propTypes = {
  path: T.string.isRequired
}

export {
  DashboardTool
}
