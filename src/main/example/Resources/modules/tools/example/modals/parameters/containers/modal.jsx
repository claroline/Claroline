import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {ParametersModal as ParametersModalComponent} from '#/main/example/tools/example/modals/parameters/components/modal'
import {selectors, reducer} from '#/main/example/tools/example/modals/parameters/store'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

const ParametersModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      formData: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME)),
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      save(formData, onSave) {
        dispatch(formActions.saveForm(selectors.STORE_NAME, formData.id ? ['apiv2_example_update', {id: formData.id}] : ['apiv2_example_create'])).then((response) => {
          onSave(response)
        })
      },
      reset() {
        dispatch(formActions.resetForm(selectors.STORE_NAME, null, true))
      }
    })
  )(ParametersModalComponent)
)

export {
  ParametersModal
}
