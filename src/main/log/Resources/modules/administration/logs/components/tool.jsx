import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {LogsSecurity} from '#/main/log/administration/logs/components/security'
import {LogsMessage} from '#/main/log/administration/logs/components/message'
import {LogsFunctional} from '#/main/log/administration/logs/components/functional'
import {LogsOperational} from '#/main/log/administration/logs/components/operational'
import {LogsTypes} from '#/main/log/administration/logs/components/types'
import {Tool} from '#/main/core/tool'

const LogsTool = (props) =>
  <Tool {...props}>
    <Routes
      path={props.path}
      redirect={[
        { from: '/', exact: true, to : '/security' }
      ]}
      routes={[
        {
          path: '/security',
          component: LogsSecurity
        }, {
          path: '/message',
          component: LogsMessage
        }, {
          path: '/functional',
          component: LogsFunctional
        }, {
          path: '/operational',
          component: LogsOperational
        }, {
          path: '/parameters',
          component: LogsTypes
        }
      ]}
    />
  </Tool>

LogsTool.propTypes = {
  path: T.string.isRequired
}

export {
  LogsTool
}
