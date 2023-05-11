import {createSelector} from 'reselect'
import get from 'lodash/get'

import {selectors as paramSelectors} from '#/main/core/administration/parameters/store/selectors'

const STORE_NAME = 'appearanceParameters'

const store = createSelector(
  [paramSelectors.store],
  (baseStore) => baseStore[STORE_NAME]
)

const availableThemes = createSelector(
  [store],
  (store) => store.availableThemes
)

const availableIconSets = createSelector(
  [store],
  (store) => store.availableIconSets
)

const currentIconSet = createSelector(
  [paramSelectors.parameters, availableIconSets],
  (parameters, availableIconSets) => {
    const currentSetName = get(parameters, 'display.resource_icon_set')

    return availableIconSets.find(iconSet => iconSet.name === currentSetName)
  }
)

const availableColorsCharts = createSelector(
  [store],
  (store) => store.availableColorsCharts
)

const currentColorsChart = createSelector(
  [paramSelectors.parameters, availableColorsCharts],
  (parameters, availableColorsCharts) => {
    const currentColorsChartName = get(parameters, 'display.resource_colors_chart')

    return availableColorsCharts.find(colorsChart => colorsChart.name === currentColorsChartName)
  }
)

export const selectors = {
  STORE_NAME,

  store,
  availableThemes,
  availableIconSets,
  currentIconSet,
  availableColorsCharts,
  currentColorsChart
}
