import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {selectors as toolSelectors} from  '#/main/core/tool/store'
import {selectors} from '#/main/core/tools/dashboard/store'
import {UserLogList} from '#/main/core/layout/logs'

const UserLogsComponent = props =>
  <UserLogList
    listUrl={['apiv2_workspace_tool_logs_list_users', {workspaceId: props.workspaceId}]}
    name={selectors.STORE_NAME + '.userActions'}
  />

UserLogsComponent.propTypes = {
  workspaceId: T.number.isRequired
}

const UserLogs = connect(
  state => ({
    workspaceId: toolSelectors.contextData(state).id
  })
)(UserLogsComponent)

export {
  UserLogs
}