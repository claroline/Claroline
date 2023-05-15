import { connect } from 'react-redux'
import cloneDeep from 'lodash/cloneDeep'

import { withReducer } from '#/main/app/store/components/withReducer'
import { actions as formActions, selectors as formSelectors } from '#/main/app/content/form/store'

import { actions, reducer, selectors } from '#/main/theme/administration/appearance/modals/color-chart-parameters/store'
import { ColorChartParametersModal as ColorChartParametersModalComponent } from '#/main/theme/administration/appearance/modals/color-chart-parameters/components/modal'

const mapStateToProps = state => ({
  formData: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME)),
  saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME))
})

const mapDispatchToProps = dispatch => ({
  save: data => dispatch(actions.save(data)),
  updateProp: (prop, value) => dispatch(formActions.updateProp(selectors.STORE_NAME, prop, value)),
  reset: colorChart => {
    if (typeof colorChart === 'undefined') {
      dispatch(formActions.reset(selectors.STORE_NAME, {}, true))
    } else {
      const colorChartCopy = cloneDeep(colorChart)
      colorChartCopy.colors = colorChart.colors.reduce((acc, color, index) => {
        acc[`color${index + 1}`] = color
        return acc
      }, {})
      dispatch(formActions.reset(selectors.STORE_NAME, colorChartCopy, false))
    }
  }
})

const ColorChartParametersModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(mapStateToProps, mapDispatchToProps)(ColorChartParametersModalComponent)
)

export { ColorChartParametersModal }
