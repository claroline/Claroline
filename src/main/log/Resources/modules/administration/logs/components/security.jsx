import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool'

import {selectors} from '#/main/log/administration/logs/store/selectors'
import {LogSecurityList} from '#/main/log/components/security-list'
import {PageListSection} from '#/main/app/page/components/list-section'

const LogsSecurity = () =>
  <ToolPage title={trans('security', {}, 'log')}>
    <PageListSection>
      <LogSecurityList
        name={selectors.LIST_NAME}
        url={['apiv2_logs_security']}
        selectable={false}
      />
    </PageListSection>
  </ToolPage>

export {
  LogsSecurity
}
