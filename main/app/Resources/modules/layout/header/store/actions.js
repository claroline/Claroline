import {API_REQUEST} from '#/main/app/api'

export const actions = {}

actions.sendValidationEmail = () => ({
  [API_REQUEST]: {
    url: ['claro_security_validate_email_send'],
    request: {
      method: 'PUT'
    }
  }
})