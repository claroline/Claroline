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
          name: 'name',
          type: 'string',
          label: trans('name'),
          displayed: true,
          filterable: false,
          sortable: false,
          calculated: (rowData) => trans(rowData.name, {}, 'template'),
          options: {
            domain: 'template'
          },
          primary: true
        }, {
          name: 'description',
          type: 'string',
          label: trans('description'),
          displayed: true,
          filterable: false,
          sortable: false,
          calculated: (rowData) => trans(`${rowData.name}_desc`, {}, 'template')
        }, {
          name: 'typeName',
          type: 'string',
          label: trans('type'),
          displayable: false,
          filterable: false,
          sortable: false
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
