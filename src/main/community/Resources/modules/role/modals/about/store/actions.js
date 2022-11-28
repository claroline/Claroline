import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

export const LOAD_ROLE_ABOUT = 'LOAD_ROLE_ABOUT'

export const actions = {}

actions.load = makeActionCreator(LOAD_ROLE_ABOUT, 'role')

actions.get = (id) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_role_get', {id: id}],
    silent: true,
    success: (data) => dispatch(actions.load(data))
  }
})
