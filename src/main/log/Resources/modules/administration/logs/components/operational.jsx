import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool'

import {selectors} from '#/main/log/administration/logs/store/selectors'
import {LogOperationalList} from '#/main/log/components/operational-list'
import {PageListSection} from '#/main/app/page/components/list-section'

const LogsOperational = () =>
  <ToolPage title={trans('operational', {}, 'log')}>
    <PageListSection>
      <LogOperationalList
        name={selectors.OPERATIONAL_NAME}
        url={['apiv2_logs_operational']}
        customDefinition={[
          {
            name: 'objectClass',
            type: 'string',
            label: trans('object'),
            displayed: true
          }, {
            name: 'objectId',
            type: 'string',
            label: trans('id'),
            displayed: true
          }
        ]}
      />
    </PageListSection>
  </ToolPage>

export {
  LogsOperational
}
