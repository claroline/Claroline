import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as menuActions, selectors as menuSelectors} from '#/main/app/layout/menu/store'
import {actions as walkthroughActions} from '#/main/app/overlays/walkthrough/store'

import {WorkspaceMenu as WorkspaceMenuComponent} from '#/main/core/workspace/components/menu'
import {actions, reducer, selectors} from '#/main/core/workspace/store'

const WorkspaceMenu = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        currentUser: securitySelectors.currentUser(state),
        impersonated: selectors.impersonated(state),
        workspace: selectors.workspace(state),
        section: menuSelectors.openedSection(state),
        tools: selectors.tools(state),
        shortcuts: selectors.userShortcuts(state)
      }),
      (dispatch) => ({
        update(workspace) {
          dispatch(actions.setLoaded(false))
          dispatch(actions.open(workspace.slug))
        },
        changeSection(section) {
          dispatch(menuActions.changeSection(section))
        },
        startWalkthrough(steps) {
          dispatch(walkthroughActions.start(steps))
        }
      })
    )(WorkspaceMenuComponent)
  )
)

export {
  WorkspaceMenu
}
