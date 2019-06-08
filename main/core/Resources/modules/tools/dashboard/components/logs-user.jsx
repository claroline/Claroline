import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {UserLogList} from '#/main/core/layout/logs'

const UserLogsComponent = props =>
  <UserLogList
    listUrl={['apiv2_workspace_tool_logs_list_users', {workspaceId: props.workspaceId}]}
  />

UserLogsComponent.propTypes = {
  workspaceId: T.number.isRequired
}

const UserLogs = connect(
  state => ({
    workspaceId: state.workspace.id
  })
)(UserLogsComponent)

export {
  UserLogs
}