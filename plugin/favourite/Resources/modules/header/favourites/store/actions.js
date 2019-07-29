import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

// actions
export const FAVOURITE_LOAD       = 'FAVOURITE_LOAD'
export const FAVOURITE_REMOVE     = 'FAVOURITE_REMOVE'
export const FAVOURITE_SET_LOADED = 'FAVOURITE_SET_LOADED'

// action creators
export const actions = {}

actions.load = makeActionCreator(FAVOURITE_LOAD, 'favourites')
actions.setLoaded = makeActionCreator(FAVOURITE_SET_LOADED, 'loaded')
actions.remove = makeActionCreator(FAVOURITE_REMOVE, 'object', 'type')

actions.getFavourites = () => ({
  [API_REQUEST]: {
    silent: true,
    url: ['claro_user_favourites'],
    before: (dispatch) => dispatch(actions.setLoaded(false)),
    success: (response, dispatch) => dispatch(actions.load(response))
  }
})

actions.deleteFavourite = (object, type) => ({
  [API_REQUEST]: {
    url: ['workspaces' === object ? 'hevinci_favourite_workspaces_toggle':'hevinci_favourite_resources_toggle', {ids: [object.id]}],
    request: {
      method: 'PUT'
    },
    success: (response, dispatch) => dispatch(actions.remove(object, type))
  }
})