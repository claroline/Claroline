import React from 'react'
import {connect} from 'react-redux'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {UserLogs as UserLogsComponent} from '#/plugin/path/resources/path/dashboard/components/logs-user'

const UserLogs = connect(
  state => ({
    resourceId: resourceSelectors.resourceNode(state).autoId
  })
)(UserLogsComponent)

export {
  UserLogs
}