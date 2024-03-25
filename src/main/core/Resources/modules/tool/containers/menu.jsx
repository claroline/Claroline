import {connect} from 'react-redux'

import {ToolMenu as ToolMenuComponent} from '#/main/core/tool/components/menu'
import {actions, selectors} from '#/main/core/tool/store'

const ToolMenu = connect(
  (state) => ({
    name: selectors.name(state),
    path: selectors.path(state),
    toolData: selectors.toolData(state),
    loaded: selectors.loaded(state),
    currentContext: selectors.context(state)
  }),
  (dispatch) => ({
    reload() {
      dispatch(actions.setLoaded(false))
    }
  })
)(ToolMenuComponent)

export {
  ToolMenu
}
