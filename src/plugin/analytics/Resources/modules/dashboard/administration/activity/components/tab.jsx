import React from 'react'
import {PropTypes as T} from 'prop-types'

import {schemeCategory20c} from 'd3-scale'

import {trans, displayDuration} from '#/main/app/intl'
import {DOWNLOAD_BUTTON} from '#/main/app/buttons'
import {ContentCounter} from '#/main/app/content/components/counter'
import {ToolPage} from '#/main/core/tool/containers/page'

import {ActionsChart} from '#/plugin/analytics/charts/actions/containers/chart'
import {ConnectionTimeChart} from '#/plugin/analytics/charts/connection-time/containers/chart'

const ActivityTab = (props) =>
  <ToolPage
    subtitle={trans('activity')}
    toolbar="more"
    actions={[
      {
        name: 'download-connection-times',
        type: DOWNLOAD_BUTTON,
        icon: 'fa fa-fw fa-download',
        label: trans('export-connections', {}, 'actions'),
        file: {
          url: ['apiv2_log_connect_platform_list_csv']
        },
        group: trans('transfer')
      }, {
        name: 'download',
        type: DOWNLOAD_BUTTON,
        icon: 'fa fa-fw fa-download',
        label: trans('export-actions', {}, 'actions'),
        file: {
          url: ['apiv2_admin_tool_logs_list_csv']
        },
        group: trans('transfer')
      }, {
        name: 'download-users',
        type: DOWNLOAD_BUTTON,
        icon: 'fa fa-fw fa-download',
        label: trans('export-user-actions', {}, 'actions'),
        file: {
          url: ['apiv2_admin_tool_logs_list_users_csv']
        },
        group: trans('transfer')
      }
    ]}
  >
    <div className="row">
      <ContentCounter
        icon="fa fa-power-off"
        label={trans('connections')}
        color={schemeCategory20c[1]}
        value={props.count.connections.count}
      />

      <ContentCounter
        icon="fa fa-clock"
        label={trans('connection_time')}
        color={schemeCategory20c[5]}
        value={props.count.connections.avgTime ?
          trans('connection_avg_time', {time: displayDuration(props.count.connections.avgTime)}, 'analytics') :
          '-'
        }
      />
    </div>

    <div className="row">
      <div className="col-md-6">
        <ActionsChart
          url={['apiv2_admin_tool_analytics_actions']}
          listUrl={['apiv2_admin_tool_logs_list']}
        />
      </div>

      <div className="col-md-6">
        <ConnectionTimeChart
          url={['apiv2_admin_tool_analytics_time']}
          listUrl={['apiv2_log_connect_platform_list']}
        />
      </div>
    </div>
  </ToolPage>

ActivityTab.propTypes = {
  count: T.shape({
    connections: T.shape({
      count: T.number,
      avgTime: T.number
    })
  }).isRequired
}

export {
  ActivityTab
}
