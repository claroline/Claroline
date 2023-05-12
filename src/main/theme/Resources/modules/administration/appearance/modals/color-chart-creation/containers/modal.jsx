import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {actions, reducer, selectors} from '#/main/theme/administration/appearance/modals/color-chart-creation/store'
import {ColorChartCreationModal as ColorChartCreationModalComponent} from '#/main/theme/administration/appearance/modals/color-chart-creation/components/modal'

const ColorChartCreationModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    state => ({
      formData: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME)),
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME))
    }),
    dispatch => ({
      save(data) {
        return dispatch(actions.save(data))
      },
      updateProp(prop, value) {
        dispatch(formActions.updateProp(selectors.STORE_NAME, prop, value))
      },
      reset() {
        dispatch(formActions.reset(selectors.STORE_NAME, {}, true))
      }
    })
  )(ColorChartCreationModalComponent)
)

export {
  ColorChartCreationModal
}
