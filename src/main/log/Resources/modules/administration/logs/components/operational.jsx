import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool/containers/page'
import {ContentSizing} from '#/main/app/content/components/sizing'

import {selectors} from '#/main/log/administration/logs/store/selectors'
import {LogOperationalList} from '#/main/log/components/operational-list'

const LogsOperational = () =>
  <ToolPage subtitle={trans('operational', {}, 'log')}>
    <ContentSizing size="full">
      <LogOperationalList
        flush={true}
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
    </ContentSizing>
  </ToolPage>

export {
  LogsOperational
}
