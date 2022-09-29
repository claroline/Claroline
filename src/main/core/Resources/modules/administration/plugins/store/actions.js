import isEmpty from 'lodash/isEmpty'
import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

import {actions as listActions} from '#/main/app/content/list/store/actions'

import {selectors} from '#/main/core/administration/plugins/store/selectors'

export const PLUGIN_LOAD = 'PLUGIN_LOAD'

export const actions = {}

actions.loadPlugin = makeActionCreator(PLUGIN_LOAD, 'plugin')

actions.open = (pluginId, force = false) => (dispatch, getState) => {
  const currentPlugin = selectors.plugin(getState())
  if (force || isEmpty(currentPlugin) || currentPlugin.id !== pluginId) {
    return dispatch({
      [API_REQUEST]: {
        url: ['apiv2_plugin_get', {id: pluginId}],
        silent: true,
        before: () => dispatch(actions.loadPlugin(null)),
        success: (data) => dispatch(actions.loadPlugin(data))
      }
    })
  }
}

actions.enable = (plugin) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_plugin_enable', {id: plugin.id}],
    request: {
      method: 'PUT'
    },
    success: () => dispatch(listActions.invalidateData(selectors.STORE_NAME+'.plugins'))
  }
})

actions.disable = (plugin) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_plugin_disable', {id: plugin.id}],
    request: {
      method: 'PUT'
    },
    success: () => dispatch(listActions.invalidateData(selectors.STORE_NAME+'.plugins'))
  }
})
