import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {UserLogList} from '#/main/core/layout/logs'

const List = props =>
  <UserLogList
    listUrl={['apiv2_resource_logs_list_users', {resourceId: props.resourceId}]}
  />

List.propTypes = {
  resourceId: T.number.isRequired
}

const ListContainer = connect(
  state => ({
    resourceId: state.resourceId
  }),
  null
)(List)

export {
  ListContainer as UserLogs
}