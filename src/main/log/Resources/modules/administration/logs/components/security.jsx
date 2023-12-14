import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool/containers/page'
import {ContentSizing} from '#/main/app/content/components/sizing'

import {selectors} from '#/main/log/administration/logs/store/selectors'
import {LogSecurityList} from '#/main/log/components/security-list'

const LogsSecurity = () =>
  <ToolPage subtitle={trans('security', {}, 'log')}>
    <ContentSizing size="full">
      <LogSecurityList
        flush={true}
        name={selectors.LIST_NAME}
        url={['apiv2_logs_security']}
        selectable={false}
      />
    </ContentSizing>
  </ToolPage>

export {
  LogsSecurity
}
