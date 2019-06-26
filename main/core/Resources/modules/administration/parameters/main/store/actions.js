import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'

export const PLUGINS_LOAD = 'PLUGINS_LOAD'

export const actions = {}

actions.loadPlugins = makeActionCreator(PLUGINS_LOAD, 'plugins')

actions.fetchPlugins = () => ({
  [API_REQUEST]: {
    url: ['apiv2_plugin_list'],
    success: (plugins, dispatch) => dispatch(actions.loadPlugins(plugins))
  }
})

actions.openConnectionMessageForm = (formName, defaultProps, id = null) => (dispatch) => {
  if (id) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_connectionmessage_get', {id}],
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(formName, response, false))
        }
      }
    })
  } else {
    dispatch(formActions.resetForm(formName, defaultProps, true))
  }
}

actions.resetForm = (formName) => (dispatch) => {
  dispatch(formActions.resetForm(formName, {}, true))
}