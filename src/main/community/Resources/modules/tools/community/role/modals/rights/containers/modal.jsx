import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {RightsModal as RightsModalComponent} from '#/main/community/tools/community/role/modals/rights/components/modal'
import {reducer, selectors} from '#/main/community/tools/community/role/modals/rights/store'

const RightsModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      formData: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME)),
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      updateRights(toolName, perm, permValue) {
        dispatch(formActions.updateProp(selectors.STORE_NAME, `${toolName}.${perm}`, permValue))
      },
      loadRights(rights) {
        dispatch(formActions.resetForm(selectors.STORE_NAME, rights, false))
      },
      save(role, workspace, onSave) {
        dispatch(formActions.save(selectors.STORE_NAME, ['apiv2_workspace_tools_set', {workspace: workspace.id, role: role.id}])).then((response) => {
          if (onSave) {
            onSave(response)
          }
        })
      }
    })
  )(RightsModalComponent)
)

export {
  RightsModal
}
