import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {ContentTitle} from '#/main/app/content/components/title'
import {LogFunctionalList} from '#/main/log/components/functional-list'

import {selectors} from '#/main/log/resource/logs/store'

const LogsMain = (props) =>
  <>
    <ContentTitle className="mt-3" title={trans('activity')} />

    <LogFunctionalList
      name={selectors.STORE_NAME}
      url={['apiv2_resource_functional_logs', {id: props.resourceId}]}
    />
  </>

LogsMain.propTypes = {
  resourceId: T.string.isRequired
}

export {
  LogsMain
}
