import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {
  actions as formActions,
  selectors as formSelectors
} from '#/main/app/content/form/store'

import {ParametersModal as ParametersModalComponent} from '#/main/core/workspace/modals/parameters/components/modal'
import {actions, reducer, selectors} from '#/main/core/workspace/modals/parameters/store'

const ParametersModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      workspace: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME)),
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      loadWorkspace(workspaceId) {
        dispatch(actions.get(workspaceId))
      },
      reset() {
        dispatch(formActions.resetForm(selectors.STORE_NAME, null))
      },
      saveWorkspace(workspace, callback) {
        dispatch(formActions.saveForm(selectors.STORE_NAME, ['apiv2_workspace_update', {id: workspace.id}])).then(() => {
          if (callback) {
            callback(workspace)
          }
        })
      }
    })
  )(ParametersModalComponent)
)

export {
  ParametersModal
}
