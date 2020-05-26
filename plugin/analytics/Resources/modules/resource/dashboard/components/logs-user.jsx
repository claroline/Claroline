import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {DOWNLOAD_BUTTON} from '#/main/app/buttons'
import {ContentTitle} from '#/main/app/content/components/title'
import {UserLogList} from '#/main/core/layout/logs'

import {selectors as dashboardSelectors} from '#/plugin/analytics/resource/dashboard/store'

const UserLogs = props =>
  <Fragment>
    <ContentTitle
      title={trans('user_actions')}
      actions={[
        {
          name: 'download-users',
          type: DOWNLOAD_BUTTON,
          icon: 'fa fa-fw fa-download',
          label: trans('export-user-actions', {}, 'actions'),
          file: {
            url: url(['apiv2_resource_logs_list_users_csv', {resourceId: props.resourceId}])
          },
          group: trans('export')
        }
      ]}
    />

    <UserLogList
      name={dashboardSelectors.STORE_NAME + '.userActions'}
      listUrl={['apiv2_resource_logs_list_users', {resourceId: props.resourceId}]}
    />
  </Fragment>

UserLogs.propTypes = {
  resourceId: T.number.isRequired
}

export {
  UserLogs
}
