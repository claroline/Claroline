import React from 'react'
import {connect} from 'react-redux'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {UserLogs as UserLogsComponent} from '#/main/core/resource/dashboard/components/logs-user'

const UserLogs = connect(
  state => ({
    resourceId: resourceSelectors.resourceNode(state).autoId
  })
)(UserLogsComponent)

export {
  UserLogs
}