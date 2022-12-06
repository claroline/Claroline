import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

export const LOAD_USER_ABOUT = 'LOAD_USER_ABOUT'

export const actions = {}

actions.load = makeActionCreator(LOAD_USER_ABOUT, 'user')

actions.get = (id) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_user_get', {id: id}],
    silent: true,
    success: (data) => dispatch(actions.load(data))
  }
})
