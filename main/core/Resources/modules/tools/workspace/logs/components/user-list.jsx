import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {UserLogList} from '#/main/core/layout/logs'

const List = props =>
  <UserLogList
    listUrl={['apiv2_workspace_tool_logs_list_users', {workspaceId: props.workspaceId}]}
  />

List.propTypes = {
  workspaceId: T.number.isRequired
}

const ListContainer = connect(
  state => ({
    workspaceId: state.workspaceId
  }),
  null
)(List)

export {
  ListContainer as UserLogs
}