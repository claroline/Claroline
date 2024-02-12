import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {ParametersModal as ParametersModalComponent} from '#/main/core/widget/editor/modals/parameters/components/modal'
import {reducer, selectors} from '#/main/core/widget/editor/modals/parameters/store'


const ParametersModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      formData: selectors.data(state),
      saveEnabled: selectors.saveEnabled(state)
    }),
    (dispatch) => ({
      loadWidget(widget) {
        dispatch(formActions.resetForm(selectors.STORE_NAME, widget))
      }
    })
  )(ParametersModalComponent)
)

export {
  ParametersModal
}
