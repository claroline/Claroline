import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {LogFunctionalList} from '#/main/log/components/functional-list'

import {selectors} from '#/main/log/resource/logs/store'
import {ResourcePage} from '#/main/core/resource/components/page'
import {PageListSection} from '#/main/app/page'

const LogsMain = (props) =>
  <ResourcePage
    title={trans('activity')}
  >
    <PageListSection>
      <LogFunctionalList
        flush={true}
        name={selectors.STORE_NAME}
        url={['apiv2_resource_functional_logs', {id: props.resourceId}]}
      />
    </PageListSection>
  </ResourcePage>

LogsMain.propTypes = {
  resourceId: T.string.isRequired
}

export {
  LogsMain
}
