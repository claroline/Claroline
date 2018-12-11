import {makeActionCreator} from '#/main/app/store/actions'

import {API_REQUEST} from '#/main/app/api'

export const PLUGINS_LOAD = 'PLUGINS_LOAD'

export const actions = {}

actions.loadPlugins = makeActionCreator(PLUGINS_LOAD, 'plugins')

actions.fetchPlugins = () => ({
  [API_REQUEST]: {
    url: ['apiv2_plugin_list'],
    success: (plugins, dispatch) => dispatch(actions.loadPlugins(plugins))
  }
})
