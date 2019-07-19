import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {LayoutMain as LayoutMainComponent} from '#/main/app/layout/components/main'
import {actions, selectors} from '#/main/app/layout/store'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as menuActions, selectors as menuSelectors} from '#/main/app/layout/menu/store'

import {actions as workspaceActions} from '#/main/core/workspace/store'

const LayoutMain = withRouter(
  connect(
    (state) => ({
      maintenance: selectors.maintenance(state),
      authenticated: securitySelectors.isAuthenticated(state),
      menuOpened: menuSelectors.opened(state),
      sidebar: selectors.sidebar(state)
    }),
    (dispatch) => ({
      openWorkspace(workspaceId) {
        dispatch(workspaceActions.open(workspaceId))
      },

      /**
       * Open/close the main app menu.
       */
      toggleMenu() {
        dispatch(menuActions.toggle())
      },

      openSidebar(toolName) {
        dispatch(actions.openSidebar(toolName))
      },
      closeSidebar() {
        dispatch(actions.closeSidebar())
      }
    })
  )(LayoutMainComponent)
)

export {
  LayoutMain
}