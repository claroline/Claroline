import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool'
import {PageListSection} from '#/main/app/page'

import {LogFunctionalList} from '#/main/log/components/functional-list'
import {selectors} from '#/main/log/administration/logs/store/selectors'

const LogsFunctional = () =>
  <ToolPage title={trans('functional', {}, 'log')}>
    <PageListSection
      title={trans('functional', {}, 'log')}
    >
      <LogFunctionalList
        className="mb-5"
        flush={true}
        name={selectors.FUNCTIONAL_NAME}
        url={['apiv2_logs_functional']}
      />
    </PageListSection>
  </ToolPage>

export {
  LogsFunctional
}
