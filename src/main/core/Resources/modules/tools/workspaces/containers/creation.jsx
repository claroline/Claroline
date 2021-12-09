import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {WorkspaceCreation as WorkspaceCreationComponent} from '#/main/core/tools/workspaces/components/creation'

const WorkspaceCreation = withRouter(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      workspace: formSelectors.data(formSelectors.form(state, 'workspaces.creation'))
    }),
    (dispatch) =>({
      save() {
        return dispatch(formActions.save('workspaces.creation', ['apiv2_workspace_create']))
      }
    })
  )(WorkspaceCreationComponent)
)

export {
  WorkspaceCreation
}
