import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {DOWNLOAD_BUTTON} from '#/main/app/buttons'
import {ContentTitle} from '#/main/app/content/components/title'

import {ActionsChart} from '#/plugin/analytics/charts/actions/containers/chart'
import {ConnectionTimeChart} from '#/plugin/analytics/charts/connection-time/containers/chart'

const DashboardActivity = (props) =>
  <Fragment>
    <ContentTitle
      title={trans('activity')}
      actions={[
        {
          name: 'download-connection-times',
          type: DOWNLOAD_BUTTON,
          icon: 'fa fa-fw fa-download',
          label: trans('export-connections', {}, 'actions'),
          file: {
            url: url(['apiv2_log_connect_resource_list_csv', {resource: props.resourceId}])
          },
          group: trans('export')
        }, {
          name: 'download',
          type: DOWNLOAD_BUTTON,
          icon: 'fa fa-fw fa-download',
          label: trans('export-actions', {}, 'actions'),
          file: {
            url: url(['apiv2_resource_logs_list_csv', {resourceId: props.resourceId}])
          },
          group: trans('export')
        }, {
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

    <div className="row">
      <div className="col-md-6">
        <ActionsChart
          url={['apiv2_resource_analytics_actions', {resource: props.resourceId}]}
          listUrl={['apiv2_resource_logs_list', {resourceId: props.resourceId}]}
        />
      </div>

      <div className="col-md-6">
        <ConnectionTimeChart
          url={['apiv2_resource_analytics_time', {resource: props.resourceId}]}
          listUrl={['apiv2_log_connect_resource_list', {resource: props.resourceId}]}
        />
      </div>
    </div>
  </Fragment>

DashboardActivity.propTypes = {
  resourceId: T.number.isRequired
}

export {
  DashboardActivity
}