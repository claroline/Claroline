import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const WORKSPACES_MENU_LOAD = 'WORKSPACES_MENU_LOAD'

export const actions = {}

actions.loadMenu = makeActionCreator(WORKSPACES_MENU_LOAD, 'history', 'personal', 'creatable')
actions.fetchMenu = () => ({
  [API_REQUEST]: {
    silent: true,
    url: ['apiv2_workspace_menu'],
    success: (response, dispatch) => dispatch(actions.loadMenu(response.history, response.personal, response.creatable))
  }
})
