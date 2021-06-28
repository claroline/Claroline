import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {
  actions as formActions,
  selectors as formSelectors
} from '#/main/app/content/form/store'

import {RightsModal as RightsModalComponent} from '#/main/core/resource/modals/rights/components/modal'
import {reducer, selectors, actions} from '#/main/core/resource/modals/rights/store'

const RightsModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.FORM_NAME)),
      nodeForm: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME)),
      recursiveEnabled: selectors.recursiveEnabled(state)
    }),
    (dispatch) => ({
      updateRights(perms) {
        dispatch(formActions.updateProp(selectors.FORM_NAME, 'rights', perms))
      },
      loadRights(resourceNode) {
        return dispatch(actions.fetchRights(resourceNode))
      },
      save(resourceNode, update, recursive = false) {
        dispatch(formActions.saveForm(selectors.FORM_NAME, ['claro_resource_action', {
          action: 'rights',
          id: resourceNode.id,
          recursive
        }])).then((response) => update(response))
      },
      reset() {
        dispatch(formActions.resetForm(selectors.FORM_NAME, {}))
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
