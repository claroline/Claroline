import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ToolPage} from '#/main/core/tool/containers/page'
import {Routes} from '#/main/app/router'

import {DashboardLog} from '#/main/log/administration/logs/components/log'
import {DashboardMessage} from '#/main/log/administration/logs/components/message'
import {DashboardFunctional} from '#/main/log/administration/logs/components/functional'

const LogsTool = (props) =>
  <ToolPage>
    <Routes
      path={props.path}
      redirect={[
        {
          from: '/',
          exact: true,
          to : '/security'
        }
      ]}
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
  </ToolPage>

LogsTool.propTypes = {
  path: T.string.isRequired
}

export {
  LogsTool
}
