import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool'

import {LogFunctionalList} from '#/main/log/components/functional-list'
import {selectors} from '#/main/log/administration/logs/store/selectors'
import {PageListSection} from '#/main/app/page/components/list-section'

const LogsFunctional = () =>
  <ToolPage title={trans('functional', {}, 'log')}>
    <PageListSection>
      <LogFunctionalList
        flush={true}
        name={selectors.FUNCTIONAL_NAME}
        url={['apiv2_logs_functional']}
        customDefinition={[
          {
            name: 'workspace',
            type: 'workspace',
            label: trans('workspace'),
            displayed: true
          }, {
            name: 'resource',
            type: 'resource',
            label: trans('resource'),
            displayed: true
          }
        ]}
      />
    </PageListSection>
  </ToolPage>

export {
  LogsFunctional
}
