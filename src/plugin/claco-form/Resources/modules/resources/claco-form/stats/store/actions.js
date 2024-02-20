import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

export const LOAD_CLACOFORM_STATS = 'LOAD_CLACOFORM_STATS'

export const actions = {}

actions.loadStats = makeActionCreator(LOAD_CLACOFORM_STATS, 'stats')

actions.fetchStats = (clacoformId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['apiv2_clacoform_stats', {id: clacoformId}],
    success: (response) => dispatch(actions.loadStats(response))
  }
})
