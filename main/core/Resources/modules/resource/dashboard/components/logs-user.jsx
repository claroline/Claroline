import React from 'react'
import {PropTypes as T} from 'prop-types'

import {UserLogList} from '#/main/core/layout/logs'

import {selectors as dashboardSelectors} from '#/main/core/resource/dashboard/store'

const UserLogs = props =>
  <UserLogList
    name={dashboardSelectors.STORE_NAME + '.userActions'}
    listUrl={['apiv2_resource_logs_list_users', {resourceId: props.resourceId}]}
  />

UserLogs.propTypes = {
  resourceId: T.number.isRequired
}

export {
  UserLogs
}