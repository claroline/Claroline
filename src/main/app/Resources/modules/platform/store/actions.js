import {API_REQUEST, url} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

// action names
export const PLATFORM_SET_CURRENT_ORGANIZATION = 'PLATFORM_SET_CURRENT_ORGANIZATION'
export const FAVORITE_TOGGLE = 'FAVORITE_TOGGLE'

// action creators
export const actions = {}

actions.setCurrentOrganizations = makeActionCreator(PLATFORM_SET_CURRENT_ORGANIZATION, 'organization')

actions.extend = () => ({
  [API_REQUEST]: {
    url: ['apiv2_platform_extend'],
    request: {
      method: 'PUT'
    },
    success: () => window.location.href = url(['claro_index'])
  }
})

actions.toggleFavorite = makeActionCreator(FAVORITE_TOGGLE, 'favorite')
actions.saveFavorite = (workspace) => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['hevinci_favourite_workspaces_toggle', {ids: [workspace.id]}],
    before: () => dispatch(actions.toggleFavorite(workspace)),
    request: {
      method: 'PUT'
    }
  }
})
