import React from 'react'

import {ToolPage} from '#/main/core/tool/containers/page'
import {ListData} from '#/main/app/content/list/containers/data'
import {trans} from '#/main/app/intl/translation'

import {selectors} from '#/plugin/analytics/administration/dashboard/store/selectors'

const DashboardFunctional = () =>
  <ToolPage
    subtitle={trans('functional')}
  >
    <ListData
      name={selectors.FUNCTIONAL_NAME}
      fetch={{
        url: ['apiv2_logs_functional'],
        autoload: true
      }}
      definition={[
        {
          name: 'user',
          type: 'user',
          label: trans('user'),
          displayed: true
        }, {
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
          name: 'resource',
          type: 'resource',
          label: trans('resource'),
          displayed: true
        }, {
          name: 'event',
          type: 'translation',
          label: trans('event'),
          displayed: false,
          options: {
            domain: 'security'
          }
        }
      ]}
    />
  </ToolPage>

export {
  DashboardFunctional
}
