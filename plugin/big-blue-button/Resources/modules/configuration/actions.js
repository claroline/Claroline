import {makeActionCreator} from '#/main/core/utilities/redux'
import {generateUrl} from '#/main/core/fos-js-router'
import {REQUEST_SEND} from '#/main/core/api/actions'
import {trans} from '#/main/core/translation'

export const CONFIGURATION_UPDATE = 'CONFIGURATION_UPDATE'
export const CONFIGURATION_MESSAGE_RESET = 'CONFIGURATION_MESSAGE_RESET'
export const CONFIGURATION_MESSAGE_UPDATE = 'CONFIGURATION_MESSAGE_UPDATE'
export const MEETINGS_INIT = 'MEETINGS_INIT'

export const actions = {}

actions.updateConfigurationProperty = makeActionCreator(CONFIGURATION_UPDATE, 'property', 'value')
actions.resetConfigurationMessage = makeActionCreator(CONFIGURATION_MESSAGE_RESET)
actions.updateConfigurationMessage = makeActionCreator(CONFIGURATION_MESSAGE_UPDATE, 'content', 'status')

actions.updateConfiguration = (property, value) => (dispatch) => {
  dispatch(actions.updateConfigurationProperty(property, value))
  dispatch(actions.resetConfigurationMessage())
}

actions.saveConfiguration = () => (dispatch, getState) => {
  const state = getState()
  const serverUrl = state.config.serverUrl
  const securitySalt = state.config.securitySalt
  const formData = new FormData()

  if (serverUrl) {
    formData.append('serverUrl', serverUrl)
  }
  if (securitySalt) {
    formData.append('securitySalt', securitySalt)
  }

  dispatch({
    [REQUEST_SEND]: {
      url: generateUrl('claro_bbb_plugin_configuration_save'),
      request: {
        method: 'POST',
        body: formData
      },
      success: (data, dispatch) => {
        dispatch(actions.updateConfigurationMessage(trans('configuration_saved_success_msg', {}, 'bbb'), 'success'))
        dispatch(actions.getMeetings())
      },
      failure: (data, dispatch) => {
        dispatch(actions.updateConfigurationMessage(trans('configuration_saved_error_msg', {}, 'bbb'), 'danger'))
      }
    }
  })
}

actions.initializeMeetings = makeActionCreator(MEETINGS_INIT, 'meetings')

actions.getMeetings = () => (dispatch) => {
  dispatch({
    [REQUEST_SEND]: {
      url: generateUrl('claro_bbb_plugin_configuration_meetings_list'),
      request: {
        method: 'GET'
      },
      success: (data, dispatch) => {
        dispatch(actions.initializeMeetings(data))
      }
    }
  })
}