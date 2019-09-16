import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {MessageTab} from '#/plugin/planned-notification/tools/planned-notification/message/components/message-tab'
import {NotificationTab} from '#/plugin/planned-notification/tools/planned-notification/notification/components/notification-tab'

const PlannedNotificationTool = props =>
  <Routes
    path={props.path}
    redirect={[
      {from: '/', exact: true, to: '/notifications'}
    ]}
    routes={[
      {
        path: '/notifications',
        component: NotificationTab
      }, {
        path: '/messages',
        component: MessageTab
      }
    ]}
  />

PlannedNotificationTool.propTypes = {
  path: T.string.isRequired
}

export {
  PlannedNotificationTool
}
