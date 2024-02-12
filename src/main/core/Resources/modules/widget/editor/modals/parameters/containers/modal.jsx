import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {ParametersModal as ParametersModalComponent} from '#/main/core/widget/editor/modals/parameters/components/modal'
import {reducer, selectors} from '#/main/core/widget/editor/modals/parameters/store'
import {selectors as formSelectors} from '#/main/app/content/form/store'

const ParametersModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      formData: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME)),
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      loadWidget(widget) {
        dispatch(formActions.resetForm(selectors.STORE_NAME, widget, false))
      }
    })
  )(ParametersModalComponent)
)

export {
  ParametersModal
}
