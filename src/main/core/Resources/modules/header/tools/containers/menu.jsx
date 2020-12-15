import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {ToolsMenu as ToolsMenuComponent} from '#/main/core/header/tools/components/menu'
import {actions, reducer, selectors} from '#/main/core/header/tools/store'

const ToolsMenu = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      loaded: selectors.loaded(state),
      tools: selectors.tools(state)
    }),
    (dispatch) => ({
      getTools() {
        dispatch(actions.getTools())
      }
    })
  )(ToolsMenuComponent)
)

export {
  ToolsMenu
}
