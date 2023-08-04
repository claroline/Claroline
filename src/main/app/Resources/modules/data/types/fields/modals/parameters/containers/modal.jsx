import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {ParametersModal as ParametersModalComponent} from '#/main/app/data/types/fields/modals/parameters/components/modal'
import {reducer, selectors} from '#/main/app/data/types/fields/modals/parameters/store'

const ParametersModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      formData: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME)),
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      update(prop, value) {
        dispatch(formActions.updateProp(selectors.STORE_NAME, prop, value))
      },
      reset(data, isNew) {
        dispatch(formActions.reset(selectors.STORE_NAME, data, isNew))
      }
    })
  )(ParametersModalComponent)
)

export {
  ParametersModal
}
