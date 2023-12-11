import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {LogsMain as LogsMainComponent} from '#/main/log/resource/logs/components/main'
import {selectors, reducer} from '#/main/log/resource/logs/store'

const LogsMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      resourceId: resourceSelectors.id(state)
    })
  )(LogsMainComponent)
)

export {
  LogsMain
}