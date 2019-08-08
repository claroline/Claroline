import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'
import {constants} from '#/main/app/security/registration/constants'

const REGISTRATION_DATA_LOAD = 'REGISTRATION_DATA_LOAD'

const actions = {}

actions.loadRegistrationData = makeActionCreator(REGISTRATION_DATA_LOAD, 'data')

actions.createUser = (user, onCreated = () => {}) => ({
  [API_REQUEST]: {
    url: ['apiv2_user_create_and_login'],
    messages: constants.ALERT_REGISTRATION,
    request: {
      method: 'POST',
      body: JSON.stringify(user)
    },
    success: onCreated
  }
})

actions.fetchRegistrationData = () => ({
  [API_REQUEST]: {
    url: ['claro_user_registration_data_fetch'],
    request: {
      method: 'GET'
    },
    success: (data, dispatch) => {
      dispatch(actions.loadRegistrationData(data))
    }
  }
})

export {
  actions,
  REGISTRATION_DATA_LOAD
}