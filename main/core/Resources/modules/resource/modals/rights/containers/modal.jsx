import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {
  actions as formActions,
  selectors as formSelect
} from '#/main/app/content/form/store'

import {RightsModal as RightsModalComponent} from '#/main/core/resource/modals/rights/components/modal'
import {reducer, selectors} from '#/main/core/resource/modals/rights/store'

const RightsModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      saveEnabled: formSelect.saveEnabled(formSelect.form(state, selectors.STORE_NAME)),
      nodeForm: formSelect.data(formSelect.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      updateRights(perms) {
        dispatch(formActions.updateProp(selectors.STORE_NAME, 'rights', perms))
      },
      loadNode(resourceNode) {
        dispatch(formActions.resetForm(selectors.STORE_NAME, resourceNode))
      },
      save(resourceNode, update) {
        dispatch(formActions.saveForm(selectors.STORE_NAME, ['claro_resource_action', {
          type: resourceNode.meta.type,
          action: 'rights',
          id: resourceNode.id
        }])).then((response) => update(response))
      }
    })
  )(RightsModalComponent)
)

export {
  RightsModal
}
