import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

export const TERMS_OF_SERVICE_LOAD = 'TERMS_OF_SERVICE_LOAD'

export const actions = {}

actions.load = makeActionCreator(TERMS_OF_SERVICE_LOAD, 'content')

actions.fetch = () => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_platform_terms_of_service'],
    silent: true,
    success: (response) => dispatch(actions.load(response))
  }
})
