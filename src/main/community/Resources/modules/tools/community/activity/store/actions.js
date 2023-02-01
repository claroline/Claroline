import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST, url} from '#/main/app/api'

export const COMMUNITY_ACTIVITY_LOAD = 'COMMUNITY_ACTIVITY_LOAD'

export const actions = {}

actions.load = makeActionCreator(COMMUNITY_ACTIVITY_LOAD, 'actionTypes', 'count')
actions.fetch = (contextId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: url(['apiv2_community_activity', {contextId: contextId}]),
    silent: true,
    success: (response, dispatch) => dispatch(actions.load(response.actionTypes, response.count))
  }
})
