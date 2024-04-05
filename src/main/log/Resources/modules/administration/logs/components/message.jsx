import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool'
import {ContentSizing} from '#/main/app/content/components/sizing'

import {selectors} from '#/main/log/administration/logs/store/selectors'
import {LogMessageList} from '#/main/log/components/message-list'

const LogsMessage = () =>
  <ToolPage title={trans('message', {}, 'log')}>
    <ContentSizing size="full">
      <LogMessageList
        flush={true}
        name={selectors.MESSAGE_NAME}
        url={['apiv2_logs_message']}
      />
    </ContentSizing>
  </ToolPage>

export {
  LogsMessage
}
