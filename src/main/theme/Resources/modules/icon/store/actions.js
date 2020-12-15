import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const ICON_COLLECTION_LOAD = 'ICON_COLLECTION_LOAD'

export const actions = {}

actions.loadIconCollection = makeActionCreator(ICON_COLLECTION_LOAD, 'icons')

actions.fetchIconCollection = () => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['apiv2_icon_system_list'],
    success: (response) => dispatch(actions.loadIconCollection(response))
  }
})
