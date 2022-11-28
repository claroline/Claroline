import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

export const LOAD_GROUP_ABOUT = 'LOAD_GROUP_ABOUT'

export const actions = {}

actions.load = makeActionCreator(LOAD_GROUP_ABOUT, 'group')

actions.get = (id) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_group_get', {id: id}],
    silent: true,
    success: (data) => dispatch(actions.load(data))
  }
})
