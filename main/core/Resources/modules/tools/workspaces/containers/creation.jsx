import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {WorkspaceCreation as WorkspaceCreationComponent} from '#/main/core/tools/workspaces/components/creation'
import {actions, selectors} from '#/main/core/tools/workspaces/store'

const WorkspaceCreation = withRouter(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      workspace: formSelectors.data(formSelectors.form(state, 'workspaces.creation')),
      logData: selectors.creationLogs(state)
    }),
    (dispatch, ownProps) =>({
      updateProp(propName, propValue) {
        dispatch(formActions.updateProp(ownProps.name, propName, propValue))
      },
      loadLog(filename) {
        dispatch(actions.fetchCreationLogs(filename))
      },
      save() {
        return dispatch(formActions.save('workspaces.creation', ['apiv2_workspace_create']))
      }
    })
  )(WorkspaceCreationComponent)
)

export {
  WorkspaceCreation
}
