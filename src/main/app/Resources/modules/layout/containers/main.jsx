import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {LayoutMain as LayoutMainComponent} from '#/main/app/layout/components/main'
import {selectors} from '#/main/app/layout/store'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as menuActions, selectors as menuSelectors} from '#/main/app/layout/menu/store'

import {actions as workspaceActions} from '#/main/core/workspace/store'

const LayoutMain = withRouter(
  connect(
    (state) => ({
      unavailable: selectors.unavailable(state),
      authenticated: securitySelectors.isAuthenticated(state),
      menuOpened: menuSelectors.opened(state)
    }),
    (dispatch) => ({
      openWorkspace(workspaceId) {
        dispatch(workspaceActions.fetch(workspaceId))
      },

      /**
       * Open/close the main app menu.
       */
      toggleMenu() {
        dispatch(menuActions.toggle())
      }
    })
  )(LayoutMainComponent)
)

export {
  LayoutMain
}