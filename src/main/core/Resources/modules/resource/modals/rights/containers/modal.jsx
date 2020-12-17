import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {
  actions as formActions,
  selectors as formSelect
} from '#/main/app/content/form/store'

import {RightsModal as RightsModalComponent} from '#/main/core/resource/modals/rights/components/modal'
import {reducer, selectors, actions} from '#/main/core/resource/modals/rights/store'

const RightsModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      saveEnabled: formSelect.saveEnabled(formSelect.form(state, selectors.FORM_NAME)),
      nodeForm: formSelect.data(formSelect.form(state, selectors.FORM_NAME)),
      recursiveEnabled: selectors.recursiveEnabled(state)
    }),
    (dispatch) => ({
      updateRights(perms) {
        dispatch(formActions.updateProp(selectors.FORM_NAME, 'rights', perms))
      },
      loadNode(resourceNode) {
        dispatch(formActions.resetForm(selectors.FORM_NAME, resourceNode))
      },
      save(resourceNode, update, recursive = false) {
        dispatch(formActions.saveForm(selectors.FORM_NAME, ['claro_resource_action', {
          type: resourceNode.meta.type,
          action: 'rights',
          id: resourceNode.id,
          recursive
        }])).then((response) => update(response))
      },
      setRecursiveEnabled(bool) {
        dispatch(actions.setRecursive(bool))
      }
    })
  )(RightsModalComponent)
)

export {
  RightsModal
}
