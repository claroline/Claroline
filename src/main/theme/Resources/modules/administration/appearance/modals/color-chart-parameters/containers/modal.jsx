import {connect} from 'react-redux'
import cloneDeep from 'lodash/cloneDeep'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {actions, reducer, selectors} from '#/main/theme/administration/appearance/modals/color-chart-parameters/store'
import {ColorChartParametersModal as ColorChartParametersModalComponent} from '#/main/theme/administration/appearance/modals/color-chart-parameters/components/modal'

const ColorChartParametersModal = withReducer(selectors.STORE_NAME, reducer)(
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
      reset(colorChart) {

        if( typeof colorChart === 'undefined' ) {
          dispatch(formActions.reset(selectors.STORE_NAME, {}, true))
        }
        else {
          const colorChartCopy = cloneDeep(colorChart)
          colorChartCopy.colors = {
            color1: colorChart.colors[0],
            color2: colorChart.colors[1],
            color3: colorChart.colors[2],
            color4: colorChart.colors[3]
          }
          dispatch(formActions.reset(selectors.STORE_NAME, colorChartCopy, false))
        }
      }
    })
  )(ColorChartParametersModalComponent)
)

export {
  ColorChartParametersModal
}
