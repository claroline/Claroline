import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ToolPage} from '#/main/core/tool/containers/page'
import {ListData} from '#/main/app/content/list/containers/data'
import {trans} from '#/main/app/intl/translation'

import {selectors} from '#/plugin/analytics/administration/dashboard/store/selectors'

const DashboardLog = () =>
  <ToolPage
    subtitle={trans('logs')}
  >
    <ListData
      name={selectors.LIST_NAME}
      fetch={{
        url: ['apiv2_logs_security'],
        autoload: true
      }}
      definition={[
        {
          name: 'date',
          label: trans('date'),
          type: 'date',
          options: {time: true},
          displayed: true
        }, {
          name: 'details',
          type: 'string',
          label: trans('details'),
          displayed: true
        }, {
          name: 'doer.name',
          type: 'string',
          label: trans('doer'),
          displayed: true
        }, {
          name: 'target.name',
          type: 'string',
          label: trans('target'),
          displayed: true
        }, {
          name: 'event',
          type: 'string',
          label: trans('event'),
          displayed: true
        }
      ]}
    />
  </ToolPage>

DashboardLog.propTypes = {
  count: T.shape({
    workspaces: T.number,
    resources: T.number,
    storage: T.number,
    connections: T.shape({
      count: T.number,
      avgTime: T.number
    }),
    users: T.number,
    roles: T.number,
    groups: T.number,
    organizations: T.number
  }).isRequired
}

export {
  DashboardLog
}
