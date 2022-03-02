import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors as homeSelectors} from '#/plugin/home/tools/home/store'
import {ParametersModal as ParametersModalComponent} from '#/plugin/home/tools/home/editor/modals/parameters/components/modal'
import {reducer, selectors} from '#/plugin/home/tools/home/editor/modals/parameters/store'

const ParametersModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      formData: selectors.data(state),
      saveEnabled: selectors.saveEnabled(state),
      currentContext: toolSelectors.context(state),
      administration: homeSelectors.administration(state)
    }),
    (dispatch) => ({
      update(field, value) {
        dispatch(formActions.updateProp(selectors.STORE_NAME, field, value))
      },
      setErrors(errors) {
        dispatch(formActions.setErrors(selectors.STORE_NAME, errors))
      },
      loadTab(tab) {
        dispatch(formActions.resetForm(selectors.STORE_NAME, tab))
      }
    })
  )(ParametersModalComponent)
)

export {
  ParametersModal
}
