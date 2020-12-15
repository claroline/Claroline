import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {DOWNLOAD_BUTTON} from '#/main/app/buttons'
import {ContentTitle} from '#/main/app/content/components/title'
import {LogList} from '#/main/core/layout/logs'

import {selectors as dashboardSelectors} from '#/plugin/analytics/resource/dashboard/store'

const Logs = (props) =>
  <Fragment>
    <ContentTitle
      title={trans('users_actions')}
      actions={[
        {
          name: 'download',
          type: DOWNLOAD_BUTTON,
          icon: 'fa fa-fw fa-download',
          label: trans('export-actions', {}, 'actions'),
          file: {
            url: url(['apiv2_resource_logs_list_csv', {resourceId: props.resourceId}])
          },
          group: trans('export')
        }
      ]}
    />

    <LogList
      id={props.resourceId}
      name={dashboardSelectors.STORE_NAME + '.logs'}
      listUrl={['apiv2_resource_logs_list', {resourceId: props.resourceId}]}
      chart={props.chart}
      actions={props.actions}
      getChartData={props.getChartData}
      queryString={props.queryString}
    />
  </Fragment>

Logs.propTypes = {
  resourceId: T.number.isRequired,
  chart: T.object.isRequired,
  actions: T.array,
  getChartData: T.func.isRequired,
  queryString: T.string
}

export {
  Logs
}