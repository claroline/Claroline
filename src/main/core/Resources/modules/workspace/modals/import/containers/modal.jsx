import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {ImportModal as ImportModalComponent} from '#/main/core/workspace/modals/import/components/modal'
import {actions, reducer, selectors} from '#/main/core/workspace/modals/import/store'

const ImportModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      formData: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME)),
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      save(data) {
        return dispatch(actions.save(data))
      },
      reset() {
        dispatch(formActions.reset(selectors.STORE_NAME, {}, true))
      }
    })
  )(ImportModalComponent)
)

export {
  ImportModal
}
