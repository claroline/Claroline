import React from 'react'

import {UserLogList} from '#/main/core/layout/logs'

const UserLogs = () =>
  <UserLogList
    listUrl={['apiv2_admin_tool_logs_list_users']}
  />

export {
  UserLogs
}