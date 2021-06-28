import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const DOCUMENTATION_LOAD = 'DOCUMENTATION_LOAD'

export const actions = {}

actions.load = makeActionCreator(DOCUMENTATION_LOAD, 'doc')

actions.open = (id) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_documentation_get', {id: id}],
    silent: true,
    success: (response) => dispatch(actions.load(response))
  }
})
