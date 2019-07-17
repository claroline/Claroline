import React from 'react'

import {selectors} from '#/main/core/administration/dashboard/store'
import {UserLogList} from '#/main/core/layout/logs'

const UserLogs = () =>
  <UserLogList
    listUrl={['apiv2_admin_tool_logs_list_users']}
    name={selectors.STORE_NAME + '.userActions'}
  />

export {
  UserLogs
}