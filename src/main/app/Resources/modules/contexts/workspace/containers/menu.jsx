import {connect} from 'react-redux'

import {selectors as contextSelectors} from '#/main/app/context/store'

import {WorkspaceMenu as WorkspaceMenuComponent} from '#/main/app/contexts/workspace/components/menu'
import {selectors} from '#/main/app/contexts/workspace/store'

const WorkspaceMenu = connect(
  (state) => ({
    impersonated: contextSelectors.impersonated(state),
    roles: contextSelectors.roles(state),
    workspace: contextSelectors.data(state),
    tools: contextSelectors.tools(state),
    userEvaluation: selectors.userEvaluation(state)
  })
)(WorkspaceMenuComponent)

export {
  WorkspaceMenu
}
