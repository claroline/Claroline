import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool/containers/page'

import {FunctionalList} from '#/main/log/functional/components/list'
import {selectors} from '#/main/log/administration/logs/store/selectors'
import {ContentSizing} from '#/main/app/content/components/sizing'

const LogsFunctional = () =>
  <ToolPage subtitle={trans('functional', {}, 'log')}>
    <ContentSizing size="full">
      <FunctionalList
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
    </ContentSizing>
  </ToolPage>

export {
  LogsFunctional
}
