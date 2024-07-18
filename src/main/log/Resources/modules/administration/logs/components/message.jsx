import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool'

import {selectors} from '#/main/log/administration/logs/store/selectors'
import {LogMessageList} from '#/main/log/components/message-list'
import {PageListSection} from '#/main/app/page/components/list-section'

const LogsMessage = () =>
  <ToolPage title={trans('message', {}, 'log')}>
    <PageListSection>
      <LogMessageList
        flush={true}
        name={selectors.MESSAGE_NAME}
        url={['apiv2_logs_message']}
      />
    </PageListSection>
  </ToolPage>

export {
  LogsMessage
}
