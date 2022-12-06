import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

export const LOAD_TEAM_ABOUT = 'LOAD_TEAM_ABOUT'

export const actions = {}

actions.load = makeActionCreator(LOAD_TEAM_ABOUT, 'team')

actions.get = (id) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_team_get', {id: id}],
    silent: true,
    success: (data) => dispatch(actions.load(data))
  }
})
