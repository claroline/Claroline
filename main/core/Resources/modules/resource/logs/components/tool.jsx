import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {TabbedPageContainer} from '#/main/core/layout/tabs'
import {LogTab, LogTabActions} from '#/main/core/resource/logs/log/components/log-tab'
import {ConnectionTab, ConnectionTabActions} from '#/main/core/resource/logs/connection/components/connection-tab'

const LogTool = () =>
  <TabbedPageContainer
    title={trans('logs', {}, 'tools')}
    redirect={[
      {from: '/', exact: true, to: '/connections'}
    ]}

    tabs={[
      {
        icon: 'fa fa-clock',
        title: trans('connection_time'),
        path: '/connections',
        actions: ConnectionTabActions,
        content: ConnectionTab
      }, {
        icon: 'fa fa-user',
        title: trans('users_tracking'),
        path: '/log',
        actions: LogTabActions,
        content: LogTab
      }
    ]}
  />

export {
  LogTool
}
