import cloneDeep from 'lodash/cloneDeep'

import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeInstanceAction} from '#/main/app/store/actions'
import {TOOL_LOAD} from '#/main/core/tool/store'

import {
  APPEARANCE_ADD_ICON_SET, APPEARANCE_REMOVE_ICON_SET,
  APPEARANCE_ADD_COLOR_CHART, APPEARANCE_UPDATE_COLOR_CHART, APPEARANCE_REMOVE_COLOR_CHART
} from '#/main/theme/administration/appearance/store/actions'

export const reducer = combineReducers({
  availableThemes: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, 'main_settings')]: (state, action) => action.toolData.availableThemes
  }),
  availableIconSets: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, 'main_settings')]: (state, action) => action.toolData.availableIconSets,
    [APPEARANCE_ADD_ICON_SET]: (state, action) => {
      const newState = cloneDeep(state)

      newState.push(action.iconSet)

      return newState
    },
    [APPEARANCE_REMOVE_ICON_SET]: (state, action) => {
      const newState = cloneDeep(state)

      const pos = newState.findIndex(iconSet => iconSet.name === action.iconSet.name)
      if (-1 !== pos) {
        newState.splice(pos, 1)
      }

      return newState
    }
  }),
  availableColorCharts: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, 'main_settings')]: (state, action) => action.toolData.availableColorCharts,
    [APPEARANCE_ADD_COLOR_CHART]: (state, action) => {
      const newState = cloneDeep(state)

      const newColorChart = {
        ...action.colorChart,
        colors: Object.keys(action.colorChart.colors).map(key => action.colorChart.colors[key])
      }
      newState.push(newColorChart)

      return newState
    },
    [APPEARANCE_UPDATE_COLOR_CHART]: (state, action) => {
      const newState = cloneDeep(state)

      const updatedColorChart = {
        ...action.colorChart,
        colors: Object.keys(action.colorChart.colors).map(key => action.colorChart.colors[key])
      }

      const colorChartIndex = newState.findIndex(colorChart => colorChart.id === action.colorChart.id)

      if (colorChartIndex !== -1) {
        newState[colorChartIndex] = updatedColorChart
      }

      return newState
    },
    [APPEARANCE_REMOVE_COLOR_CHART]: (state, action) => {
      const newState = cloneDeep(state)

      const pos = newState.findIndex(colorChart => colorChart.id === action.colorChart.id)
      if (-1 !== pos) {
        newState.splice(pos, 1)
      }

      return newState
    }
  })
})
