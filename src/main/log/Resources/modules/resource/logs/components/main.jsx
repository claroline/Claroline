import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {ContentTitle} from '#/main/app/content/components/title'
import {LogFunctionalList} from '#/main/log/components/functional-list'

import {selectors} from '#/main/log/resource/logs/store'
import {ResourcePage} from '#/main/core/resource/components/page'

const LogsMain = (props) =>
  <ResourcePage
    title={trans('activity')}
  >
    {/*<ContentTitle className="mt-3" title={trans('activity')} />*/}

    <LogFunctionalList
      name={selectors.STORE_NAME}
      url={['apiv2_resource_functional_logs', {id: props.resourceId}]}
    />
  </ResourcePage>

LogsMain.propTypes = {
  resourceId: T.string.isRequired
}

export {
  LogsMain
}
