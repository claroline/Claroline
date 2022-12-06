import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

export const PROFILE_LOAD = 'PROFILE_LOAD'
export const PROFILE_SET_LOADED = 'PROFILE_SET_LOADED'
export const PROFILE_FACET_OPEN = 'PROFILE_FACET_OPEN'

export const actions = {}

actions.openFacet = makeActionCreator(PROFILE_FACET_OPEN, 'id')
actions.load = makeActionCreator(PROFILE_LOAD, 'facets', 'parameters')
actions.setLoaded = makeActionCreator(PROFILE_SET_LOADED, 'loaded')

actions.open = () => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_profile_open'],
    silent: true,
    before: () => dispatch(actions.setLoaded(false)),
    success: (response) => dispatch(actions.load(response.facets, response.parameters || {}))
  }
})
