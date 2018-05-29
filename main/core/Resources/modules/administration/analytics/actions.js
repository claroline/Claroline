import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const LOAD_OVERVIEW = 'LOAD_OVERVIEW'
export const LOAD_AUDIENCE = 'LOAD_AUDIENCE'
export const LOAD_RESOURCES = 'LOAD_RESOURCES'
export const LOAD_WIDGETS = 'LOAD_WIDGETS'

export const actions = {}

actions.loadOverviewData = makeActionCreator(LOAD_OVERVIEW, 'data')
actions.loadAudienceData = makeActionCreator(LOAD_AUDIENCE, 'data')
actions.loadResourcesData = makeActionCreator(LOAD_RESOURCES, 'data')
actions.loadWidgetsData = makeActionCreator(LOAD_WIDGETS, 'data')

actions.getOverviewData = () => (dispatch) => {
  actions.loadOverviewData({})
  dispatch({
    [API_REQUEST]: {
      url: ['apiv2_admin_tool_analytics_overview'],
      success: (response, dispatch) => {
        dispatch(actions.loadOverviewData(response))
      }
    }
  })
}

actions.getAudienceData = (filters = {}) => (dispatch) => {
  actions.loadAudienceData({})
  if (Object.keys(filters).length !== 0) {
    filters = {filters: filters}
  }
  dispatch({
    [API_REQUEST]: {
      url: ['apiv2_admin_tool_analytics_audience', filters],
      success: (response, dispatch) => {
        dispatch(actions.loadAudienceData(response))
      }
    }
  })
}

actions.getResourcesData = () => (dispatch) => {
  actions.loadResourcesData({})
  dispatch({
    [API_REQUEST]: {
      url: ['apiv2_admin_tool_analytics_resources'],
      success: (response, dispatch) => {
        dispatch(actions.loadResourcesData(response))
      }
    }
  })
}

actions.getWidgetsData = () => (dispatch) => {
  actions.loadWidgetsData({})
  dispatch({
    [API_REQUEST]: {
      url: ['apiv2_admin_tool_analytics_widgets'],
      success: (response, dispatch) => {
        dispatch(actions.loadWidgetsData(response))
      }
    }
  })
}
