import React from 'react'
import {UserLogList} from '#/main/core/layout/logs'

const List = () =>
  <UserLogList
    listUrl={['apiv2_admin_tool_logs_list_users']}
  />

export {
  List as UserLogs
}