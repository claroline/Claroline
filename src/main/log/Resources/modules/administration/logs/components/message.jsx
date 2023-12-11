import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {ListData} from '#/main/app/content/list/containers/data'
import {ToolPage} from '#/main/core/tool/containers/page'

import {selectors} from '#/main/log/administration/logs/store/selectors'
import {ContentSizing} from '#/main/app/content/components/sizing'

const LogsMessage = () =>
  <ToolPage subtitle={trans('message', {}, 'log')}>
    <ContentSizing size="full">
      <ListData
        flush={true}
        name={selectors.MESSAGE_NAME}
        fetch={{
          url: ['apiv2_logs_message'],
          autoload: true
        }}
        definition={[
          {
            name: 'doer',
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
            label: trans('description'),
            displayed: true
          }, {
            name: 'receiver',
            type: 'user',
            label: trans('target'),
            displayed: false
          }, {
            name: 'event',
            type: 'translation',
            label: trans('event'),
            displayed: false,
            options: {
              domain: 'platform'
            }
          }
        ]}
        selectable={false}
      />
    </ContentSizing>
  </ToolPage>

export {
  LogsMessage
}
