import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'
import {selectors as formSelectors} from '#/main/app/content/form/store'

import {selectors} from '#/main/core/user/profile/store/selectors'

export const PROFILE_LOAD = 'PROFILE_LOAD'
export const PROFILE_SET_LOADED = 'PROFILE_SET_LOADED'
export const PROFILE_FACET_OPEN = 'PROFILE_FACET_OPEN'

export const actions = {}

actions.openFacet = makeActionCreator(PROFILE_FACET_OPEN, 'id')
actions.load = makeActionCreator(PROFILE_LOAD, 'user', 'facets', 'parameters')
actions.setLoaded = makeActionCreator(PROFILE_SET_LOADED, 'loaded')

actions.open = (username) => (dispatch, getState) => {
  const current = formSelectors.data(formSelectors.form(getState(), selectors.FORM_NAME))

  if (current.username !== username) {
    return dispatch({
      [API_REQUEST]: {
        url: ['apiv2_profile_open', {username: username}],
        silent: true,
        before: () => dispatch(actions.setLoaded(false)),
        success: (response) => dispatch(actions.load(response.user, response.facets, response.parameters || {}))
      }
    })
  }
}
