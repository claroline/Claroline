import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {
  actions as formActions,
  selectors as formSelect
} from '#/main/app/content/form/store'

import {ParametersModal as ParametersModalComponent} from '#/main/core/resource/modals/parameters/components/modal'
import {reducer, selectors} from '#/main/core/resource/modals/parameters/store'

const ParametersModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      saveEnabled: formSelect.saveEnabled(formSelect.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      loadNode(resourceNode) {
        dispatch(formActions.resetForm(selectors.STORE_NAME, resourceNode))
      },

      save(resourceNode, update) {
        dispatch(formActions.saveForm(selectors.STORE_NAME, ['claro_resource_action', {
          type: resourceNode.meta.type,
          action: 'configure',
          id: resourceNode.id
        }])).then((response) => update(response))
      }
    })
  )(ParametersModalComponent)
)

export {
  ParametersModal
}
