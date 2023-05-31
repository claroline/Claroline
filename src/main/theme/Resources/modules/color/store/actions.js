import { makeActionCreator } from '#/main/app/store/actions'
import { API_REQUEST } from '#/main/app/api'
export const COLOR_CHART_LOAD = 'COLOR_CHART_LOAD'
export const actions = {}

actions.loadColorChart = makeActionCreator(COLOR_CHART_LOAD, 'colorChart')

actions.fetchColorChart = () => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['apiv2_color_collection_list'],
    success: (response) => dispatch(actions.loadColorChart(response))
  }
})
