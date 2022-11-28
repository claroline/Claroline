import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

export const LOAD_ORGANIZATION_ABOUT = 'LOAD_ORGANIZATION_ABOUT'

export const actions = {}

actions.load = makeActionCreator(LOAD_ORGANIZATION_ABOUT, 'organization')

actions.get = (id) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_organization_get', {id: id}],
    silent: true,
    success: (data) => dispatch(actions.load(data))
  }
})
