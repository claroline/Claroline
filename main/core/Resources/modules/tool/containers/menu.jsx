import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'

import {ToolMenu as ToolMenuComponent} from '#/main/core/tool/components/menu'
import {reducer, selectors} from '#/main/core/tool/store'

const ToolMenu = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        name: selectors.name(state),
        path: selectors.path(state),
        loaded: selectors.loaded(state)
      })
    )(ToolMenuComponent)
  )
)

export {
  ToolMenu
}
