import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions} from '#/main/app/content/form/store'

import {ParametersModal as ParametersModalComponent} from '#/main/core/widget/content/modals/parameters/components/modal'
import {reducer, selectors} from '#/main/core/widget/content/modals/parameters/store'

const ParametersModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      saveEnabled: selectors.saveEnabled(state),
      formData: selectors.formData(state)
    }),
    (dispatch) => ({
      loadContent(data) {
        dispatch(formActions.resetForm(selectors.STORE_NAME, data, false))
      }
    })
  )(ParametersModalComponent)
)

export {
  ParametersModal
}
