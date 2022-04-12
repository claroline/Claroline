import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {ToolsMenu as ToolsMenuComponent} from '#/main/core/header/tools/components/menu'
import {actions, reducer, selectors} from '#/main/core/header/tools/store'
import {selectors as securitySelectors} from '#/main/app/security/store'

const ToolsMenu = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      isAuthenticated: securitySelectors.isAuthenticated(state),
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
