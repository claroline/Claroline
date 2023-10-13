import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as menuActions, selectors as menuSelectors} from '#/main/app/layout/menu/store'
import {selectors as contextSelectors} from '#/main/app/context/store'

import {WorkspaceMenu as WorkspaceMenuComponent} from '#/main/app/contexts/workspace/components/menu'
import {actions, selectors} from '#/main/app/contexts/workspace/store'

const WorkspaceMenu = withRouter(
  connect(
    (state) => ({
      currentUser: securitySelectors.currentUser(state),
      impersonated: contextSelectors.impersonated(state),
      roles: contextSelectors.roles(state),
      workspace: contextSelectors.data(state),
      section: menuSelectors.openedSection(state),
      tools: contextSelectors.tools(state),
      shortcuts: contextSelectors.shortcuts(state),
      userEvaluation: selectors.userEvaluation(state)
    }),
    (dispatch) => ({
      update(workspace) {
        dispatch(actions.reload(workspace))
      },
      changeSection(section) {
        dispatch(menuActions.changeSection(section))
      }
    })
  )(WorkspaceMenuComponent)
)

export {
  WorkspaceMenu
}
